<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>

<div class="row g-3">
    <!-- QR Image Card -->
    <div class="col-md-4">
        <div class="card text-center h-100">
            <div class="card-header fw-semibold">
                <i class="bi bi-qr-code text-primary me-2"></i><?= esc($qrcode['location_name']) ?>
            </div>
            <div class="card-body">
                <img src="<?= base_url($qrImage) ?>" alt="QR Code" class="qr-img img-fluid mb-3 rounded">
                <p class="small text-muted mb-1">Token:</p>
                <code class="small"><?= esc($qrcode['token']) ?></code>
                <div class="d-flex justify-content-center gap-3 mt-2 small text-muted">
                    <span><i class="bi bi-geo-alt me-1"></i><?= $qrcode['latitude'] ?>, <?= $qrcode['longitude'] ?></span>
                    <span><i class="bi bi-bullseye me-1"></i><?= $qrcode['geofence_radius'] ?>m radius</span>
                </div>
            </div>
            <div class="card-footer d-grid gap-2">
                <button onclick="window.print()" class="btn btn-primary">
                    <i class="bi bi-printer me-1"></i> Print QR Code
                </button>
                <a href="<?= base_url($qrImage) ?>" download class="btn btn-outline-secondary">
                    <i class="bi bi-download me-1"></i> Download PNG
                </a>
            </div>
        </div>
    </div>

    <!-- Map Card -->
    <div class="col-md-8">
        <div class="card h-100">
            <div class="card-header d-flex align-items-center justify-content-between">
                <span><i class="bi bi-map text-primary me-2"></i>Location on Map</span>
                <a href="<?= base_url('qr-codes/edit/' . $qrcode['id']) ?>" class="btn btn-sm btn-outline-primary">
                    <i class="bi bi-pencil me-1"></i> Edit Location / Radius
                </a>
            </div>
            <div class="card-body p-2">
                <div id="qr-map"></div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const lat    = <?= $qrcode['latitude']  ?? 23.0225 ?>;
    const lng    = <?= $qrcode['longitude'] ?? 72.5714 ?>;
    const radius = <?= $qrcode['geofence_radius'] ?>;
    const map    = L.map('qr-map').setView([lat, lng], 17);
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png').addTo(map);
    L.marker([lat, lng]).addTo(map).bindPopup('<?= esc($qrcode['location_name']) ?>').openPopup();
    L.circle([lat, lng], { radius: radius, color: '#0d6efd', fillOpacity: 0.12 }).addTo(map);
});
</script>
<?= $this->endSection() ?>
