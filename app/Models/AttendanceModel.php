<?php

namespace App\Models;

use CodeIgniter\Model;

class AttendanceModel extends Model
{
    protected $table         = 'attendance';
    protected $primaryKey    = 'id';
    protected $allowedFields = [
        'employee_id', 'qr_token_id', 'type', 'scan_label',
        'scan_latitude', 'scan_longitude', 'geofence_status',
        'scanned_at', 'date', 'note',
    ];
    protected $useTimestamps = true;

    // ─── Scan-type cycle ───────────────────────────────────────────────────────
    //
    //  Sequence within a single day:
    //    (empty / check_out)  → check_in
    //    check_in             → break_start
    //    break_start          → break_end
    //    break_end            → check_out
    //    check_out            → check_in  (should not normally happen same day)
    //
    // If the employee has already checked-out, subsequent scans restart at
    // check_in so an admin can fix edge-cases without a migration.

    /** Labels shown in the app / API response */
    public const LABELS = [
        'check_in'    => 'Shift Start',
        'break_start' => 'Break Start',
        'break_end'   => 'Break End',
        'check_out'   => 'Shift End',
    ];

    /** The fixed scan cycle */
    private const CYCLE = ['check_in', 'break_start', 'break_end', 'check_out'];

    public function getLastScanToday(int $employeeId): ?array
    {
        return $this->where('employee_id', $employeeId)
                    ->where('date', date('Y-m-d'))
                    ->orderBy('scanned_at', 'DESC')
                    ->first();
    }

    /**
     * Returns the NEXT scan type the employee should perform today.
     * Also returns a human-readable label.
     *
     * @return array{type: string, label: string}
     */
    public function getNextScan(int $employeeId): array
    {
        $last = $this->getLastScanToday($employeeId);

        if (!$last) {
            // First scan of the day
            return ['type' => 'check_in', 'label' => self::LABELS['check_in']];
        }

        $currentIndex = array_search($last['type'], self::CYCLE, true);

        if ($currentIndex === false || $currentIndex === count(self::CYCLE) - 1) {
            // Unknown type or already at check_out → restart
            return ['type' => 'check_in', 'label' => self::LABELS['check_in']];
        }

        $nextType = self::CYCLE[$currentIndex + 1];
        return ['type' => $nextType, 'label' => self::LABELS[$nextType]];
    }

    /**
     * Backwards-compatible helper used by old code.
     */
    public function getNextScanType(int $employeeId): string
    {
        return $this->getNextScan($employeeId)['type'];
    }

    // ─── Dashboard ──────────────────────────────────────────────────────────────

    public function getTodaySummary(): array
    {
        $today = date('Y-m-d');
        $db    = \Config\Database::connect();

        $totalEmp = $db->table('employees')->where('is_active', 1)->countAllResults();

        $present = (int) $db->query(
            "SELECT COUNT(DISTINCT employee_id) AS cnt FROM attendance WHERE date = ? AND type = 'check_in'",
            [$today]
        )->getRow()->cnt;

        $absent = $totalEmp - $present;

        $workStart = model('SettingsModel')->getSetting('work_start_time', '09:00');
        $late = (int) $db->query(
            "SELECT COUNT(*) AS cnt FROM attendance WHERE date = ? AND type = 'check_in' AND TIME(scanned_at) > ?",
            [$today, $workStart]
        )->getRow()->cnt;

        return compact('totalEmp', 'present', 'absent', 'late');
    }

    // ─── Reports ────────────────────────────────────────────────────────────────

    /**
     * Returns one row per employee per date with:
     *   check_in, break_start, break_end, check_out,
     *   net_minutes (gross minutes minus break minutes),
     *   geofence_status
     */
    public function getDailyLog(string $date, ?int $employeeId = null, ?string $department = null): array
    {
        $db  = \Config\Database::connect();
        $sql = $db->table('employees e')
                  ->select("
                    e.id, e.employee_code, e.name, e.department,
                    MIN(CASE WHEN a.type = 'check_in'    THEN a.scanned_at END) AS check_in,
                    MIN(CASE WHEN a.type = 'break_start' THEN a.scanned_at END) AS break_start,
                    MAX(CASE WHEN a.type = 'break_end'   THEN a.scanned_at END) AS break_end,
                    MAX(CASE WHEN a.type = 'check_out'   THEN a.scanned_at END) AS check_out,
                    ROUND(
                        TIMESTAMPDIFF(MINUTE,
                            MIN(CASE WHEN a.type = 'check_in'  THEN a.scanned_at END),
                            MAX(CASE WHEN a.type = 'check_out' THEN a.scanned_at END)
                        )
                        -
                        COALESCE(
                            TIMESTAMPDIFF(MINUTE,
                                MIN(CASE WHEN a.type = 'break_start' THEN a.scanned_at END),
                                MAX(CASE WHEN a.type = 'break_end'   THEN a.scanned_at END)
                            ), 0
                        )
                    ) AS net_minutes,
                    MAX(a.geofence_status) AS geofence_status
                  ")
                  ->join('attendance a', "a.employee_id = e.id AND a.date = '$date'", 'left')
                  ->where('e.is_active', 1)
                  ->groupBy('e.id');

        if ($employeeId) $sql->where('e.id', $employeeId);
        if ($department) $sql->where('e.department', $department);

        return $sql->get()->getResultArray();
    }

    public function getMonthlyReport(string $month): array
    {
        $db   = \Config\Database::connect();
        $from = $month . '-01';
        $to   = date('Y-m-t', strtotime($from));

        // Compute per-employee per-day net_minutes, then SUM across the month
        $sql = "
            SELECT
                e.id,
                e.employee_code,
                e.name,
                e.department,
                COUNT(DISTINCT CASE WHEN a.type = 'check_in' THEN a.date END) AS days_present,
                SUM(CASE
                    WHEN a.type = 'check_in'
                     AND TIME(a.scanned_at) > (SELECT value FROM settings WHERE `key` = 'work_start_time')
                    THEN 1 ELSE 0 END) AS late_days,
                COALESCE(SUM(daily.net_minutes), 0) AS total_net_minutes
            FROM employees e
            LEFT JOIN attendance a
                ON a.employee_id = e.id
               AND a.date BETWEEN ? AND ?
            LEFT JOIN (
                SELECT
                    a2.employee_id,
                    a2.date,
                    GREATEST(0, COALESCE(
                        TIMESTAMPDIFF(MINUTE,
                            MIN(CASE WHEN a2.type = 'check_in'    THEN a2.scanned_at END),
                            MAX(CASE WHEN a2.type = 'check_out'   THEN a2.scanned_at END)
                        ) - COALESCE(
                            TIMESTAMPDIFF(MINUTE,
                                MIN(CASE WHEN a2.type = 'break_start' THEN a2.scanned_at END),
                                MAX(CASE WHEN a2.type = 'break_end'   THEN a2.scanned_at END)
                            ), 0
                        ), 0
                    )) AS net_minutes
                FROM attendance a2
                WHERE a2.date BETWEEN ? AND ?
                GROUP BY a2.employee_id, a2.date
            ) daily ON daily.employee_id = e.id AND daily.date BETWEEN ? AND ?
            WHERE e.is_active = 1
            GROUP BY e.id
        ";

        return $db->query($sql, [$from, $to, $from, $to, $from, $to])->getResultArray();
    }

    // ─── Map Live ───────────────────────────────────────────────────────────────

    public function getLiveCheckedIn(): array
    {
        $db    = \Config\Database::connect();
        $today = date('Y-m-d');

        // Show employees whose LAST scan today is check_in or break_end
        // (i.e., they are currently "working", not on break or checked out)
        return $db->query("
            SELECT e.name, e.employee_code, q.location_name, q.latitude, q.longitude,
                   a.scanned_at, a.geofence_status, a.type AS last_scan_type
            FROM attendance a
            JOIN employees e ON e.id = a.employee_id
            JOIN qr_tokens q ON q.id = a.qr_token_id
            WHERE a.date = '$today'
              AND a.type IN ('check_in', 'break_end')
              AND NOT EXISTS (
                SELECT 1 FROM attendance a2
                WHERE a2.employee_id = a.employee_id
                  AND a2.date = '$today'
                  AND a2.scanned_at > a.scanned_at
              )
        ")->getResultArray();
    }
}
