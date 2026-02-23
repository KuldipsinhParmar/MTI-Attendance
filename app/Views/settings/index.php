<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>

<div class="card" style="max-width:680px;">
    <div class="card-header fw-semibold">
        <i class="bi bi-gear-fill text-primary me-2"></i>System Settings
    </div>
    <div class="card-body">
        <form method="POST" action="<?= base_url('settings') ?>">
            <?= csrf_field() ?>
            
            <div class="d-flex flex-wrap gap-2 mb-4 pb-4 border-bottom">
                <button type="button" class="btn btn-outline-primary btn-sm" onclick="copySignupLink(this)">
                    <i class="bi bi-link-45deg me-1"></i> Copy Public Signup Link
                </button>
            </div>

            <div class="row g-3">
                <div class="col-12">
                    <label class="form-label fw-medium">Company Name</label>
                    <input type="text" name="company_name" class="form-control"
                           value="<?= esc($settings['company_name'] ?? 'MTI Company') ?>">
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-medium">Work Start Time</label>
                    <input type="time" name="work_start_time" class="form-control"
                           value="<?= esc($settings['work_start_time'] ?? '09:00') ?>">
                    <div class="form-text">Used to detect late arrivals.</div>
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-medium">Work End Time</label>
                    <input type="time" name="work_end_time" class="form-control"
                           value="<?= esc($settings['work_end_time'] ?? '18:00') ?>">
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-medium">Default Geofence Radius (meters)</label>
                    <input type="number" name="default_geofence_radius" class="form-control" min="10" max="5000"
                           value="<?= esc($settings['default_geofence_radius'] ?? 50) ?>">
                    <div class="form-text">Applied to new QR codes unless overridden per location.</div>
                </div>
            </div>
            <div class="mt-4">
                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-save me-1"></i> Save Settings
                </button>
            </div>
        </form>
    </div>
</div>

<script>
function copySignupLink(btn) {
    const url = '<?= base_url('signup') ?>';
    navigator.clipboard.writeText(url).then(() => {
        const originalText = btn.innerHTML;
        btn.innerHTML = '<i class="bi bi-check2 me-1"></i> Copied!';
        btn.classList.remove('btn-outline-primary');
        btn.classList.add('btn-success', 'text-white');
        
        setTimeout(() => {
            btn.innerHTML = originalText;
            btn.classList.remove('btn-success', 'text-white');
            btn.classList.add('btn-outline-primary');
        }, 2000);
    }).catch(err => {
        console.error('Failed to copy: ', err);
        alert('Failed to copy link. The URL is: ' + url);
    });
}
</script>

<?= $this->endSection() ?>
