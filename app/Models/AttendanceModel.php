<?php

namespace App\Models;

use CodeIgniter\Model;

class AttendanceModel extends Model
{
    protected $table         = 'attendance';
    protected $primaryKey    = 'id';
    protected $allowedFields = [
        'employee_id', 'qr_token_id', 'type',
        'scan_latitude', 'scan_longitude', 'geofence_status',
        'scanned_at', 'date', 'note',
    ];
    protected $useTimestamps = true;

    // ─── Scan Logic ────────────────────────────────────────────────────────────

    public function getLastScanToday(int $employeeId): ?array
    {
        return $this->where('employee_id', $employeeId)
                    ->where('date', date('Y-m-d'))
                    ->orderBy('scanned_at', 'DESC')
                    ->first();
    }

    public function getNextScanType(int $employeeId): string
    {
        $last = $this->getLastScanToday($employeeId);
        if (!$last || $last['type'] === 'check_out') {
            return 'check_in';
        }
        return 'check_out';
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

    public function getDailyLog(string $date, ?int $employeeId = null, ?string $department = null): array
    {
        $db  = \Config\Database::connect();
        $sql = $db->table('employees e')
                  ->select('e.id, e.employee_code, e.name, e.department,
                    MIN(CASE WHEN a.type="check_in"  THEN a.scanned_at END) AS check_in,
                    MAX(CASE WHEN a.type="check_out" THEN a.scanned_at END) AS check_out,
                    MAX(a.geofence_status) AS geofence_status')
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

        return $db->table('employees e')
                  ->select('e.id, e.employee_code, e.name, e.department,
                    COUNT(DISTINCT CASE WHEN a.type="check_in" THEN a.date END) AS days_present,
                    SUM(CASE WHEN a.type="check_in" AND TIME(a.scanned_at) > (SELECT value FROM settings WHERE `key`="work_start_time") THEN 1 ELSE 0 END) AS late_days')
                  ->join('attendance a', "a.employee_id = e.id AND a.date BETWEEN '$from' AND '$to'", 'left')
                  ->where('e.is_active', 1)
                  ->groupBy('e.id')
                  ->get()->getResultArray();
    }

    // ─── Map Live ───────────────────────────────────────────────────────────────

    public function getLiveCheckedIn(): array
    {
        $db    = \Config\Database::connect();
        $today = date('Y-m-d');

        // Employees with check_in but no check_out today
        return $db->query("
            SELECT e.name, e.employee_code, q.location_name, q.latitude, q.longitude,
                   a.scanned_at, a.geofence_status
            FROM attendance a
            JOIN employees e ON e.id = a.employee_id
            JOIN qr_tokens q ON q.id = a.qr_token_id
            WHERE a.date = '$today' AND a.type = 'check_in'
              AND NOT EXISTS (
                SELECT 1 FROM attendance a2
                WHERE a2.employee_id = a.employee_id AND a2.date = '$today'
                  AND a2.type = 'check_out' AND a2.scanned_at > a.scanned_at
              )
        ")->getResultArray();
    }
}
