<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>

<div class="row justify-content-center">
    <div class="col-lg-6 col-md-8">
        <div class="card shadow-sm border-0 mt-3">
            <div class="card-header bg-white border-bottom pt-3 pb-2 d-flex justify-content-between align-items-center">
                <h5 class="card-title fw-semibold mb-0">
                    <i class="bi bi-pencil-square text-primary me-2"></i>Edit Attendance
                </h5>
                <a href="<?= base_url('attendance?date=' . $date) ?>" class="btn btn-sm btn-outline-secondary">
                    <i class="bi bi-arrow-left"></i> Back
                </a>
            </div>
            <div class="card-body">
                <div class="alert alert-secondary py-2 border-secondary-subtle">
                    <strong>Employee:</strong> <?= esc($employee['name'] ?? $log['name']) ?> <br>
                    <strong>Date:</strong> <?= date('d M Y', strtotime($date)) ?>
                </div>

                <form method="POST" action="<?= base_url('attendance/update/' . $date . '/' . $employee['id']) ?>">
                    <?= csrf_field() ?>

                    <?php 
                        // Format times to H:i for the input[type=time]
                        $checkIn    = $log['check_in'] ? date('H:i', strtotime($log['check_in'])) : '';
                        $breakStart = $log['break_start'] ? date('H:i', strtotime($log['break_start'])) : '';
                        $breakEnd   = $log['break_end'] ? date('H:i', strtotime($log['break_end'])) : '';
                        $checkOut   = $log['check_out'] ? date('H:i', strtotime($log['check_out'])) : '';
                    ?>

                    <div class="row mb-3">
                        <div class="col-6">
                            <label class="form-label text-success fw-medium"><i class="bi bi-box-arrow-in-right me-1"></i>Shift In</label>
                            <input type="time" name="check_in" class="form-control" value="<?= $checkIn ?>">
                        </div>
                        <div class="col-6">
                            <label class="form-label text-danger fw-medium"><i class="bi bi-box-arrow-right me-1"></i>Shift Out</label>
                            <input type="time" name="check_out" class="form-control" value="<?= $checkOut ?>">
                        </div>
                    </div>

                    <div class="row mb-4">
                        <div class="col-12">
                            <label class="form-label text-warning fw-medium"><i class="bi bi-cup-hot me-1"></i>Breaks</label>
                            <div id="breaks-container">
                                <?php 
                                $breaks = $log['breaks'] ?? [];
                                if (empty($breaks)) {
                                    $breaks[] = ['start' => null, 'end' => null];
                                }
                                foreach ($breaks as $idx => $break): 
                                    $bStart = $break['start'] ? date('H:i', strtotime($break['start'])) : '';
                                    $bEnd   = $break['end'] ? date('H:i', strtotime($break['end'])) : '';
                                ?>
                                <div class="row mb-2 break-row">
                                    <div class="col-5">
                                        <input type="time" name="break_starts[]" class="form-control" value="<?= $bStart ?>">
                                    </div>
                                    <div class="col-5">
                                        <input type="time" name="break_ends[]" class="form-control" value="<?= $bEnd ?>">
                                    </div>
                                    <div class="col-2">
                                        <button type="button" class="btn btn-outline-danger w-100 remove-break"><i class="bi bi-x-lg"></i></button>
                                    </div>
                                </div>
                                <?php endforeach; ?>
                            </div>
                            <button type="button" class="btn btn-sm btn-outline-warning mt-2" id="add-break-btn"><i class="bi bi-plus-circle me-1"></i> Add Another Break</button>
                        </div>
                    </div>

                    <div class="d-grid mt-4">
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-save me-1"></i> Save Changes
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const container = document.getElementById('breaks-container');
    const addBtn = document.getElementById('add-break-btn');

    addBtn.addEventListener('click', function() {
        const row = document.createElement('div');
        row.className = 'row mb-2 break-row';
        row.innerHTML = `
            <div class="col-5">
                <input type="time" name="break_starts[]" class="form-control">
            </div>
            <div class="col-5">
                <input type="time" name="break_ends[]" class="form-control">
            </div>
            <div class="col-2">
                <button type="button" class="btn btn-outline-danger w-100 remove-break"><i class="bi bi-x-lg"></i></button>
            </div>
        `;
        container.appendChild(row);
    });

    container.addEventListener('click', function(e) {
        if (e.target.closest('.remove-break')) {
            const row = e.target.closest('.break-row');
            if (document.querySelectorAll('.break-row').length > 1) {
                row.remove();
            } else {
                row.querySelectorAll('input').forEach(input => input.value = '');
            }
        }
    });
});
</script>

<?= $this->endSection() ?>
