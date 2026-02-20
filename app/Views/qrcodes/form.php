<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>

<div class="card" style="max-width:680px;">
    <div class="card-header fw-semibold">
        <i class="bi bi-qr-code text-primary me-2"></i>
        <?= $qrcode ? 'Edit QR Code' : 'Generate New QR Code' ?>
    </div>
    <div class="card-body">
        <form method="POST" action="<?= base_url($qrcode ? 'qr-codes/update/' . $qrcode['id'] : 'qr-codes/store') ?>">
            <?= csrf_field() ?>
            <div class="row g-3 mb-3">
                <div class="col-md-6">
                    <label class="form-label fw-medium">Location Name <span class="text-danger">*</span></label>
                    <input type="text" name="location_name" class="form-control" required
                           placeholder="e.g. Main Gate"
                           value="<?= esc(old('location_name', $qrcode['location_name'] ?? '')) ?>">
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-medium">Geofence Radius (m) <span class="text-danger">*</span></label>
                    <input type="number" name="geofence_radius" class="form-control" required min="10" max="5000"
                           value="<?= esc(old('geofence_radius', $qrcode['geofence_radius'] ?? 50)) ?>">
                    <div class="form-text">Default 50m. How far employee can be to scan in.</div>
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-medium">Latitude <span class="text-danger">*</span></label>
                    <input type="text" name="latitude" id="lat-input" class="form-control" required
                           placeholder="23.0225"
                           value="<?= esc(old('latitude', $qrcode['latitude'] ?? '')) ?>">
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-medium">Longitude <span class="text-danger">*</span></label>
                    <input type="text" name="longitude" id="lng-input" class="form-control" required
                           placeholder="72.5714"
                           value="<?= esc(old('longitude', $qrcode['longitude'] ?? '')) ?>">
                </div>
            </div>

            <div class="alert alert-info py-2 small">
                <i class="bi bi-info-circle me-1"></i> Click on the map to auto-fill latitude &amp; longitude.
            </div>
            <div id="picker-map" class="mb-3"></div>

            <div class="d-flex gap-2">
                <a href="<?= base_url('qr-codes') ?>" class="btn btn-secondary">
                    <i class="bi bi-x-lg me-1"></i> Cancel
                </a>
                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-check-lg me-1"></i>
                    <?= $qrcode ? 'Update QR Code' : 'Generate QR Code' ?>
                </button>
            </div>
        </form>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const initLat = parseFloat(document.getElementById('lat-input').value) || 23.0225;
    const initLng = parseFloat(document.getElementById('lng-input').value) || 72.5714;
    const map = L.map('picker-map').setView([initLat, initLng], 15);
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png').addTo(map);
    let marker = L.marker([initLat, initLng]).addTo(map);
    map.on('click', function(e) {
        marker.setLatLng(e.latlng);
        document.getElementById('lat-input').value = e.latlng.lat.toFixed(8);
        document.getElementById('lng-input').value = e.latlng.lng.toFixed(8);
    });
});
</script>
<?= $this->endSection() ?>
