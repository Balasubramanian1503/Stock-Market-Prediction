<?= $this->extend('layouts/main') ?>

<?= $this->section('title') ?>
Dashboard
<?= $this->endSection() ?>

<?= $this->section('content') ?>

<!-- PAGE HEADER -->
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h2 class="fw-bold mb-1">Market Overview</h2>
        <p class="text-muted mb-0">Track real-time prices, sector indices, and predictive machine learning models.</p>
    </div>
    <div class="text-end">
        <span class="text-muted d-block small">Last Sync</span>
        <span class="fw-semibold text-color"><i class="fa-solid fa-clock me-1"></i><?= date('h:i A') ?> (IST)</span>
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

<!-- 1. INDEXES BAR -->
<div class="row g-4 mb-4">
    <?php foreach ($indices as $key => $index): ?>
        <?php 
            $isPositive = $index['change_percent'] >= 0;
            $classColor = $isPositive ? 'text-profit' : 'text-loss';
            $bgSoftClass = $isPositive ? 'bg-profit-soft' : 'bg-loss-soft';
            $borderClass = $isPositive ? 'border-success' : 'border-danger';
            $caretIcon = $isPositive ? 'fa-caret-up' : 'fa-caret-down';
        ?>
        <div class="col-12 col-md-4">
            <div class="card-custom">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <span class="text-muted fw-medium small text-uppercase"><?= esc($index['company_name']) ?></span>
                    <span class="badge <?= $bgSoftClass ?> <?= $classColor ?> border <?= $borderClass ?> rounded-pill px-2.5 py-1">
                        <i class="fa-solid <?= $caretIcon ?> me-1"></i><?= number_format(abs($index['change_percent']), 2) ?>%
                    </span>
                </div>
                <h3 class="fw-bold mb-2">₹<?= number_format($index['price'], 2) ?></h3>
                <div class="d-flex justify-content-between text-muted small">
                    <span>Range: ₹<?= number_format($index['low'], 2) ?> - ₹<?= number_format($index['high'], 2) ?></span>
                    <span class="<?= $classColor ?> fw-medium">
                        <?= $isPositive ? '+' : '' ?><?= number_format($index['change'], 2) ?>
                    </span>
                </div>
            </div>
        </div>
    <?php endforeach; ?>
</div>

<!-- 2. ANALYTICS STAT CARDS -->
<div class="row g-4 mb-4">
    <div class="col-6 col-lg-3">
        <div class="card-custom d-flex align-items-center justify-content-between py-3.5">
            <div>
                <span class="text-muted small d-block">Supported Assets</span>
                <span class="fs-4 fw-bold">10 NSE Tickers</span>
            </div>
            <div class="bg-primary bg-opacity-10 text-primary p-3 rounded-3">
                <i class="fa-solid fa-cube fs-4 text-emerald" style="color:#10B981;"></i>
            </div>
        </div>
    </div>
    
    <div class="col-6 col-lg-3">
        <div class="card-custom d-flex align-items-center justify-content-between py-3.5">
            <div>
                <span class="text-muted small d-block">Historical Prices</span>
                <span class="fs-4 fw-bold">11,100+ Rows</span>
            </div>
            <div class="bg-primary bg-opacity-10 text-primary p-3 rounded-3">
                <i class="fa-solid fa-database fs-4 text-emerald" style="color:#10B981;"></i>
            </div>
        </div>
    </div>
    
    <div class="col-6 col-lg-3">
        <div class="card-custom d-flex align-items-center justify-content-between py-3.5">
            <div>
                <span class="text-muted small d-block">ML Models Active</span>
                <span class="fs-4 fw-bold">2 Models (LR/LSTM)</span>
            </div>
            <div class="bg-primary bg-opacity-10 text-primary p-3 rounded-3">
                <i class="fa-solid fa-brain fs-4 text-emerald" style="color:#10B981;"></i>
            </div>
        </div>
    </div>
    
    <div class="col-6 col-lg-3">
        <div class="card-custom d-flex align-items-center justify-content-between py-3.5">
            <div>
                <span class="text-muted small d-block">Trading Mode</span>
                <span class="fs-4 fw-bold">Virtual Simulator</span>
            </div>
            <div class="bg-primary bg-opacity-10 text-primary p-3 rounded-3">
                <i class="fa-solid fa-laptop-code fs-4 text-emerald" style="color:#10B981;"></i>
            </div>
        </div>
    </div>
</div>

<!-- 3. MAIN DASHBOARD CONTENT -->
<div class="row g-4">
    <!-- LEFT PANEL: STOCK TABLE & NEWS -->
    <div class="col-12 col-lg-8">
        
        <!-- MONITORED STOCKS LIST -->
        <div class="card-custom mb-4">
            <h5 class="fw-bold mb-4"><i class="fa-solid fa-chart-line me-2 text-profit"></i>Monitored NSE Stocks</h5>
            <div class="table-responsive">
                <table class="table table-hover align-middle" style="color: var(--text-color);">
                    <thead>
                        <tr style="border-bottom: 2px solid var(--border-color); color: var(--text-muted); font-size: 13px;">
                            <th>SYMBOL</th>
                            <th>COMPANY</th>
                            <th>SECTOR</th>
                            <th class="text-end">PRICE (₹)</th>
                            <th class="text-end">CHANGE (%)</th>
                            <th class="text-center">ACTION</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($stocks as $stock): ?>
                            <?php 
                                $isPositive = $stock['change_percent'] >= 0;
                                $classColor = $isPositive ? 'text-profit' : 'text-loss';
                                $caretIcon = $isPositive ? 'fa-arrow-trend-up' : 'fa-arrow-trend-down';
                            ?>
                            <tr style="border-bottom: 1px solid var(--border-color);">
                                <td class="fw-bold"><?= esc(str_replace('.NS', '', $stock['symbol'])) ?></td>
                                <td>
                                    <span class="fw-semibold d-block"><?= esc($stock['company_name']) ?></span>
                                </td>
                                <td>
                                    <span class="badge bg-secondary bg-opacity-10 text-muted px-2 py-1 rounded small">
                                        <?= esc($stock['sector']) ?>
                                    </span>
                                </td>
                                <td class="text-end fw-semibold">₹<?= number_format($stock['price'], 2) ?></td>
                                <td class="text-end <?= $classColor ?> fw-medium">
                                    <i class="fa-solid <?= $caretIcon ?> me-1 small"></i><?= $isPositive ? '+' : '' ?><?= number_format($stock['change_percent'], 2) ?>%
                                </td>
                                <td class="text-center">
                                    <a href="<?= base_url('tracker/' . $stock['symbol']) ?>" class="btn btn-sm btn-outline-success rounded-pill px-3 py-1 font-weight-bold" style="font-size: 12px;">
                                        <i class="fa-solid fa-chart-line me-1"></i> Analyze
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
        
        <!-- FINANCIAL NEWS MODULE -->
        <div class="card-custom">
            <h5 class="fw-bold mb-4"><i class="fa-solid fa-newspaper me-2 text-profit"></i>Latest Indian Financial News</h5>
            <div class="news-list">
                <?php foreach ($news as $n): ?>
                    <div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between p-3 rounded-3 mb-3" style="background: rgba(255,255,255,0.02); border: 1px solid var(--border-color);">
                        <div class="mb-2 mb-md-0" style="max-width: 80%;">
                            <div class="d-flex align-items-center gap-2 mb-2">
                                <span class="badge bg-success bg-opacity-10 text-profit px-2 py-1 rounded small">
                                    <?= esc($n['category']) ?>
                                </span>
                                <span class="text-muted small"><?= date('F d, Y', strtotime($n['published_at'])) ?></span>
                            </div>
                            <h6 class="fw-bold mb-1"><?= esc($n['title']) ?></h6>
                            <p class="text-muted small mb-0"><?= esc($n['summary']) ?></p>
                        </div>
                        <div class="text-md-end text-muted small mt-2 mt-md-0">
                            <span class="d-block"><i class="fa-solid fa-circle-nodes me-1"></i><?= esc($n['source']) ?></span>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>

    </div>

    <!-- RIGHT PANEL: GAINERS, LOSERS, SECTORS -->
    <div class="col-12 col-lg-4">
        
        <!-- TOP GAINERS CARD -->
        <div class="card-custom mb-4" style="border-top: 4px solid #10B981;">
            <h6 class="fw-bold mb-3 text-profit"><i class="fa-solid fa-arrow-trend-up me-2"></i>Top Gainers (NSE)</h6>
            <div class="list-group list-group-flush bg-transparent">
                <?php if (empty($gainers)): ?>
                    <div class="text-muted small py-3">No positive gains tracked today.</div>
                <?php else: ?>
                    <?php foreach ($gainers as $g): ?>
                        <div class="d-flex align-items-center justify-content-between py-2 border-0 bg-transparent">
                            <div>
                                <span class="fw-bold d-block text-color"><?= esc(str_replace('.NS', '', $g['symbol'])) ?></span>
                                <span class="text-muted small" style="font-size: 11px;"><?= esc($g['company_name']) ?></span>
                            </div>
                            <div class="text-end">
                                <span class="fw-semibold text-color d-block">₹<?= number_format($g['price'], 2) ?></span>
                                <span class="text-profit small fw-medium"><i class="fa-solid fa-plus me-1"></i><?= number_format($g['change_percent'], 2) ?>%</span>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
        
        <!-- TOP LOSERS CARD -->
        <div class="card-custom mb-4" style="border-top: 4px solid #EF4444;">
            <h6 class="fw-bold mb-3 text-loss"><i class="fa-solid fa-arrow-trend-down me-2"></i>Top Losers (NSE)</h6>
            <div class="list-group list-group-flush bg-transparent">
                <?php if (empty($losers)): ?>
                    <div class="text-muted small py-3">No negative drops tracked today.</div>
                <?php else: ?>
                    <?php foreach ($losers as $l): ?>
                        <div class="d-flex align-items-center justify-content-between py-2 border-0 bg-transparent">
                            <div>
                                <span class="fw-bold d-block text-color"><?= esc(str_replace('.NS', '', $l['symbol'])) ?></span>
                                <span class="text-muted small" style="font-size: 11px;"><?= esc($l['company_name']) ?></span>
                            </div>
                            <div class="text-end">
                                <span class="fw-semibold text-color d-block">₹<?= number_format($l['price'], 2) ?></span>
                                <span class="text-loss small fw-medium"><i class="fa-solid fa-minus me-1"></i><?= number_format(abs($l['change_percent']), 2) ?>%</span>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>

        <!-- SECTOR PERFORMANCE CARD -->
        <div class="card-custom">
            <h6 class="fw-bold mb-4"><i class="fa-solid fa-chart-pie me-2 text-profit"></i>Sector Performance Average</h6>
            <div class="sector-list">
                <?php foreach ($sectors as $s): ?>
                    <?php 
                        $isPositive = $s['avg_change'] >= 0;
                        $classColor = $isPositive ? 'text-profit' : 'text-loss';
                        $caretIcon = $isPositive ? 'fa-circle-chevron-up' : 'fa-circle-chevron-down';
                        $percentFormatted = number_format($s['avg_change'], 2);
                    ?>
                    <div class="d-flex align-items-center justify-content-between py-2.5 border-bottom" style="border-color: var(--border-color) !important;">
                        <div>
                            <span class="fw-semibold text-color small"><?= esc($s['name']) ?></span>
                            <span class="text-muted d-block" style="font-size: 11px;"><?= $s['count'] ?> Stocks Ingested</span>
                        </div>
                        <div class="text-end">
                            <span class="<?= $classColor ?> fw-semibold small">
                                <i class="fa-solid <?= $caretIcon ?> me-1"></i><?= $isPositive ? '+' : '' ?><?= $percentFormatted ?>%
                            </span>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>

    </div>
</div>

<?= $this->endSection() ?>
