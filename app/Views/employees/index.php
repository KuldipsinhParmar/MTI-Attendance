<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>

<!-- Toolbar -->
<div class="d-flex align-items-center gap-2 mb-3 flex-wrap">
    <a href="<?= base_url('employees/create') ?>" class="btn btn-primary">
        <i class="bi bi-plus-lg me-1"></i> Add Employee
    </a>
</div>



<!-- Table card -->
<div class="card">
    <div class="card-body p-0">
        <table class="table table-hover datatable mb-0" id="employees-table" width="100%">
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Department</th>
                    <th>Designation</th>
                    <th>Phone</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($employees as $emp): ?>
                <tr>
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
                        <?php if (isset($status) && $status === 'inactive'): ?>
                            <form method="POST" action="<?= base_url('employees/activate/' . $emp['id']) ?>" class="d-inline"
                                  onsubmit="return confirm('Activate this employee?')">
                                <?= csrf_field() ?>
                                <button class="btn btn-sm btn-outline-success" title="Activate">
                                    <i class="bi bi-person-check"></i>
                                </button>
                            </form>
                        <?php else: ?>
                            <form method="POST" action="<?= base_url('employees/deactivate/' . $emp['id']) ?>" class="d-inline"
                                  onsubmit="return confirm('Deactivate this employee?')">
                                <?= csrf_field() ?>
                                <button class="btn btn-sm btn-outline-danger" title="Deactivate">
                                    <i class="bi bi-person-x"></i>
                                </button>
                            </form>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
<?= $this->endSection() ?>
