<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>

<div class="card" style="max-width:720px;">
    <div class="card-header">
        <i class="bi bi-person-fill me-2 text-primary"></i>
        <?= $employee ? 'Edit Employee' : 'Add New Employee' ?>
    </div>
    <div class="card-body">

        <?php if (!empty($errors) || session()->getFlashdata('errors')): ?>
            <div class="alert alert-danger">
                <?php foreach (($errors ?? session()->getFlashdata('errors') ?? []) as $e): ?>
                    <div><i class="bi bi-x-circle me-1"></i><?= esc($e) ?></div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <form method="POST" action="<?= base_url($employee ? 'employees/update/' . $employee['id'] : 'employees/store') ?>"
              enctype="multipart/form-data">
            <?= csrf_field() ?>
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label fw-medium">Full Name <span class="text-danger">*</span></label>
                    <input type="text" name="name" class="form-control" required
                           value="<?= esc(old('name', $employee['name'] ?? '')) ?>">
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-medium">Email</label>
                    <input type="email" name="email" class="form-control"
                           value="<?= esc(old('email', $employee['email'] ?? '')) ?>">
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-medium">Phone</label>
                    <input type="text" name="phone" class="form-control"
                           value="<?= esc(old('phone', $employee['phone'] ?? '')) ?>">
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-medium">Department <span class="text-danger">*</span></label>
                    <input type="text" name="department" class="form-control" list="dept-list" required
                           value="<?= esc(old('department', $employee['department'] ?? '')) ?>">
                    <datalist id="dept-list">
                        <?php foreach ($departments as $d): ?>
                            <option value="<?= esc($d['department']) ?>">
                        <?php endforeach; ?>
                    </datalist>
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-medium">Designation <span class="text-danger">*</span></label>
                    <input type="text" name="designation" class="form-control" required
                           value="<?= esc(old('designation', $employee['designation'] ?? '')) ?>">
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-medium">Join Date</label>
                    <input type="date" name="join_date" class="form-control"
                           value="<?= esc(old('join_date', $employee['join_date'] ?? '')) ?>">
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-medium">Photo</label>
                    <input type="file" name="photo" class="form-control" accept="image/*">
                    <?php if (!empty($employee['photo'])): ?>
                        <img src="<?= base_url($employee['photo']) ?>" class="mt-2 rounded" style="height:60px;">
                    <?php endif; ?>
                </div>
            </div>
            <div class="d-flex gap-2 mt-4">
                <a href="<?= base_url('employees') ?>" class="btn btn-secondary">
                    <i class="bi bi-x-lg me-1"></i> Cancel
                </a>
                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-check-lg me-1"></i>
                    <?= $employee ? 'Update Employee' : 'Add Employee' ?>
                </button>
            </div>
        </form>
    </div>
</div>
<?= $this->endSection() ?>
