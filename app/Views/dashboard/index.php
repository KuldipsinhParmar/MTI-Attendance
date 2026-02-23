<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>

<!-- Stat Cards -->
<div class="row g-3 mb-4">
    <div class="col-sm-6 col-xl-3">
        <div class="card stat-card h-100">
            <div class="card-body d-flex align-items-center gap-3">
                <div class="stat-icon-box bg-primary bg-opacity-10">
                    <i class="bi bi-people-fill text-primary"></i>
                </div>
                <div>
                    <div class="stat-value"><?= $summary['totalEmp'] ?></div>
                    <div class="stat-label text-muted">Total Employees</div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="card stat-card h-100">
            <div class="card-body d-flex align-items-center gap-3">
                <div class="stat-icon-box bg-success bg-opacity-10">
                    <i class="bi bi-check-circle-fill text-success"></i>
                </div>
                <div>
                    <div class="stat-value"><?= $summary['present'] ?></div>
                    <div class="stat-label text-muted">Present Today</div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="card stat-card h-100">
            <div class="card-body d-flex align-items-center gap-3">
                <div class="stat-icon-box bg-danger bg-opacity-10">
                    <i class="bi bi-x-circle-fill text-danger"></i>
                </div>
                <div>
                    <div class="stat-value"><?= $summary['absent'] ?></div>
                    <div class="stat-label text-muted">Absent Today</div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="card stat-card h-100">
            <div class="card-body d-flex align-items-center gap-3">
                <div class="stat-icon-box bg-warning bg-opacity-10">
                    <i class="bi bi-clock-fill text-warning"></i>
                </div>
                <div>
                    <div class="stat-value"><?= $summary['late'] ?></div>
                    <div class="stat-label text-muted">Late Today</div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Map + Live Feed -->
<div class="row g-3">
    <div class="col-xl-8">
        <div class="card h-100">
            <div class="card-header d-flex align-items-center justify-content-between">
                <span><i class="bi bi-clock-history text-primary me-2"></i>Recent Attendance Logs</span>
                <a href="<?= base_url('attendance') ?>" class="btn btn-sm btn-light border">View All</a>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th class="ps-3 border-0">Employee</th>
                                <th class="border-0">Time</th>
                                <th class="border-0">Action</th>
                                <th class="border-0">Location</th>
                                <th class="pe-3 border-0 text-end">Geofence</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if(empty($recentLogs)): ?>
                            <tr>
                                <td colspan="5" class="text-center text-muted py-4">No recent logs found.</td>
                            </tr>
                            <?php else: ?>
                            <?php foreach($recentLogs as $log): ?>
                            <tr>
                                <td class="ps-3">
                                    <div class="d-flex align-items-center gap-2">
                                        <div class="emp-initials"><?= strtoupper(substr($log['employee_name'], 0, 1)) ?></div>
                                        <div>
                                            <div class="fw-semibold text-dark"><?= esc($log['employee_name']) ?></div>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <div class="fw-medium"><?= date('h:i A', strtotime($log['scanned_at'])) ?></div>
                                    <div class="text-muted" style="font-size:12px;"><?= date('d M Y', strtotime($log['scanned_at'])) ?></div>
                                </td>
                                <td>
                                    <?php 
                                        $badges = [
                                            'check_in'    => 'bg-success-subtle text-success border border-success-subtle',
                                            'check_out'   => 'bg-danger-subtle text-danger border border-danger-subtle',
                                            'break_start' => 'bg-warning-subtle text-warning border border-warning-subtle',
                                            'break_end'   => 'bg-info-subtle text-info border border-info-subtle',
                                        ];
                                        $badgeClass = $badges[$log['type']] ?? 'bg-secondary-subtle text-secondary';
                                    ?>
                                    <span class="badge rounded-pill <?= $badgeClass ?>"><?= esc($log['scan_label']) ?></span>
                                </td>
                                <td>
                                    <?php if($log['location_name']): ?>
                                        <div class="text-dark" style="font-size:13px;"><i class="bi bi-geo-alt text-muted me-1"></i><?= esc($log['location_name']) ?></div>
                                    <?php else: ?>
                                        <span class="text-muted">-</span>
                                    <?php endif; ?>
                                </td>
                                <td class="pe-3 text-end">
                                    <?php if($log['geofence_status'] === 'flagged'): ?>
                                        <span class="badge bg-danger-subtle text-danger"><i class="bi bi-exclamation-triangle me-1"></i>Flagged</span>
                                    <?php else: ?>
                                        <span class="badge bg-success-subtle text-success"><i class="bi bi-shield-check me-1"></i>Inside</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    <div class="col-xl-4">
        <div class="card h-100">
            <div class="card-header">
                <i class="bi bi-activity text-primary me-2"></i>Live Activity
            </div>
            <div class="card-body" id="activity-feed" style="overflow-y:auto; max-height:400px;">
                <?php if (empty($liveData) || $liveData === '[]'): ?>
                    <p class="text-muted text-center mt-4 mb-0">
                        <i class="bi bi-inbox fs-3 d-block mb-2"></i>No active check-ins right now.
                    </p>
                <?php else: ?>
                    <?php foreach (json_decode($liveData, true) as $item): ?>
                    <div class="activity-item">
                        <div class="activity-avatar"><?= strtoupper(substr($item['name'], 0, 1)) ?></div>
                        <div>
                            <div class="fw-semibold small"><?= esc($item['name']) ?></div>
                            <div class="text-muted" style="font-size:11.5px;">
                                <i class="bi bi-geo-alt"></i> <?= esc($item['location_name']) ?>
                                <span class="<?= $item['geofence_status'] === 'flagged' ? 'text-warning' : 'text-success' ?> ms-1">
                                    <i class="bi bi-<?= $item['geofence_status'] === 'flagged' ? 'exclamation-triangle' : 'shield-check' ?>"></i>
                                    <?= $item['geofence_status'] ?>
                                </span>
                            </div>
                            <div class="text-muted" style="font-size:11px;"><?= date('h:i A', strtotime($item['scanned_at'])) ?></div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<script>
// Dashboard specific scripts (if any) can go here.
</script>
<?= $this->endSection() ?>
