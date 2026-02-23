<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign Up — MTI Attendance</title>
    <!-- QR Code Favicon -->
    <link rel="icon" type="image/svg+xml" href="data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 16 16' fill='%230d6efd'%3E%3Cpath d='M0 .5A.5.5 0 0 1 .5 0h3a.5.5 0 0 1 0 1H1v2.5a.5.5 0 0 1-1 0v-3zm12 0a.5.5 0 0 1 .5-.5h3a.5.5 0 0 1 .5.5v3a.5.5 0 0 1-1 0V1h-2.5a.5.5 0 0 1-.5-.5zM.5 16a.5.5 0 0 1-.5-.5v-3a.5.5 0 0 1 1 0v2.5h2.5a.5.5 0 0 1 0 1h-3zm12 0a.5.5 0 0 1-.5-.5v-3a.5.5 0 0 1 1 0v2.5h2.5a.5.5 0 0 1 0 1h-3zM3 3h10v10H3V3z'/%3E%3Cpath d='M4 4h8v8H4z'/%3E%3C/svg%3E">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?= base_url('assets/css/style.css') ?>">
</head>
<body class="login-bg">

<div class="login-card card p-4 p-md-5 mx-auto" style="max-width: 500px;">
    <!-- Logo -->
    <div class="text-center mb-4">
        <div class="login-icon-wrap mx-auto mb-3">
            <i class="bi bi-person-plus"></i>
        </div>
        <h4 class="fw-bold mb-1">MTI Attendance</h4>
        <p class="text-muted small mb-0">Employee Registration</p>
    </div>

    <?php if (session()->getFlashdata('errors')): ?>
        <div class="alert alert-danger alert-sm py-2">
            <ul class="mb-0 ps-3">
            <?php foreach (session()->getFlashdata('errors') as $error): ?>
                <li><?= esc($error) ?></li>
            <?php endforeach ?>
            </ul>
        </div>
    <?php endif; ?>
    <?php if (session()->getFlashdata('error')): ?>
        <div class="alert alert-danger alert-sm d-flex align-items-center gap-2 py-2">
            <i class="bi bi-exclamation-circle-fill"></i>
            <?= session()->getFlashdata('error') ?>
        </div>
    <?php endif; ?>

    <form action="<?= base_url('signup') ?>" method="POST">
        <?= csrf_field() ?>
        
        <div class="mb-3">
            <label class="form-label fw-medium">Full Name <span class="text-danger">*</span></label>
            <div class="input-group">
                <span class="input-group-text"><i class="bi bi-person"></i></span>
                <input type="text" name="name" class="form-control" placeholder="John Doe" value="<?= old('name') ?>" required autofocus>
            </div>
        </div>

        <div class="mb-3">
            <label class="form-label fw-medium">Email Address <span class="text-danger">*</span></label>
            <div class="input-group">
                <span class="input-group-text"><i class="bi bi-envelope"></i></span>
                <input type="email" name="email" class="form-control" placeholder="user@example.com" value="<?= old('email') ?>" required>
            </div>
        </div>

        <div class="mb-3">
            <label class="form-label fw-medium">Phone Number <span class="text-danger">*</span></label>
            <div class="input-group">
                <span class="input-group-text"><i class="bi bi-telephone"></i></span>
                <input type="text" name="phone" class="form-control" placeholder="Your Phone Number" value="<?= old('phone') ?>" required>
            </div>
        </div>



        <div class="mb-4">
            <label class="form-label fw-medium">Password <span class="text-danger">*</span></label>
            <div class="input-group">
                <span class="input-group-text"><i class="bi bi-lock"></i></span>
                <input type="password" name="password" class="form-control" placeholder="••••••••" required>
            </div>
            <div class="form-text">Minimum 6 characters.</div>
        </div>

        <button type="submit" class="btn btn-primary w-100 py-2 fw-semibold mb-3">
            <i class="bi bi-person-plus me-1"></i> Create Account
        </button>
    </form>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
