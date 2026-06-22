<?= $this->extend('layouts/main') ?>

<?= $this->section('title') ?>
Admin Panel
<?= $this->endSection() ?>

<?= $this->section('content') ?>

<!-- PAGE HEADER -->
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h2 class="fw-bold mb-1">Admin Control Panel</h2>
        <p class="text-muted mb-0">Manage system assets, publish sector news, view system logs, and trigger machine learning training loops.</p>
    </div>
    
    <!-- RETRAIN ALL -->
    <a href="<?= base_url('admin/retrain/all') ?>" class="btn btn-warning rounded-pill px-4 py-2 font-weight-bold shadow-sm" onclick="return confirm('Warning: Retraining ALL stock models will execute multiple neural network epoch passes. This might take 10-15 seconds. Proceed?');">
        <i class="fa-solid fa-arrows-spin me-1"></i> Retrain All Models
    </a>
</div>

<!-- QUICK STATS BAR -->
<div class="row g-4 mb-4">
    <div class="col-12 col-md-4">
        <div class="card-custom py-3.5">
            <span class="text-muted small d-block mb-1">REGISTERED USERS</span>
            <h3 class="fw-bold mb-0 text-color"><i class="fa-solid fa-users me-2 text-profit"></i><?= $total_users ?> Users</h3>
        </div>
    </div>
    
    <div class="col-12 col-md-4">
        <div class="card-custom py-3.5">
            <span class="text-muted small d-block mb-1">MONITORED STOCKS</span>
            <h3 class="fw-bold mb-0 text-color"><i class="fa-solid fa-circle-nodes me-2 text-profit"></i><?= $total_stocks ?> Tickers</h3>
        </div>
    </div>
    
    <div class="col-12 col-md-4">
        <div class="card-custom py-3.5">
            <span class="text-muted small d-block mb-1">ACTIVE PREDICTIONS</span>
            <h3 class="fw-bold mb-0 text-color"><i class="fa-solid fa-brain me-2 text-profit"></i><?= $total_predictions ?> Records</h3>
        </div>
    </div>
</div>

<!-- TERMINAL STYLE EXECUTION CONSOLE -->
<?php if (session()->getFlashdata('cmd_output')): ?>
    <div class="card-custom mb-4">
        <h5 class="fw-bold mb-3"><i class="fa-solid fa-terminal me-2 text-warning"></i>Python Training Console Output</h5>
        <pre class="bg-dark text-success p-3 rounded font-monospace small mb-0" style="max-height: 250px; overflow-y: auto; border: 1px solid rgba(255,255,255,0.05);"><?= esc(session()->getFlashdata('cmd_output')) ?></pre>
    </div>
<?php endif; ?>

<!-- GRID SYSTEM -->
<div class="row g-4">
    <!-- LEFT PANEL: RETRAIN & AUDIT LOGS -->
    <div class="col-12 col-lg-8">
        
        <!-- RETRAIN INDIVIDUAL MODELS -->
        <div class="card-custom mb-4">
            <h5 class="fw-bold mb-4"><i class="fa-solid fa-gears me-2 text-profit"></i>Ingest & Retrain Individual Stocks</h5>
            <div class="table-responsive">
                <table class="table table-hover align-middle" style="color: var(--text-color);">
                    <thead>
                        <tr style="border-bottom: 2px solid var(--border-color); color: var(--text-muted); font-size: 13px;">
                            <th>SYMBOL</th>
                            <th>COMPANY</th>
                            <th>SECTOR</th>
                            <th class="text-center">TRAINING TRIGGER</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($stocks as $s): ?>
                            <tr style="border-bottom: 1px solid var(--border-color);">
                                <td class="fw-bold"><?= esc(str_replace('.NS', '', $s['symbol'])) ?></td>
                                <td>
                                    <span class="fw-semibold d-block"><?= esc($s['company_name']) ?></span>
                                </td>
                                <td>
                                    <span class="badge bg-secondary bg-opacity-10 text-muted px-2 py-1 rounded small">
                                        <?= esc($s['sector']) ?>
                                    </span>
                                </td>
                                <td class="text-center">
                                    <a href="<?= base_url('admin/retrain/' . $s['symbol']) ?>" class="btn btn-sm btn-success rounded-pill px-3 py-1 font-weight-bold shadow-sm" style="font-size: 12px;" onclick="return confirm('Retrain models for <?= $s['symbol'] ?>?');">
                                        <i class="fa-solid fa-play me-1"></i> Retrain
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- SYSTEM AUDIT LOGS FEED -->
        <div class="card-custom">
            <h5 class="fw-bold mb-4"><i class="fa-solid fa-clipboard-list me-2 text-profit"></i>System Security & Ingestion Audit Logs</h5>
            <div class="table-responsive" style="max-height: 400px; overflow-y: auto;">
                <table class="table table-hover align-middle" style="color: var(--text-color); font-size: 13px;">
                    <thead>
                        <tr style="border-bottom: 2px solid var(--border-color); color: var(--text-muted);">
                            <th>TIMESTAMP</th>
                            <th>ACTION</th>
                            <th>USER</th>
                            <th>DETAILS</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($logs as $l): ?>
                            <tr style="border-bottom: 1px solid var(--border-color);">
                                <td style="white-space: nowrap;"><?= date('M d, H:i:s', strtotime($l['created_at'])) ?></td>
                                <td>
                                    <?php 
                                        $badge = 'bg-secondary';
                                        if ($l['action'] === 'LOGIN' || $l['action'] === 'REGISTRATION') $badge = 'bg-success';
                                        if ($l['action'] === 'MODEL_TRAINING' || $l['action'] === 'ADMIN_RETRAIN') $badge = 'bg-warning text-dark';
                                        if ($l['action'] === 'PORTFOLIO_BUY' || $l['action'] === 'PORTFOLIO_SELL') $badge = 'bg-info text-dark';
                                    ?>
                                    <span class="badge <?= $badge ?> px-2 py-1 rounded">
                                        <?= esc($l['action']) ?>
                                    </span>
                                </td>
                                <td class="fw-semibold"><?= $l['user_name'] ? esc($l['user_name']) : '<span class="text-muted">System</span>' ?></td>
                                <td class="text-muted"><?= esc($l['details']) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>

    </div>

    <!-- RIGHT PANEL: PUBLISH NEWS & ADD STOCK -->
    <div class="col-12 col-lg-4">
        
        <!-- PUBLISH NEWS FORM -->
        <div class="card-custom mb-4">
            <h5 class="fw-bold mb-4"><i class="fa-solid fa-bullhorn me-2 text-profit"></i>Publish Sector News</h5>
            
            <form action="<?= base_url('admin/news/save') ?>" method="POST">
                <?= csrf_field() ?>
                
                <div class="mb-3">
                    <label for="title" class="form-label">News Headline</label>
                    <input type="text" class="form-control" id="title" name="title" placeholder="e.g. IT Sector witness strong rally..." required style="background-color: var(--bg-color); border: 1px solid var(--border-color); color: var(--text-color); border-radius: 8px; padding: 10px;">
                </div>
                
                <div class="mb-3">
                    <label for="category" class="form-label">Sector Category</label>
                    <select class="form-select" id="category" name="category" required style="background-color: var(--bg-color); border: 1px solid var(--border-color); color: var(--text-color); border-radius: 8px; padding: 10px;">
                        <option value="NIFTY">NIFTY</option>
                        <option value="Banking">Banking</option>
                        <option value="IT Sector">IT Sector</option>
                        <option value="Energy Sector">Energy Sector</option>
                        <option value="Auto Sector">Auto Sector</option>
                    </select>
                </div>
                
                <div class="mb-3">
                    <label for="summary" class="form-label">Summary Brief</label>
                    <textarea class="form-control" id="summary" name="summary" rows="2" placeholder="Brief summary of the article..." required style="background-color: var(--bg-color); border: 1px solid var(--border-color); color: var(--text-color); border-radius: 8px; padding: 10px;"></textarea>
                </div>
                
                <div class="mb-4">
                    <label for="content" class="form-label">Detailed Content Body</label>
                    <textarea class="form-control" id="content" name="content" rows="4" placeholder="Full body of the news article..." required style="background-color: var(--bg-color); border: 1px solid var(--border-color); color: var(--text-color); border-radius: 8px; padding: 10px;"></textarea>
                </div>
                
                <button type="submit" class="btn btn-primary w-100 py-2.5 font-weight-bold">
                    <i class="fa-solid fa-paper-plane me-2"></i>Publish Article
                </button>
            </form>
        </div>

        <!-- ADD STOCK FORM -->
        <div class="card-custom">
            <h5 class="fw-bold mb-4"><i class="fa-solid fa-circle-plus me-2 text-profit"></i>Add Supported NSE Asset</h5>
            
            <form action="<?= base_url('admin/stock/save') ?>" method="POST">
                <?= csrf_field() ?>
                
                <div class="mb-3">
                    <label for="symbol" class="form-label">Stock Symbol (Yahoo Format)</label>
                    <input type="text" class="form-control" id="symbol" name="symbol" placeholder="e.g. RELIANCE.NS" required style="background-color: var(--bg-color); border: 1px solid var(--border-color); color: var(--text-color); border-radius: 8px; padding: 10px;">
                </div>
                
                <div class="mb-3">
                    <label for="company_name" class="form-label">Company Name</label>
                    <input type="text" class="form-control" id="company_name" name="company_name" placeholder="e.g. Reliance Industries Limited" required style="background-color: var(--bg-color); border: 1px solid var(--border-color); color: var(--text-color); border-radius: 8px; padding: 10px;">
                </div>
                
                <div class="mb-4">
                    <label for="sector" class="form-label">Sector Category</label>
                    <input type="text" class="form-control" id="sector" name="sector" placeholder="e.g. Energy Sector" required style="background-color: var(--bg-color); border: 1px solid var(--border-color); color: var(--text-color); border-radius: 8px; padding: 10px;">
                </div>
                
                <button type="submit" class="btn btn-primary w-100 py-2.5 font-weight-bold">
                    <i class="fa-solid fa-plus me-2"></i>Add Supported Stock
                </button>
            </form>
        </div>

    </div>
</div>

<?= $this->endSection() ?>
