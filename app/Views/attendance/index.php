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
                    <th>Name</th>
                    <th>Department</th>
                    <th><i class="bi bi-box-arrow-in-right text-success me-1"></i>Shift In</th>
                    <th><i class="bi bi-cup-hot text-warning me-1"></i>Break</th>
                    <th><i class="bi bi-box-arrow-right text-danger me-1"></i>Shift Out</th>
                    <th>Net Hours</th>
                    <th>Status</th>
                    <th class="text-end">Actions</th>
                </tr>
            </thead>

            <tbody>
            <?php foreach ($logs as $row):
                $in      = $row['check_in']    ? date('h:i A', strtotime($row['check_in']))   : '—';
                $brStart = $row['break_start'] ? date('h:i A', strtotime($row['break_start'])): null;
                $brEnd   = $row['break_end']   ? date('h:i A', strtotime($row['break_end']))  : null;
                $out     = $row['check_out']   ? date('h:i A', strtotime($row['check_out']))  : '—';

                if ($brStart && $brEnd)       $breakCell = $brStart . ' → ' . $brEnd;
                elseif ($brStart && !$brEnd)  $breakCell = '<span class="badge bg-warning-subtle text-warning border border-warning-subtle">On Break</span>';
                else                          $breakCell = '—';

                $netMins = isset($row['net_minutes']) && $row['net_minutes'] > 0 ? (int)$row['net_minutes'] : null;
                $netDur  = $netMins !== null
                    ? floor($netMins / 60) . 'h ' . ($netMins % 60 > 0 ? ($netMins % 60) . 'm' : '')
                    : '—';

                $status = !$row['check_in'] ? 'absent'
                        : ($row['geofence_status'] === 'flagged' ? 'flagged' : 'present');
                $badgeClass = match($status) {
                    'present' => 'bg-success-subtle text-success border border-success-subtle',
                    'absent'  => 'bg-danger-subtle text-danger border border-danger-subtle',
                    'flagged' => 'bg-warning-subtle text-warning border border-warning-subtle',
                    default   => 'bg-secondary-subtle text-secondary',
                };
            ?>
            <tr>
                <td class="fw-medium"><?= esc($row['name']) ?></td>
                <td><?= esc($row['department']) ?></td>
                <td><?= $in ?></td>
                <td class="small text-muted"><?= $breakCell ?></td>
                <td><?= $out ?></td>
                <td><?= $netDur ?></td>
                <td><span class="badge <?= $badgeClass ?>"><?= ucfirst($status) ?></span></td>
                <td class="text-end">
                    <?php if ($status !== 'absent'): ?>
                        <form method="POST" action="<?= base_url('attendance/delete/' . $date . '/' . $row['id']) ?>" class="d-inline"
                              onsubmit="return confirm('Delete all attendance logs for <?= esc($row['name']) ?> on this date?')">
                            <?= csrf_field() ?>
                            <button class="btn btn-sm btn-outline-danger" title="Delete Logs">
                                <i class="bi bi-trash"></i>
                            </button>
                        </form>
                    <?php else: ?>
                        —
                    <?php endif; ?>
                </td>
            </tr>
            <?php endforeach; ?>

            </tbody>
        </table>
    </div>
</div>
<?= $this->endSection() ?>
