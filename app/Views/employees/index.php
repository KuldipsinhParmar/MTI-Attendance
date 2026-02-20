<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>

<!-- Toolbar -->
<div class="d-flex align-items-center gap-2 mb-3 flex-wrap">
    <a href="<?= base_url('employees/create') ?>" class="btn btn-primary">
        <i class="bi bi-plus-lg me-1"></i> Add Employee
    </a>
    <form class="d-flex align-items-center gap-2 ms-auto flex-wrap" method="GET">
        <input type="text" name="search" class="form-control form-control-sm" style="width:180px;"
               placeholder="Search name or code…" value="<?= esc($searchQuery ?? '') ?>">
        <select name="department" class="form-select form-select-sm" style="width:160px;">
            <option value="">All Departments</option>
            <?php foreach ($departments as $d): ?>
                <option value="<?= esc($d['department']) ?>"
                    <?= (($selectedDepartment ?? '') == $d['department']) ? 'selected' : '' ?>>
                    <?= esc($d['department']) ?>
                </option>
            <?php endforeach; ?>
        </select>
        <button type="submit" class="btn btn-outline-secondary btn-sm">
            <i class="bi bi-funnel"></i> Filter
        </button>
    </form>
</div>

<!-- Table card -->
<div class="card">
    <div class="card-body p-0">
        <table class="table table-hover datatable mb-0" id="employees-table" width="100%">
            <thead>
                <tr>
                    <th>Code</th>
                    <th>Name</th>
                    <th>Department</th>
                    <th>Designation</th>
                    <th>Phone</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
            <?php if (empty($employees)): ?>
                <tr><td colspan="6" class="text-center text-muted py-4">No employees found.</td></tr>
            <?php else: ?>
                <?php foreach ($employees as $emp): ?>
                <tr>
                    <td><span class="badge bg-primary-subtle text-primary border border-primary-subtle"><?= esc($emp['employee_code']) ?></span></td>
                    <td>
                        <div class="d-flex align-items-center gap-2">
                            <?php if ($emp['photo']): ?>
                                <img src="<?= base_url($emp['photo']) ?>" class="emp-avatar">
                            <?php else: ?>
                                <div class="emp-initials"><?= strtoupper(substr($emp['name'], 0, 1)) ?></div>
                            <?php endif; ?>
                            <span class="fw-medium"><?= esc($emp['name']) ?></span>
                        </div>
                    </td>
                    <td><?= esc($emp['department']) ?></td>
                    <td><?= esc($emp['designation']) ?></td>
                    <td><?= esc($emp['phone']) ?></td>
                    <td>
                        <a href="<?= base_url('employees/edit/' . $emp['id']) ?>" class="btn btn-sm btn-outline-primary" title="Edit">
                            <i class="bi bi-pencil"></i>
                        </a>
                        <form method="POST" action="<?= base_url('employees/deactivate/' . $emp['id']) ?>" class="d-inline"
                              onsubmit="return confirm('Deactivate this employee?')">
                            <?= csrf_field() ?>
                            <button class="btn btn-sm btn-outline-danger" title="Deactivate">
                                <i class="bi bi-person-x"></i>
                            </button>
                        </form>
                    </td>
                </tr>
                <?php endforeach; ?>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
<?= $this->endSection() ?>
