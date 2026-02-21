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

            <hr class="my-4">
            <h6 class="fw-semibold text-primary mb-3">
                <i class="bi bi-phone me-2"></i>App Login Credentials
            </h6>
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label fw-medium">
                        Username <span class="text-danger">*</span>
                    </label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="bi bi-person-badge"></i></span>
                        <input type="text" name="username" class="form-control"
                               placeholder="e.g. rahul.sharma"
                               autocomplete="username"
                               value="<?= esc(old('username', $employee['username'] ?? '')) ?>"
                               required>
                    </div>
                    <div class="form-text">Employee uses this to log into the mobile app.</div>
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-medium">
                        Password
                        <?php if (!$employee): ?>
                            <span class="text-danger">*</span>
                        <?php else: ?>
                            <span class="text-muted fw-normal fs-6">(leave blank to keep current)</span>
                        <?php endif; ?>
                    </label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="bi bi-lock"></i></span>
                        <input type="password" name="password" class="form-control"
                               placeholder="Min. 6 characters"
                               autocomplete="new-password"
                               <?= !$employee ? 'required' : '' ?>>
                        <button type="button" class="btn btn-outline-secondary"
                                onclick="togglePw(this)" title="Show/hide password">
                            <i class="bi bi-eye"></i>
                        </button>
                    </div>
                </div>
            </div>

            <div class="row g-3 mt-2">
                <div class="col-md-12">
                    <div class="form-check form-switch bg-light p-3 rounded border">
                        <input class="form-check-input ms-0 me-3 mt-1" type="checkbox" role="switch" id="allow_anywhere_attendance" name="allow_anywhere_attendance" value="1" <?= old('allow_anywhere_attendance', $employee['allow_anywhere_attendance'] ?? 0) ? 'checked' : '' ?> style="transform: scale(1.3);">
                        <label class="form-check-label fw-medium text-dark" for="allow_anywhere_attendance">
                            Allow Anywhere Attendance
                            <div class="text-muted fw-normal fs-6" style="margin-top: 2px;">
                                If enabled, this employee can scan QR codes from any location without triggering distance warnings.
                            </div>
                        </label>
                    </div>
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

<script>
function togglePw(btn) {
    const input = btn.closest('.input-group').querySelector('input[type="password"], input[type="text"]');
    const icon  = btn.querySelector('i');
    if (input.type === 'password') {
        input.type = 'text';
        icon.className = 'bi bi-eye-slash';
    } else {
        input.type = 'password';
        icon.className = 'bi bi-eye';
    }
}
</script>
<?= $this->endSection() ?>

