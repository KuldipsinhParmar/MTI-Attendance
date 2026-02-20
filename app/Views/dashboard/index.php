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
                <span><i class="bi bi-geo-alt-fill text-primary me-2"></i>Live Location Map</span>
                <span class="badge bg-success-subtle text-success border border-success-subtle">
                    <span class="pulse-dot me-1"></span> Live
                </span>
            </div>
            <div class="card-body p-2">
                <div id="dashboard-map"></div>
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
document.addEventListener('DOMContentLoaded', function() {
    const liveData = <?= $liveData ?>;
    const map = L.map('dashboard-map').setView([23.0225, 72.5714], 14);
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', { attribution: '© OpenStreetMap' }).addTo(map);
    liveData.forEach(d => {
        const color = d.geofence_status === 'flagged' ? '#ffc107' : '#198754';
        L.circleMarker([d.latitude, d.longitude], {
            radius: 10, fillColor: color, color: '#fff', weight: 2, fillOpacity: 0.9
        }).addTo(map).bindPopup(`<b>${d.name}</b><br>${d.location_name}<br><small>${d.scanned_at}</small>`);
    });
});
</script>
<?= $this->endSection() ?>
