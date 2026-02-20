<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>

<!-- Filter bar -->
<div class="card mb-3">
    <div class="card-body py-2">
        <form class="d-flex align-items-center gap-2 flex-wrap" method="GET">
            <input type="date" name="date" class="form-control form-control-sm" style="width:160px;"
                   value="<?= esc($date) ?>">
            <select name="employee_id" class="form-select form-select-sm" style="width:160px;">
                <option value="">All Employees</option>
                <?php foreach ($employees as $emp): ?>
                    <option value="<?= $emp['id'] ?>" <?= (($selectedEmployee ?? '') == $emp['id']) ? 'selected' : '' ?>>
                        <?= esc($emp['name']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
            <select name="department" class="form-select form-select-sm" style="width:150px;">
                <option value="">All Departments</option>
                <?php foreach ($departments as $d): ?>
                    <option value="<?= esc($d['department']) ?>"
                        <?= (($selectedDepartment ?? '') == $d['department']) ? 'selected' : '' ?>>
                        <?= esc($d['department']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
            <button type="submit" class="btn btn-primary btn-sm">
                <i class="bi bi-funnel me-1"></i> Filter
            </button>
        </form>
    </div>
</div>

<!-- Table -->
<div class="card">
    <div class="card-body p-0">
        <table class="table table-hover datatable mb-0" id="attendance-table" width="100%">
            <thead>
                <tr>
                    <th>Code</th>
                    <th>Name</th>
                    <th>Department</th>
                    <th>Check-In</th>
                    <th>Check-Out</th>
                    <th>Duration</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($logs as $row):
                $in     = $row['check_in']  ? date('h:i A', strtotime($row['check_in']))  : '—';
                $out    = $row['check_out'] ? date('h:i A', strtotime($row['check_out'])) : '—';
                $dur    = ($row['check_in'] && $row['check_out'])
                        ? round((strtotime($row['check_out']) - strtotime($row['check_in'])) / 3600, 1) . 'h'
                        : '—';
                $status = !$row['check_in'] ? 'absent' : ($row['geofence_status'] === 'flagged' ? 'flagged' : 'present');
                $badgeClass = match($status) {
                    'present' => 'bg-success-subtle text-success border border-success-subtle',
                    'absent'  => 'bg-danger-subtle text-danger border border-danger-subtle',
                    'flagged' => 'bg-warning-subtle text-warning border border-warning-subtle',
                    default   => 'bg-secondary-subtle text-secondary'
                };
            ?>
            <tr>
                <td><span class="badge bg-primary-subtle text-primary border border-primary-subtle"><?= esc($row['employee_code']) ?></span></td>
                <td class="fw-medium"><?= esc($row['name']) ?></td>
                <td><?= esc($row['department']) ?></td>
                <td><?= $in ?></td>
                <td><?= $out ?></td>
                <td><?= $dur ?></td>
                <td><span class="badge <?= $badgeClass ?>"><?= ucfirst($status) ?></span></td>
            </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
<?= $this->endSection() ?>
