<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - Real-Time Stock Tracker</title>
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
            padding: 20px 0;
        }
        .register-card {
            background: rgba(28, 37, 65, 0.8);
            border: 1px solid rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(10px);
            border-radius: 16px;
            padding: 40px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.3);
            width: 100%;
            max-width: 480px;
        }
        .brand-logo {
            font-size: 32px;
            font-weight: 700;
            color: #10B981; /* Emerald Green */
            text-align: center;
            margin-bottom: 24px;
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
            padding: 10px 14px;
        }
        .form-control:focus {
            border-color: #10B981 !important;
            box-shadow: 0 0 0 0.25rem rgba(16, 185, 129, 0.25) !important;
        }
        .form-label {
            font-weight: 500;
            color: #A0AEC0;
            margin-bottom: 6px;
            font-size: 14px;
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
        .login-text {
            text-align: center;
            margin-top: 24px;
            color: #A0AEC0;
            font-size: 14px;
        }
        .login-text a {
            color: #10B981;
            text-decoration: none;
            font-weight: 600;
        }
        .login-text a:hover {
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
    </style>
</head>
<body>

    <div class="register-card">
        <div class="brand-logo">
            <i class="fa-solid fa-chart-line"></i> StockScribe
        </div>
        
        <h4 class="text-center mb-4 font-weight-bold">Create Investor Account</h4>
        
        <!-- Alerts -->
        <?php if (session()->getFlashdata('error')): ?>
            <div class="alert alert-danger" role="alert">
                <i class="fa-solid fa-triangle-exclamation me-2"></i><?= esc(session()->getFlashdata('error')) ?>
            </div>
        <?php endif; ?>
        
        <form action="<?= base_url('register/submit') ?>" method="POST">
            <?= csrf_field() ?>
            
            <div class="mb-3">
                <label for="name" class="form-label">Full Name</label>
                <input type="text" class="form-control" id="name" name="name" placeholder="John Doe" required value="<?= old('name') ?>">
                <?php if (isset($validation) && $validation->hasError('name')): ?>
                    <div class="text-danger small mt-1"><?= $validation->getError('name') ?></div>
                <?php endif; ?>
            </div>
            
            <div class="mb-3">
                <label for="email" class="form-label">Email Address</label>
                <input type="email" class="form-control" id="email" name="email" placeholder="name@domain.com" required value="<?= old('email') ?>">
                <?php if (isset($validation) && $validation->hasError('email')): ?>
                    <div class="text-danger small mt-1"><?= $validation->getError('email') ?></div>
                <?php endif; ?>
            </div>
            
            <div class="mb-3">
                <label for="password" class="form-label">Password (Min. 6 chars)</label>
                <input type="password" class="form-control" id="password" name="password" placeholder="••••••••" required>
                <?php if (isset($validation) && $validation->hasError('password')): ?>
                    <div class="text-danger small mt-1"><?= $validation->getError('password') ?></div>
                <?php endif; ?>
            </div>
            
            <div class="mb-4">
                <label for="confirm_password" class="form-label">Confirm Password</label>
                <input type="password" class="form-control" id="confirm_password" name="confirm_password" placeholder="••••••••" required>
                <?php if (isset($validation) && $validation->hasError('confirm_password')): ?>
                    <div class="text-danger small mt-1"><?= $validation->getError('confirm_password') ?></div>
                <?php endif; ?>
            </div>
            
            <button type="submit" class="btn btn-primary w-100 mb-2">
                <i class="fa-solid fa-user-plus me-2"></i>Register & Start Trading
            </button>
        </form>
        
        <div class="login-text">
            Already have an investor account? <a href="<?= base_url('login') ?>">Sign In</a>
        </div>
    </div>

    <!-- Bootstrap 5 Bundle JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
