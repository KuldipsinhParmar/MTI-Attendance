<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>

<!-- Filter + Export -->
<div class="card mb-3">
    <div class="card-body py-2">
        <form class="d-flex align-items-center gap-2 flex-wrap" method="GET">
            <input type="month" name="month" class="form-control form-control-sm" style="width:160px;"
                   value="<?= esc($month) ?>">
            <select name="employee_id" class="form-select form-select-sm" style="width:160px;">
                <option value="">All Employees</option>
                <?php foreach ($employees as $emp): ?>
                    <option value="<?= $emp['id'] ?>" <?= (($selectedEmployee ?? '') == $emp['id']) ? 'selected' : '' ?>>
                        <?= esc($emp['name']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
            <select name="department" class="form-select form-select-sm" style="width:150px;">
                <option value="">All Departments</option>
                <?php foreach ($departments as $d): ?>
                    <option value="<?= esc($d['department']) ?>"
                        <?= (($selectedDepartment ?? '') == $d['department']) ? 'selected' : '' ?>>
                        <?= esc($d['department']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
            <button type="submit" class="btn btn-primary btn-sm">
                <i class="bi bi-funnel me-1"></i> Filter
            </button>
            <a href="<?= base_url('reports/export-csv?month=' . urlencode($month) . '&employee_id=' . urlencode($selectedEmployee ?? '') . '&department=' . urlencode($selectedDepartment ?? '')) ?>" class="btn btn-success btn-sm ms-auto">
                <i class="bi bi-file-earmark-spreadsheet me-1"></i> Export CSV
            </a>
        </form>
    </div>
</div>

<!-- Report Table -->
<div class="card">
    <div class="card-header d-flex align-items-center justify-content-between">
        <span><i class="bi bi-bar-chart-fill text-primary me-2"></i>
            Monthly Report — <?= date('F Y', strtotime($month . '-01')) ?>
        </span>
        <span class="badge bg-secondary-subtle text-secondary border">
            <?= $workingDaysInfo['total_working_days'] ?> working days 
            <small>(<?= $workingDaysInfo['weekends'] ?> weekends, <?= $workingDaysInfo['holidays'] ?> holidays)</small>
        </span>
    </div>
    <div class="card-body p-0">
        <table class="table table-hover datatable mb-0" id="reports-table" width="100%">
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Department</th>
                    <th>Present</th>
                    <th>Absent</th>
                    <th>Late</th>
                    <th>Total Hours</th>
                    <th>Avg / Day</th>
                    <th>Attendance %</th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($report as $row):
                $absent   = max(0, $workingDaysInfo['total_working_days'] - $row['days_present']);
                $pct      = $workingDaysInfo['total_working_days'] > 0 ? round(($row['days_present'] / $workingDaysInfo['total_working_days']) * 100) : 0;
                $barColor = $pct >= 80 ? 'bg-success' : ($pct >= 60 ? 'bg-warning' : 'bg-danger');

                // Total working hours for the month
                $totalMins = (int)($row['total_net_minutes'] ?? 0);
                $totalH    = floor($totalMins / 60);
                $totalM    = $totalMins % 60;
                $totalStr  = $totalMins > 0 ? $totalH . 'h' . ($totalM > 0 ? ' ' . $totalM . 'm' : '') : '—';

                // Average hours per present day
                $avgMins = $row['days_present'] > 0 ? round($totalMins / $row['days_present']) : 0;
                $avgH    = floor($avgMins / 60);
                $avgM    = $avgMins % 60;
                $avgStr  = $avgMins > 0 ? $avgH . 'h' . ($avgM > 0 ? ' ' . $avgM . 'm' : '') : '—';

                // Avg badge color based on expected 8h work day
                $avgBadge = $avgMins >= 480 ? 'bg-success-subtle text-success border-success-subtle'
                          : ($avgMins >= 360 ? 'bg-warning-subtle text-warning border-warning-subtle'
                          : ($avgMins > 0   ? 'bg-danger-subtle text-danger border-danger-subtle' : 'bg-secondary-subtle text-secondary border-secondary-subtle'));
            ?>
            <tr>
                <td class="fw-medium"><?= esc($row['name']) ?></td>
                <td><?= esc($row['department']) ?></td>
                <td><span class="text-success fw-semibold"><?= $row['days_present'] ?></span></td>
                <td><span class="text-danger fw-semibold"><?= $absent ?></span></td>
                <td><span class="text-warning fw-semibold"><?= $row['late_days'] ?></span></td>
                <td><span class="fw-semibold"><?= $totalStr ?></span></td>
                <td><span class="badge border <?= $avgBadge ?>"><?= $avgStr ?></span></td>
                <td style="min-width:130px;">
                    <div class="d-flex align-items-center gap-2">
                        <div class="progress flex-grow-1" style="height:6px; border-radius:3px;">
                            <div class="progress-bar <?= $barColor ?>" style="width:<?= $pct ?>%"></div>
                        </div>
                        <small class="fw-semibold text-muted" style="width:36px;"><?= $pct ?>%</small>
                    </div>
                </td>
            </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
<?= $this->endSection() ?>
