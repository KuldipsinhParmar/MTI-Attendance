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
                <span><i class="bi bi-calendar3 text-primary me-2"></i>Attendance Calendar</span>
                <a href="<?= base_url('attendance') ?>" class="btn btn-sm btn-light border">View Attendance</a>
            </div>
            <div class="card-body p-3">
                <div id="dashboard-calendar" style="min-height: 450px;"></div>
            </div>
        </div>
    </div>
    <div class="col-xl-4">
        <style>
            /* FullCalendar Custom Overrides */
            #dashboard-calendar a {
                text-decoration: none !important;
            }
            #dashboard-calendar .fc-event {
                cursor: default;
            }
            /* Make Sundays red */
            #dashboard-calendar .fc-day-sun .fc-col-header-cell-cushion,
            #dashboard-calendar .fc-day-sun .fc-daygrid-day-number {
                color: #dc3545 !important;
            }
            /* Improve list view styling */
            .fc-list-event-title a {
                color: inherit !important;
            }
        </style>
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
document.addEventListener('DOMContentLoaded', function() {
    var calendarEl = document.getElementById('dashboard-calendar');
    
    if (calendarEl) {
        var calendar = new FullCalendar.Calendar(calendarEl, {
            initialView: 'dayGridMonth',
            headerToolbar: {
                left: 'prev,next today',
                center: 'title',
                right: 'dayGridMonth,listMonth'
            },
            themeSystem: 'bootstrap5',
            height: 'auto',
            events: <?= $calendarEvents ?? '[]' ?>
        });
        calendar.render();
    }
});
</script>
<?= $this->endSection() ?>
