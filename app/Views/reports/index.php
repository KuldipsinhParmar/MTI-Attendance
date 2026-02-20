<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>

<!-- Filter + Export -->
<div class="d-flex align-items-center gap-2 mb-3 flex-wrap">
    <form class="d-flex align-items-center gap-2" method="GET">
        <input type="month" name="month" class="form-control form-control-sm" value="<?= esc($month) ?>" style="width:160px;">
        <button type="submit" class="btn btn-primary btn-sm">
            <i class="bi bi-funnel me-1"></i> Filter
        </button>
    </form>
    <a href="<?= base_url('reports/export-csv?month=' . $month) ?>" class="btn btn-success btn-sm ms-auto">
        <i class="bi bi-file-earmark-spreadsheet me-1"></i> Export CSV
    </a>
</div>

<!-- Report Table -->
<div class="card">
    <div class="card-header d-flex align-items-center justify-content-between">
        <span><i class="bi bi-bar-chart-fill text-primary me-2"></i>
            Monthly Report — <?= date('F Y', strtotime($month . '-01')) ?>
        </span>
        <span class="badge bg-secondary-subtle text-secondary border"><?= $daysInMonth ?> working days</span>
    </div>
    <div class="card-body p-0">
        <table class="table table-hover datatable mb-0" id="reports-table" width="100%">
            <thead>
                <tr>
                    <th>Code</th>
                    <th>Name</th>
                    <th>Department</th>
                    <th>Present</th>
                    <th>Absent</th>
                    <th>Late</th>
                    <th>Attendance %</th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($report as $row):
                $absent = $daysInMonth - $row['days_present'];
                $pct    = $daysInMonth > 0 ? round(($row['days_present'] / $daysInMonth) * 100) : 0;
                $barColor = $pct >= 80 ? 'bg-success' : ($pct >= 60 ? 'bg-warning' : 'bg-danger');
            ?>
            <tr>
                <td><span class="badge bg-primary-subtle text-primary border border-primary-subtle"><?= esc($row['employee_code']) ?></span></td>
                <td class="fw-medium"><?= esc($row['name']) ?></td>
                <td><?= esc($row['department']) ?></td>
                <td><span class="text-success fw-semibold"><?= $row['days_present'] ?></span></td>
                <td><span class="text-danger fw-semibold"><?= $absent ?></span></td>
                <td><span class="text-warning fw-semibold"><?= $row['late_days'] ?></span></td>
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
