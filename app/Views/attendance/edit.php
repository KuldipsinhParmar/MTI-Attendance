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
                        <div class="col-6">
                            <label class="form-label text-warning fw-medium"><i class="bi bi-cup-hot me-1"></i>Break Start</label>
                            <input type="time" name="break_start" class="form-control" value="<?= $breakStart ?>">
                        </div>
                        <div class="col-6">
                            <label class="form-label text-warning fw-medium"><i class="bi bi-cup-fill me-1"></i>Break End</label>
                            <input type="time" name="break_end" class="form-control" value="<?= $breakEnd ?>">
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

<?= $this->endSection() ?>
