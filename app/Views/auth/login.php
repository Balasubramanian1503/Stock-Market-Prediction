<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Real-Time Stock Tracker</title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/css/bootstrap.min.css" rel="stylesheet" onerror="this.onerror=null;this.href='https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css';">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <!-- FontAwesome Icons -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    
    <style>
        body {
            font-family: 'Inter', sans-serif;
            background: linear-gradient(135deg, #0B132B 0%, #1C2541 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #F8F9FA;
        }
        .login-card {
            background: rgba(28, 37, 65, 0.8);
            border: 1px solid rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(10px);
            border-radius: 16px;
            padding: 40px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.3);
            width: 100%;
            max-width: 450px;
        }
        .brand-logo {
            font-size: 32px;
            font-weight: 700;
            color: #10B981; /* Emerald Green */
            text-align: center;
            margin-bottom: 30px;
            letter-spacing: -0.5px;
        }
        .brand-logo i {
            margin-right: 8px;
        }
        .form-control {
            background-color: #0B132B !important;
            border: 1px solid rgba(255, 255, 255, 0.15) !important;
            color: #F8F9FA !important;
            border-radius: 8px;
            padding: 12px 16px;
        }
        .form-control:focus {
            border-color: #10B981 !important;
            box-shadow: 0 0 0 0.25rem rgba(16, 185, 129, 0.25) !important;
        }
        .form-label {
            font-weight: 500;
            color: #A0AEC0;
            margin-bottom: 8px;
        }
        .btn-primary {
            background: linear-gradient(135deg, #10B981 0%, #059669 100%);
            border: none;
            padding: 12px;
            font-weight: 600;
            border-radius: 8px;
            transition: all 0.3s ease;
        }
        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(16, 185, 129, 0.4);
            background: linear-gradient(135deg, #059669 0%, #047857 100%);
        }
        .forgot-link {
            color: #10B981;
            text-decoration: none;
            font-size: 14px;
            transition: color 0.2s ease;
        }
        .forgot-link:hover {
            color: #34D399;
            text-decoration: underline;
        }
        .signup-text {
            text-align: center;
            margin-top: 24px;
            color: #A0AEC0;
            font-size: 14px;
        }
        .signup-text a {
            color: #10B981;
            text-decoration: none;
            font-weight: 600;
        }
        .signup-text a:hover {
            color: #34D399;
            text-decoration: underline;
        }
        .alert {
            border-radius: 8px;
            border: none;
            font-size: 14px;
        }
        .alert-danger {
            background-color: rgba(239, 68, 68, 0.2);
            color: #FCA5A5;
            border-left: 4px solid #EF4444;
        }
        .alert-success {
            background-color: rgba(16, 185, 129, 0.2);
            color: #A7F3D0;
            border-left: 4px solid #10B981;
        }
    </style>
</head>
<body>

    <div class="login-card">
        <div class="brand-logo">
            <i class="fa-solid fa-chart-line-up"></i><i class="fa-solid fa-chart-line"></i> StockScribe
        </div>
        
        <h4 class="text-center mb-4 font-weight-bold">Sign In</h4>
        
        <!-- Alerts -->
        <?php if (session()->getFlashdata('error')): ?>
            <div class="alert alert-danger" role="alert">
                <i class="fa-solid fa-triangle-exclamation me-2"></i><?= esc(session()->getFlashdata('error')) ?>
            </div>
        <?php endif; ?>
        
        <?php if (session()->getFlashdata('success')): ?>
            <div class="alert alert-success" role="alert">
                <i class="fa-solid fa-circle-check me-2"></i><?= esc(session()->getFlashdata('success')) ?>
            </div>
        <?php endif; ?>
        
        <form action="<?= base_url('login/submit') ?>" method="POST">
            <?= csrf_field() ?>
            
            <div class="mb-3">
                <label for="email" class="form-label">Email Address</label>
                <input type="email" class="form-control" id="email" name="email" placeholder="name@domain.com" required value="<?= old('email') ?>">
                <?php if (isset($validation) && $validation->hasError('email')): ?>
                    <div class="text-danger small mt-1"><?= $validation->getError('email') ?></div>
                <?php endif; ?>
            </div>
            
            <div class="mb-3">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <label for="password" class="form-label mb-0">Password</label>
                    <a href="<?= base_url('forgot-password') ?>" class="forgot-link">Forgot Password?</a>
                </div>
                <input type="password" class="form-control" id="password" name="password" placeholder="••••••••" required>
                <?php if (isset($validation) && $validation->hasError('password')): ?>
                    <div class="text-danger small mt-1"><?= $validation->getError('password') ?></div>
                <?php endif; ?>
            </div>
            
            <div class="mb-4 form-check">
                <input type="checkbox" class="form-check-input" id="remember" style="cursor:pointer;">
                <label class="form-check-label text-muted small" for="remember" style="cursor:pointer;">Remember me on this device</label>
            </div>
            
            <button type="submit" class="btn btn-primary w-100 mb-2">
                <i class="fa-solid fa-right-to-bracket me-2"></i>Login to Dashboard
            </button>
        </form>
        
        <div class="signup-text">
            Don't have an account? <a href="<?= base_url('register') ?>">Create Account</a>
        </div>
    </div>

    <!-- Bootstrap 5 Bundle JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
