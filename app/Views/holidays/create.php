<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>

<div class="card" style="max-width:720px;">
    <div class="card-header">
        <i class="bi bi-calendar-event me-2 text-primary"></i> Add New Holiday
    </div>
    <div class="card-body">
        <form action="<?= base_url('holidays/store') ?>" method="POST">
            <?= csrf_field() ?>
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label fw-medium">Holiday Name <span class="text-danger">*</span></label>
                    <input type="text" name="name" class="form-control" required
                           value="<?= old('name') ?>" placeholder="e.g. New Year's Day">
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-medium">Date <span class="text-danger">*</span></label>
                    <input type="date" name="date" class="form-control" required
                           value="<?= old('date') ?>">
                </div>
            </div>

            <div class="d-flex gap-2 mt-4">
                <a href="<?= base_url('holidays') ?>" class="btn btn-secondary">
                    <i class="bi bi-x-lg me-1"></i> Cancel
                </a>
                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-check-lg me-1"></i> Save Holiday
                </button>
            </div>
        </form>
    </div>
</div>

<?= $this->endSection() ?>
