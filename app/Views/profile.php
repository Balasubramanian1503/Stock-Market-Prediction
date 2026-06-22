<?= $this->extend('layouts/main') ?>

<?= $this->section('title') ?>
My Profile
<?= $this->endSection() ?>

<?= $this->section('content') ?>

<!-- PAGE HEADER -->
<div class="mb-4">
    <h2 class="fw-bold mb-1">My Profile</h2>
    <p class="text-muted mb-0">View your account details and update your security credentials.</p>
</div>

<!-- ALERTS -->
<?php if (session()->getFlashdata('success')): ?>
    <div class="alert alert-success alert-dismissible fade show border border-success bg-profit-soft text-profit mb-4" role="alert">
        <i class="fa-solid fa-circle-check me-2"></i><?= esc(session()->getFlashdata('success')) ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
<?php endif; ?>

<?php if (session()->getFlashdata('error')): ?>
    <div class="alert alert-danger alert-dismissible fade show border border-danger bg-loss-soft text-loss mb-4" role="alert">
        <i class="fa-solid fa-triangle-exclamation me-2"></i><?= esc(session()->getFlashdata('error')) ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
<?php endif; ?>

<div class="row g-4">
    <!-- LEFT: ACCOUNT STATS CARD -->
    <div class="col-12 col-lg-4">
        <div class="card-custom text-center py-5">
            <div class="mx-auto mb-3 user-avatar" style="width: 80px; height: 80px; font-size: 32px; border: 4px solid var(--border-color);">
                <?= strtoupper(substr($user['name'], 0, 1)) ?>
            </div>
            
            <h4 class="fw-bold text-color mb-1"><?= esc($user['name']) ?></h4>
            <span class="badge bg-success bg-opacity-10 text-profit border border-success rounded-pill px-3 py-1 mb-4" style="font-size: 12px; text-transform: uppercase;">
                <i class="fa-solid fa-shield-halved me-1"></i><?= esc($user['role']) ?>
            </span>
            
            <div class="text-start border-top pt-4 mt-2" style="border-color: var(--border-color) !important;">
                <div class="mb-3 d-flex justify-content-between align-items-center">
                    <span class="text-muted small">Status</span>
                    <span class="badge bg-success bg-opacity-10 text-profit px-2.5 py-1 rounded small">
                        <i class="fa-solid fa-circle me-1 small"></i>Active
                    </span>
                </div>
                
                <div class="mb-3 d-flex justify-content-between align-items-center">
                    <span class="text-muted small">Registered Email</span>
                    <span class="fw-semibold text-color small"><?= esc($user['email']) ?></span>
                </div>
                
                <div class="d-flex justify-content-between align-items-center">
                    <span class="text-muted small">Joined Date</span>
                    <span class="fw-semibold text-color small"><?= date('F d, Y', strtotime($user['created_at'])) ?></span>
                </div>
            </div>
        </div>
    </div>
    
    <!-- RIGHT: EDIT FORM PANEL -->
    <div class="col-12 col-lg-8">
        <div class="card-custom h-100">
            <h5 class="fw-bold mb-4"><i class="fa-solid fa-user-pen me-2 text-profit"></i>Update Personal Details</h5>
            
            <form action="<?= base_url('profile/update') ?>" method="POST">
                <?= csrf_field() ?>
                
                <div class="row g-3">
                    <div class="col-12 col-md-6 mb-3">
                        <label for="name" class="form-label">Full Name</label>
                        <input type="text" class="form-control" id="name" name="name" placeholder="John Doe" required value="<?= esc(old('name', $user['name'])) ?>" style="background-color: var(--bg-color); border: 1px solid var(--border-color); color: var(--text-color); border-radius: 8px; padding: 10px;">
                        <?php if (isset($validation) && $validation->hasError('name')): ?>
                            <div class="text-danger small mt-1"><?= $validation->getError('name') ?></div>
                        <?php endif; ?>
                    </div>
                    
                    <div class="col-12 col-md-6 mb-3">
                        <label for="email" class="form-label">Email Address</label>
                        <input type="email" class="form-control" id="email" name="email" placeholder="name@domain.com" required value="<?= esc(old('email', $user['email'])) ?>" style="background-color: var(--bg-color); border: 1px solid var(--border-color); color: var(--text-color); border-radius: 8px; padding: 10px;">
                        <?php if (isset($validation) && $validation->hasError('email')): ?>
                            <div class="text-danger small mt-1"><?= $validation->getError('email') ?></div>
                        <?php endif; ?>
                    </div>
                </div>
                
                <hr class="my-4" style="border-color: var(--border-color) !important;">
                <h6 class="fw-bold mb-3"><i class="fa-solid fa-lock me-2 text-profit"></i>Change Security Password</h6>
                <p class="text-muted small mb-4">Leave fields blank if you do not want to alter your current password.</p>
                
                <div class="row g-3">
                    <div class="col-12 col-md-6 mb-3">
                        <label for="password" class="form-label">New Password (Min. 6 chars)</label>
                        <input type="password" class="form-control" id="password" name="password" placeholder="••••••••" style="background-color: var(--bg-color); border: 1px solid var(--border-color); color: var(--text-color); border-radius: 8px; padding: 10px;">
                        <?php if (isset($validation) && $validation->hasError('password')): ?>
                            <div class="text-danger small mt-1"><?= $validation->getError('password') ?></div>
                        <?php endif; ?>
                    </div>
                    
                    <div class="col-12 col-md-6 mb-4">
                        <label for="confirm_password" class="form-label">Confirm New Password</label>
                        <input type="password" class="form-control" id="confirm_password" name="confirm_password" placeholder="••••••••" style="background-color: var(--bg-color); border: 1px solid var(--border-color); color: var(--text-color); border-radius: 8px; padding: 10px;">
                        <?php if (isset($validation) && $validation->hasError('confirm_password')): ?>
                            <div class="text-danger small mt-1"><?= $validation->getError('confirm_password') ?></div>
                        <?php endif; ?>
                    </div>
                </div>
                
                <button type="submit" class="btn btn-primary px-4 py-2.5 font-weight-bold">
                    <i class="fa-solid fa-floppy-disk me-2"></i>Save Account Changes
                </button>
            </form>
        </div>
    </div>
</div>

<?= $this->endSection() ?>
