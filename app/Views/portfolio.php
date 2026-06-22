<?= $this->extend('layouts/main') ?>

<?= $this->section('title') ?>
Portfolio Simulator
<?= $this->endSection() ?>

<?= $this->section('content') ?>

<!-- PAGE HEADER -->
<div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-4 gap-3">
    <div>
        <h2 class="fw-bold mb-1">Portfolio Simulator</h2>
        <p class="text-muted mb-0">Simulate stock purchases, monitor live returns, and analyze your virtual investments.</p>
    </div>
    
    <!-- EXPORT REPORTS -->
    <div class="d-flex gap-2">
        <a href="<?= base_url('reports/portfolio/csv') ?>" class="btn btn-outline-success rounded-pill px-3 py-2 font-weight-bold" style="font-size: 13px;">
            <i class="fa-solid fa-file-csv me-1"></i> Export CSV
        </a>
        <a href="<?= base_url('reports/portfolio/excel') ?>" class="btn btn-outline-success rounded-pill px-3 py-2 font-weight-bold" style="font-size: 13px;">
            <i class="fa-solid fa-file-excel me-1"></i> Export Excel
        </a>
    </div>
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

<!-- 1. PORTFOLIO SUMMARY CARDS -->
<?php 
    $isPositive = $summary['total_profit_loss'] >= 0;
    $classColor = $isPositive ? 'text-profit' : 'text-loss';
    $bgSoftClass = $isPositive ? 'bg-profit-soft' : 'bg-loss-soft';
    $borderClass = $isPositive ? 'border-success' : 'border-danger';
    $caretIcon = $isPositive ? 'fa-caret-up' : 'fa-caret-down';
?>
<div class="row g-4 mb-4">
    <div class="col-12 col-md-3">
        <div class="card-custom">
            <span class="text-muted small d-block mb-1">INVESTED CAPITAL</span>
            <h3 class="fw-bold mb-0 text-color">₹<?= number_format($summary['total_investment'], 2) ?></h3>
        </div>
    </div>
    
    <div class="col-12 col-md-3">
        <div class="card-custom">
            <span class="text-muted small d-block mb-1">CURRENT VALUATION</span>
            <h3 class="fw-bold mb-0 text-color">₹<?= number_format($summary['total_current_value'], 2) ?></h3>
        </div>
    </div>
    
    <div class="col-12 col-md-3">
        <div class="card-custom">
            <span class="text-muted small d-block mb-1">NET PROFIT / LOSS</span>
            <h3 class="fw-bold mb-0 <?= $classColor ?>">
                <?= $isPositive ? '+' : '' ?>₹<?= number_format($summary['total_profit_loss'], 2) ?>
            </h3>
        </div>
    </div>
    
    <div class="col-12 col-md-3">
        <div class="card-custom" style="border-left: 4px solid <?= $isPositive ? '#10B981' : '#EF4444' ?>;">
            <span class="text-muted small d-block mb-1">TOTAL ROI</span>
            <h3 class="fw-bold mb-0 <?= $classColor ?>">
                <i class="fa-solid <?= $caretIcon ?> me-1 small"></i><?= number_format($summary['total_roi'], 2) ?>%
            </h3>
        </div>
    </div>
</div>

<!-- GRID SYSTEM FOR HOLDINGS & ADDING TRANSACTIONS -->
<div class="row g-4">
    <!-- LEFT PANEL: HOLDINGS TABLE -->
    <div class="col-12 col-lg-8">
        <div class="card-custom h-100">
            <h5 class="fw-bold mb-4"><i class="fa-solid fa-briefcase me-2 text-profit"></i>Active Virtual Holdings</h5>
            
            <?php if (empty($portfolio)): ?>
                <div class="text-center py-5">
                    <div class="mb-3 text-muted">
                        <i class="fa-solid fa-folder-open fs-1 text-emerald" style="color: #10B981;"></i>
                    </div>
                    <h5 class="fw-bold">No holdings found</h5>
                    <p class="text-muted small mx-auto" style="max-width: 400px;">Your virtual investment portfolio is empty. Use the "Simulate Purchase" form on the right to add stock transactions to your holdings.</p>
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-hover align-middle" style="color: var(--text-color);">
                        <thead>
                            <tr style="border-bottom: 2px solid var(--border-color); color: var(--text-muted); font-size: 13px;">
                                <th>STOCK</th>
                                <th>QTY</th>
                                <th class="text-end">BUY PRICE (₹)</th>
                                <th class="text-end">LIVE PRICE (₹)</th>
                                <th class="text-end">VALUATION (₹)</th>
                                <th class="text-end">P&L / ROI</th>
                                <th class="text-center">ACTION</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($portfolio as $hold): ?>
                                <?php 
                                    $isPos = $hold['profit_loss'] >= 0;
                                    $col = $isPos ? 'text-profit' : 'text-loss';
                                    $sign = $isPos ? '+' : '';
                                ?>
                                <tr style="border-bottom: 1px solid var(--border-color);">
                                    <td>
                                        <span class="fw-bold d-block"><?= esc(str_replace('.NS', '', $hold['symbol'])) ?></span>
                                        <span class="text-muted" style="font-size: 11px;"><?= esc($hold['purchase_date']) ?></span>
                                    </td>
                                    <td class="fw-semibold"><?= $hold['quantity'] ?></td>
                                    <td class="text-end fw-medium">₹<?= number_format($hold['purchase_price'], 2) ?></td>
                                    <td class="text-end fw-medium">₹<?= number_format($hold['current_price'], 2) ?></td>
                                    <td class="text-end fw-bold">₹<?= number_format($hold['current_value'], 2) ?></td>
                                    <td class="text-end font-weight-bold">
                                        <span class="<?= $col ?> d-block font-weight-bold"><?= $sign ?>₹<?= number_format($hold['profit_loss'], 2) ?></span>
                                        <span class="<?= $col ?> small" style="font-size: 11px; font-weight: 500;"><?= $sign ?><?= number_format($hold['roi'], 2) ?>%</span>
                                    </td>
                                    <td class="text-center">
                                        <a href="<?= base_url('portfolio/sell/' . $hold['id']) ?>" class="btn btn-sm btn-outline-danger rounded-pill px-3 py-1 font-weight-bold" style="font-size: 11px;" onclick="return confirm('Are you sure you want to sell/liquidate this virtual holding?');">
                                            <i class="fa-solid fa-money-bill-trend-up me-1"></i> Sell
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>
    
    <!-- RIGHT PANEL: SIMULATE PURCHASE FORM -->
    <div class="col-12 col-lg-4">
        <div class="card-custom">
            <h5 class="fw-bold mb-4"><i class="fa-solid fa-cart-plus me-2 text-profit"></i>Simulate Purchase</h5>
            
            <form action="<?= base_url('portfolio/buy') ?>" method="POST">
                <?= csrf_field() ?>
                
                <div class="mb-3">
                    <label for="stock_id" class="form-label">Select NSE Ticker</label>
                    <select class="form-select" id="stock_id" name="stock_id" required style="background-color: var(--bg-color); border: 1px solid var(--border-color); color: var(--text-color); border-radius: 8px; padding: 10px;">
                        <option value="" disabled selected>Choose a stock...</option>
                        <?php foreach ($stocks as $s): ?>
                            <option value="<?= $s['id'] ?>"><?= esc($s['company_name']) ?> (<?= esc(str_replace('.NS', '', $s['symbol'])) ?>)</option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="mb-3">
                    <label for="quantity" class="form-label">Purchase Quantity</label>
                    <input type="number" class="form-control" id="quantity" name="quantity" min="1" placeholder="e.g. 10" required style="background-color: var(--bg-color); border: 1px solid var(--border-color); color: var(--text-color); border-radius: 8px; padding: 10px;">
                </div>
                
                <div class="mb-3">
                    <label for="purchase_price" class="form-label">Purchase Price per Share (₹)</label>
                    <input type="number" step="0.01" min="0.01" class="form-control" id="purchase_price" name="purchase_price" placeholder="e.g. 1500.50" required style="background-color: var(--bg-color); border: 1px solid var(--border-color); color: var(--text-color); border-radius: 8px; padding: 10px;">
                </div>
                
                <div class="mb-4">
                    <label for="purchase_date" class="form-label">Purchase Date</label>
                    <input type="date" class="form-control" id="purchase_date" name="purchase_date" value="<?= date('Y-m-d') ?>" max="<?= date('Y-m-d') ?>" required style="background-color: var(--bg-color); border: 1px solid var(--border-color); color: var(--text-color); border-radius: 8px; padding: 10px;">
                </div>
                
                <button type="submit" class="btn btn-primary w-100 py-2.5 font-weight-bold">
                    <i class="fa-solid fa-plus me-2"></i>Add to Holdings
                </button>
            </form>
        </div>
    </div>
</div>

<?= $this->endSection() ?>
