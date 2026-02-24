<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>

<!-- Toolbar -->
<div class="d-flex align-items-center gap-2 mb-3 flex-wrap">
    <a href="<?= base_url('holidays/create') ?>" class="btn btn-primary">
        <i class="bi bi-plus-lg me-1"></i> Add Holiday
    </a>
</div>

<!-- Table card -->
<div class="card">
    <div class="card-body p-0">
        <table class="table table-hover datatable mb-0" id="holidaysTable" width="100%">
            <thead>
                <tr>
                    <th>Holiday Name</th>
                    <th>Date</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($holidays as $holiday): ?>
                <tr>
                    <td>
                        <span class="fw-medium text-dark"><i class="bi bi-calendar-event me-2 text-primary"></i><?= esc($holiday['name']) ?></span>
                    </td>
                    <td><?= date('M d, Y', strtotime($holiday['date'])) ?></td>
                    <td>
                        <a href="<?= base_url('holidays/edit/' . $holiday['id']) ?>" class="btn btn-sm btn-outline-primary" title="Edit">
                            <i class="bi bi-pencil"></i>
                        </a>
                        <form action="<?= base_url('holidays/delete/' . $holiday['id']) ?>" method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to delete this holiday?');">
                            <?= csrf_field() ?>
                            <button type="submit" class="btn btn-sm btn-outline-danger" title="Delete">
                                <i class="bi bi-trash"></i>
                            </button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
            <?php if (empty($holidays)): ?>
                <tr>
                    <td colspan="3" class="text-center py-5 text-muted">
                        <i class="bi bi-calendar-x display-4 d-block mb-3 text-secondary opacity-50"></i>
                        No holidays found. Click "Add Holiday" to create one.
                    </td>
                </tr>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>



<?= $this->endSection() ?>
