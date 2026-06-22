<?= $this->extend('layouts/main') ?>

<?= $this->section('title') ?>
<?= esc($stock['company_name']) ?> (<?= esc(str_replace('.NS', '', $stock['symbol'])) ?>)
<?= $this->endSection() ?>

<?= $this->section('styles') ?>
<style>
    .metric-title {
        font-size: 13px;
        color: var(--text-muted);
        font-weight: 500;
        text-transform: uppercase;
        margin-bottom: 4px;
    }
    .metric-value {
        font-size: 18px;
        font-weight: 700;
        color: var(--text-color);
    }
    .chart-filter-btn {
        background: none;
        border: 1px solid var(--border-color);
        color: var(--text-muted);
        font-size: 13px;
        padding: 5px 12px;
        border-radius: 20px;
        font-weight: 500;
        transition: all 0.2s ease;
    }
    .chart-filter-btn.active, .chart-filter-btn:hover {
        background-color: var(--active-bg);
        color: #10B981;
        border-color: #10B981;
    }
    .prediction-card {
        background: rgba(255, 255, 255, 0.02);
        border: 1px solid var(--border-color);
        border-radius: 12px;
        padding: 20px;
        text-align: center;
        transition: all 0.2s ease;
    }
    .prediction-card:hover {
        background: rgba(16, 185, 129, 0.04);
        border-color: rgba(16, 185, 129, 0.3);
    }
    .nav-pills-custom .nav-link {
        background-color: transparent;
        color: var(--text-muted);
        border: 1px solid var(--border-color);
        font-size: 14px;
        font-weight: 600;
        padding: 8px 16px;
        border-radius: 8px;
        margin-right: 8px;
        transition: all 0.2s ease;
    }
    .nav-pills-custom .nav-link.active, .nav-pills-custom .nav-link:hover {
        background-color: var(--active-bg) !important;
        color: #10B981 !important;
        border-color: #10B981;
    }
    .refresh-badge {
        font-size: 11px;
        font-weight: 600;
        color: var(--text-muted);
    }
    @keyframes flash-green {
        0% { background-color: rgba(16, 185, 129, 0.3); }
        100% { background-color: transparent; }
    }
    @keyframes flash-red {
        0% { background-color: rgba(239, 68, 68, 0.3); }
        100% { background-color: transparent; }
    }
    .flash-up {
        animation: flash-green 1.5s ease-out;
    }
    .flash-down {
        animation: flash-red 1.5s ease-out;
    }

    /* Automated Investment Advisor Card styling */
    .advisor-verdict-badge {
        font-size: 20px;
        font-weight: 800;
        padding: 6px 20px;
        border-radius: 8px;
        letter-spacing: 0.03em;
        text-transform: uppercase;
        display: inline-block;
    }
    .advisor-verdict-badge.badge-invest {
        color: #10B981;
        background-color: rgba(16, 185, 129, 0.12);
        border: 1.5px solid #10B981;
    }
    .advisor-verdict-badge.badge-avoid {
        color: #EF4444;
        background-color: rgba(239, 68, 68, 0.12);
        border: 1.5px solid #EF4444;
    }
    .advisor-confidence-bar-outer {
        height: 8px;
        background-color: rgba(255, 255, 255, 0.05);
        border-radius: 4px;
        overflow: hidden;
        border: 1px solid var(--border-color);
        width: 100%;
        margin-top: 8px;
    }
    .advisor-confidence-bar-inner {
        height: 100%;
        border-radius: 4px;
    }
    .advisor-confidence-bar-inner.bar-invest {
        background-color: #10B981;
    }
    .advisor-confidence-bar-inner.bar-avoid {
        background-color: #EF4444;
    }
    .checkpoint-row {
        background-color: rgba(255, 255, 255, 0.01);
        border: 1px solid var(--border-color);
        border-radius: 8px;
        padding: 10px 14px;
        margin-bottom: 8px;
    }
    .checkpoint-badge {
        font-size: 10px;
        font-weight: 700;
        padding: 2px 8px;
        border-radius: 10px;
        text-transform: uppercase;
        display: inline-block;
    }
    .checkpoint-badge.badge-bullish {
        color: #10B981;
        background-color: rgba(16, 185, 129, 0.1);
        border: 1px solid #10B981;
    }
    .checkpoint-badge.badge-bearish {
        color: #EF4444;
        background-color: rgba(239, 68, 68, 0.1);
        border: 1px solid #EF4444;
    }
    .checkpoint-badge.badge-neutral {
        color: var(--text-muted);
        background-color: rgba(255, 255, 255, 0.03);
        border: 1px solid var(--border-color);
    }
    
    /* Compliance Modal overlay styling */
    .advisor-modal-overlay {
        position: fixed;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: rgba(11, 19, 43, 0.85);
        backdrop-filter: blur(8px);
        z-index: 10000;
        display: none;
        align-items: center;
        justify-content: center;
        padding: 1rem;
    }
    .advisor-modal-content {
        background-color: var(--card-bg);
        border: 1px solid var(--border-color);
        border-radius: 16px;
        max-width: 550px;
        width: 100%;
        padding: 24px;
        box-shadow: 0 10px 30px rgba(0,0,0,0.5);
        display: flex;
        flex-direction: column;
        gap: 16px;
        text-align: left;
    }
    .advisor-close-btn {
        background: rgba(255, 255, 255, 0.05);
        color: var(--text-color);
        border: 1px solid var(--border-color);
        border-radius: 8px;
        padding: 8px 16px;
        cursor: pointer;
        font-weight: 600;
        align-self: flex-end;
        transition: background 0.2s;
    }
    .advisor-close-btn:hover {
        background: rgba(255, 255, 255, 0.1);
    }
    
    /* Focus outline style */
    button:focus-visible, 
    a:focus-visible, 
    input:focus-visible,
    div[tabindex="0"]:focus-visible {
        outline: 3px solid #10B981 !important;
        outline-offset: 2px !important;
    }
</style>
<?= $this->endSection() ?>

<?= $this->section('content') ?>

<!-- DYNAMIC BACK-ARROW & TITLE -->
<div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-4 gap-3">
    <div>
        <a href="<?= base_url('dashboard') ?>" class="text-decoration-none text-muted small fw-semibold mb-2 d-inline-block">
            <i class="fa-solid fa-arrow-left me-1"></i> Back to Dashboard
        </a>
        <h2 class="fw-bold mb-1" id="stockCompanyName"><?= esc($stock['company_name']) ?></h2>
        <span class="badge bg-secondary bg-opacity-10 text-muted px-2 py-1 rounded small">
            NSE: <?= esc(str_replace('.NS', '', $stock['symbol'])) ?>
        </span>
        <span class="badge bg-secondary bg-opacity-10 text-muted px-2 py-1 rounded small">
            <?= esc($stock['sector']) ?>
        </span>
    </div>
    
    <!-- LIVE STATS & WATCHLIST TOGGLE -->
    <div class="d-flex align-items-center gap-3">
        <div class="text-end">
            <h3 class="fw-bold mb-1" id="liveStockPrice">₹<?= number_format($quote['price'], 2) ?></h3>
            <span class="fw-semibold small" id="liveStockChange">
                <i class="fa-solid <?= $quote['change_percent'] >= 0 ? 'fa-arrow-trend-up' : 'fa-arrow-trend-down' ?> me-1"></i>
                <?= $quote['change_percent'] >= 0 ? '+' : '' ?><?= number_format($quote['change_percent'], 2) ?>%
            </span>
        </div>
        
        <button class="btn btn-outline-success rounded-pill px-4 py-2 font-weight-bold" id="watchlistToggleBtn">
            <i class="<?= $in_watchlist ? 'fa-solid fa-star' : 'fa-regular fa-star' ?> me-1"></i>
            <span id="watchlistText"><?= $in_watchlist ? 'Watchlisted' : 'Add Watchlist' ?></span>
        </button>
    </div>
</div>

<!-- AUTO-REFRESH STATUS BAR -->
<div class="card-custom mb-4 py-2.5 px-4 d-flex flex-row align-items-center justify-content-between">
    <div class="d-flex align-items-center">
        <span class="spinner-grow spinner-grow-sm text-success me-2" role="status"></span>
        <span class="refresh-badge" id="refreshTimerText">Auto-refresh active. Next update in 30 seconds...</span>
    </div>
    <div>
        <span class="badge bg-profit-soft text-profit border border-success font-weight-bold px-2 py-1 rounded-pill" style="font-size: 11px;">
            <i class="fa-solid fa-bolt me-1"></i>30s Live Ingestion
        </span>
    </div>
</div>

<!-- GRID SYSTEM -->
<div class="row g-4 mb-4">
    <!-- LEFT COLUMN: TECHNICAL INDICATORS & CHARTS -->
    <div class="col-12 col-lg-8">
        
        <!-- HISTORICAL CHARTS CARD -->
        <div class="card-custom mb-4">
            <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-4 gap-3">
                <h5 class="fw-bold mb-0">Interactive Price Visualization</h5>
                <div class="d-flex gap-1 flex-wrap">
                    <button class="chart-filter-btn" data-days="30">1M</button>
                    <button class="chart-filter-btn" data-days="90">3M</button>
                    <button class="chart-filter-btn" data-days="180">6M</button>
                    <button class="chart-filter-btn active" data-days="365">1Y</button>
                    <button class="chart-filter-btn" data-days="1825">5Y</button>
                </div>
            </div>
            
            <!-- Tab selects -->
            <ul class="nav nav-pills nav-pills-custom mb-4" id="chartTab" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="nav-link active" id="price-tab" data-bs-toggle="pill" data-bs-target="#price-pane" type="button" role="tab"><i class="fa-solid fa-chart-line me-1"></i> Close Price</button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="range-tab" data-bs-toggle="pill" data-bs-target="#range-pane" type="button" role="tab"><i class="fa-solid fa-arrows-left-right-to-line me-1"></i> High-Low Range</button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="volume-tab" data-bs-toggle="pill" data-bs-target="#volume-pane" type="button" role="tab"><i class="fa-solid fa-chart-simple me-1"></i> Volume</button>
                </li>
            </ul>
            
            <div class="tab-content" id="chartTabContent">
                <!-- PRICE CHART -->
                <div class="tab-pane fade show active" id="price-pane" role="tabpanel">
                    <div style="height: 350px; position: relative;">
                        <canvas id="historicalPriceChart"></canvas>
                    </div>
                </div>
                <!-- HIGH-LOW AREA CHART -->
                <div class="tab-pane fade" id="range-pane" role="tabpanel">
                    <div style="height: 350px; position: relative;">
                        <canvas id="historicalRangeChart"></canvas>
                    </div>
                </div>
                <!-- VOLUME CHART -->
                <div class="tab-pane fade" id="volume-pane" role="tabpanel">
                    <div style="height: 350px; position: relative;">
                        <canvas id="historicalVolumeChart"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <!-- 4. MACHINE LEARNING PREDICTIVE MODULE -->
        <div class="card-custom">
            <h5 class="fw-bold mb-4"><i class="fa-solid fa-brain me-2 text-profit"></i>Machine Learning Price Forecasting</h5>
            
            <ul class="nav nav-pills nav-pills-custom mb-4" id="mlModelTab" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="nav-link active" id="lstm-tab" data-bs-toggle="pill" data-bs-target="#lstm-pane" type="button" role="tab"><i class="fa-solid fa-square-poll-vertical me-1"></i> LSTM Neural Network</button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="lr-tab" data-bs-toggle="pill" data-bs-target="#lr-pane" type="button" role="tab"><i class="fa-solid fa-chart-column me-1"></i> Linear Regression</button>
                </li>
            </ul>
            
            <div class="tab-content" id="mlModelTabContent">
                <!-- LSTM FORECASTS -->
                <div class="tab-pane fade show active" id="lstm-pane" role="tabpanel">
                    <?php if (empty($lstm_preds)): ?>
                        <div class="text-muted py-4 text-center">LSTM model predictions are not compiled yet. Retrain in Admin Panel.</div>
                    <?php else: ?>
                        <!-- Forecasting Cards -->
                        <div class="row g-3 mb-4">
                            <div class="col-12 col-md-4">
                                <div class="prediction-card">
                                    <div class="metric-title">Predicted Tomorrow</div>
                                    <h4 class="fw-bold mb-1 text-profit">₹<?= number_format($lstm_preds[0]['predicted_price'], 2) ?></h4>
                                    <span class="text-muted small"><?= date('F d, Y', strtotime($lstm_preds[0]['target_date'])) ?></span>
                                </div>
                            </div>
                            <div class="col-12 col-md-4">
                                <div class="prediction-card">
                                    <div class="metric-title">Predicted Next Week</div>
                                    <h4 class="fw-bold mb-1 text-profit">₹<?= number_format($lstm_preds[1]['predicted_price'], 2) ?></h4>
                                    <span class="text-muted small"><?= date('F d, Y', strtotime($lstm_preds[1]['target_date'])) ?></span>
                                </div>
                            </div>
                            <div class="col-12 col-md-4">
                                <div class="prediction-card">
                                    <div class="metric-title">Predicted Next Month</div>
                                    <h4 class="fw-bold mb-1 text-profit">₹<?= number_format($lstm_preds[2]['predicted_price'], 2) ?></h4>
                                    <span class="text-muted small"><?= date('F d, Y', strtotime($lstm_preds[2]['target_date'])) ?></span>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Model accuracy indicators -->
                        <div class="row g-4">
                            <div class="col-12 col-md-6">
                                <div class="p-3 rounded-3" style="background: rgba(255,255,255,0.015); border: 1px solid var(--border-color);">
                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                        <span class="fw-semibold small">Prediction Confidence Score</span>
                                        <span class="fw-bold text-profit"><?= number_format($lstm_preds[0]['confidence_score'], 2) ?>%</span>
                                    </div>
                                    <div class="progress" style="height: 10px; background-color: var(--bg-color);">
                                        <div class="progress-bar bg-success rounded" role="progressbar" style="width: <?= $lstm_preds[0]['confidence_score'] ?>%;"></div>
                                    </div>
                                    <span class="text-muted small d-block mt-2" style="font-size: 11px;">Confidence derived mathematically from validation-set Mean Absolute Percentage Error (MAPE).</span>
                                </div>
                            </div>
                            <div class="col-12 col-md-6">
                                <div class="row g-3">
                                    <div class="col-4 text-center">
                                        <div class="text-muted small" style="font-size: 11px;">MAE</div>
                                        <span class="fw-bold small"><?= number_format($lstm_preds[0]['mae'], 2) ?></span>
                                    </div>
                                    <div class="col-4 text-center">
                                        <div class="text-muted small" style="font-size: 11px;">RMSE</div>
                                        <span class="fw-bold small"><?= number_format($lstm_preds[0]['rmse'], 2) ?></span>
                                    </div>
                                    <div class="col-4 text-center">
                                        <div class="text-muted small" style="font-size: 11px;">R² SCORE</div>
                                        <span class="fw-bold small text-profit"><?= number_format($lstm_preds[0]['r2_score'], 4) ?></span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
                
                <!-- LINEAR REGRESSION FORECASTS -->
                <div class="tab-pane fade" id="lr-pane" role="tabpanel">
                    <?php if (empty($lr_preds)): ?>
                        <div class="text-muted py-4 text-center">Linear Regression predictions are not compiled yet. Retrain in Admin Panel.</div>
                    <?php else: ?>
                        <!-- Forecasting Cards -->
                        <div class="row g-3 mb-4">
                            <div class="col-12 col-md-4">
                                <div class="prediction-card">
                                    <div class="metric-title">Predicted Tomorrow</div>
                                    <h4 class="fw-bold mb-1 text-profit">₹<?= number_format($lr_preds[0]['predicted_price'], 2) ?></h4>
                                    <span class="text-muted small"><?= date('F d, Y', strtotime($lr_preds[0]['target_date'])) ?></span>
                                </div>
                            </div>
                            <div class="col-12 col-md-4">
                                <div class="prediction-card">
                                    <div class="metric-title">Predicted Next Week</div>
                                    <h4 class="fw-bold mb-1 text-profit">₹<?= number_format($lr_preds[1]['predicted_price'], 2) ?></h4>
                                    <span class="text-muted small"><?= date('F d, Y', strtotime($lr_preds[1]['target_date'])) ?></span>
                                </div>
                            </div>
                            <div class="col-12 col-md-4">
                                <div class="prediction-card">
                                    <div class="metric-title">Predicted Next Month</div>
                                    <h4 class="fw-bold mb-1 text-profit">₹<?= number_format($lr_preds[2]['predicted_price'], 2) ?></h4>
                                    <span class="text-muted small"><?= date('F d, Y', strtotime($lr_preds[2]['target_date'])) ?></span>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Model accuracy indicators -->
                        <div class="row g-4">
                            <div class="col-12 col-md-6">
                                <div class="p-3 rounded-3" style="background: rgba(255,255,255,0.015); border: 1px solid var(--border-color);">
                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                        <span class="fw-semibold small">Prediction Confidence Score</span>
                                        <span class="fw-bold text-profit"><?= number_format($lr_preds[0]['confidence_score'], 2) ?>%</span>
                                    </div>
                                    <div class="progress" style="height: 10px; background-color: var(--bg-color);">
                                        <div class="progress-bar bg-success rounded" role="progressbar" style="width: <?= $lr_preds[0]['confidence_score'] ?>%;"></div>
                                    </div>
                                    <span class="text-muted small d-block mt-2" style="font-size: 11px;">Confidence derived mathematically from validation-set Mean Absolute Percentage Error (MAPE).</span>
                                </div>
                            </div>
                            <div class="col-12 col-md-6">
                                <div class="row g-3">
                                    <div class="col-4 text-center">
                                        <div class="text-muted small" style="font-size: 11px;">MAE</div>
                                        <span class="fw-bold small"><?= number_format($lr_preds[0]['mae'], 2) ?></span>
                                    </div>
                                    <div class="col-4 text-center">
                                        <div class="text-muted small" style="font-size: 11px;">RMSE</div>
                                        <span class="fw-bold small"><?= number_format($lr_preds[0]['rmse'], 2) ?></span>
                                    </div>
                                    <div class="col-4 text-center">
                                        <div class="text-muted small" style="font-size: 11px;">R² SCORE</div>
                                        <span class="fw-bold small text-profit"><?= number_format($lr_preds[0]['r2_score'], 4) ?></span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

    </div>

    <!-- RIGHT COLUMN: QUOTE METRICS CARD -->
    <div class="col-12 col-lg-4">
        
        <!-- AUTOMATED INVESTMENT ADVISOR CARD -->
        <div class="card-custom mb-4">
            <h5 class="fw-bold mb-3"><i class="fa-solid fa-robot me-2 text-profit" style="color:#10B981;"></i>Automated Research Verdict</h5>
            
            <div class="text-center py-2">
                <span class="advisor-verdict-badge <?= ($verdict === 'INVEST') ? 'badge-invest' : 'badge-avoid' ?>">
                    <?= esc($verdict) ?>
                </span>
                
                <div class="mt-3">
                    <span class="text-muted small d-block font-weight-bold">ALGORITHMIC CONFIDENCE</span>
                    <span class="fw-bold text-color" style="font-size: 18px;"><?= $confidence_score ?>%</span>
                    <div class="advisor-confidence-bar-outer" aria-hidden="true">
                        <div class="advisor-confidence-bar-inner <?= ($verdict === 'INVEST') ? 'bar-invest' : 'bar-avoid' ?>" style="width: <?= $confidence_score ?>%;"></div>
                    </div>
                </div>
            </div>

            <!-- Rules checklist breakdown -->
            <div class="mt-4">
                <h6 class="fw-bold mb-3 small text-uppercase" style="letter-spacing: 0.05em; color: var(--text-muted);">Technical Checkpoints</h6>
                <div class="advisor-checkpoints" role="list">
                    <?php foreach ($rules_breakdown as $rule): ?>
                        <?php 
                            $status = $rule['status'];
                            $badgeClass = strpos($status, 'BULLISH') !== false ? 'badge-bullish' : 
                                          (strpos($status, 'BEARISH') !== false ? 'badge-bearish' : 'badge-neutral');
                        ?>
                        <div class="checkpoint-row d-flex align-items-center justify-content-between gap-3" role="listitem" aria-label="<?= esc($rule['name']) ?>: <?= esc($rule['status']) ?>">
                            <div>
                                <span class="fw-semibold d-block small" style="font-size: 13px;"><?= esc($rule['name']) ?></span>
                                <span class="text-muted d-block" style="font-size: 11px;"><?= esc($rule['detail']) ?></span>
                            </div>
                            <div class="text-end" style="min-width: 90px;">
                                <span class="checkpoint-badge <?= $badgeClass ?>"><?= esc($rule['status']) ?></span>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- Disclaimer compliance trigger button -->
            <div class="mt-4 pt-3 border-top" style="border-color: var(--border-color) !important;">
                <button type="button" class="btn btn-sm btn-outline-secondary w-100 rounded-pill font-weight-bold" id="advisorComplianceBtn" aria-haspopup="dialog" aria-controls="advisorComplianceModal" style="font-size: 12px; color: var(--text-muted); border-color: var(--border-color) !important;">
                    <i class="fa-solid fa-shield-halved me-1"></i> View Regulatory Compliance
                </button>
            </div>
        </div>
        
        <!-- QUOTE SUMMARY -->
        <div class="card-custom mb-4">
            <h5 class="fw-bold mb-4">Stock Statistics</h5>
            
            <div id="liveQuoteGrid">
                <div class="mb-4 border-bottom pb-3" style="border-color: var(--border-color) !important;">
                    <div class="metric-title">Current Price</div>
                    <h2 class="fw-bold text-color mb-0" id="statPrice">₹<?= number_format($quote['price'], 2) ?></h2>
                </div>
                
                <div class="row g-3 mb-4">
                    <div class="col-6">
                        <div class="metric-title">Open Price</div>
                        <div class="metric-value" id="statOpen">₹<?= number_format($quote['open'], 2) ?></div>
                    </div>
                    <div class="col-6">
                        <div class="metric-title">Prev Close</div>
                        <div class="metric-value" id="statPrevClose">₹<?= number_format($quote['prev_close'], 2) ?></div>
                    </div>
                </div>
                
                <div class="row g-3 mb-4">
                    <div class="col-6">
                        <div class="metric-title">Day High</div>
                        <div class="metric-value text-profit" id="statHigh">₹<?= number_format($quote['high'], 2) ?></div>
                    </div>
                    <div class="col-6">
                        <div class="metric-title">Day Low</div>
                        <div class="metric-value text-loss" id="statLow">₹<?= number_format($quote['low'], 2) ?></div>
                    </div>
                </div>
                
                <div class="mb-4 pb-3 border-bottom" style="border-color: var(--border-color) !important;">
                    <div class="metric-title">Volume (Shares Traded)</div>
                    <div class="metric-value" id="statVolume"><?= number_format($quote['volume']) ?></div>
                </div>
                
                <div class="mb-2">
                    <div class="metric-title">Market Capitalization</div>
                    <h5 class="fw-bold text-color" id="statMarketCap">₹<?= number_format($quote['market_cap'] / 10000000, 2) ?> Cr</h5>
                </div>
            </div>
            
            <div class="p-3 rounded-3 mt-4" style="background: rgba(255,255,255,0.015); border: 1px solid var(--border-color); font-size: 13px;">
                <h6 class="fw-bold mb-2"><i class="fa-solid fa-circle-info me-1 text-profit"></i>Investment Advice</h6>
                <span class="text-muted d-block leading-tight">Predictions are built using historical sequential patterns and regression analysis. They are for simulator practice purposes. Always execute personal due diligence before investing in live stocks.</span>
            </div>
        </div>

    </div>
</div>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<!-- Chart.js and adapter plugins -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
    // 1. AJAX WATCHLIST TOGGLE
    const watchlistBtn = document.getElementById('watchlistToggleBtn');
    const watchlistIcon = watchlistBtn.querySelector('i');
    const watchlistText = document.getElementById('watchlistText');
    const stockId = <?= $stock['id'] ?>;

    watchlistBtn.addEventListener('click', () => {
        watchlistBtn.disabled = true;
        fetch(`<?= base_url('watchlist/toggle/') ?>` + stockId, {
            method: 'POST',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Content-Type': 'application/json'
            }
        })
        .then(res => res.json())
        .then(data => {
            watchlistBtn.disabled = false;
            if (data.status === 'added') {
                watchlistIcon.className = 'fa-solid fa-star';
                watchlistText.innerText = 'Watchlisted';
                watchlistBtn.classList.remove('btn-outline-success');
                watchlistBtn.classList.add('btn-success');
            } else if (data.status === 'removed') {
                watchlistIcon.className = 'fa-regular fa-star';
                watchlistText.innerText = 'Add Watchlist';
                watchlistBtn.classList.remove('btn-success');
                watchlistBtn.classList.add('btn-outline-success');
            }
        })
        .catch(() => {
            watchlistBtn.disabled = false;
        });
    });
    
    // Style check on load
    if (watchlistText.innerText === 'Watchlisted') {
        watchlistBtn.classList.remove('btn-outline-success');
        watchlistBtn.classList.add('btn-success');
    }

    // 2. LIVE PRICE AUTO-REFRESH (30s POLL)
    const symbol = '<?= $stock['symbol'] ?>';
    let countdown = 30;
    const timerText = document.getElementById('refreshTimerText');
    const livePrice = document.getElementById('liveStockPrice');
    const liveChange = document.getElementById('liveStockChange');
    
    // Quote stat DOM elements
    const statPrice = document.getElementById('statPrice');
    const statOpen = document.getElementById('statOpen');
    const statPrevClose = document.getElementById('statPrevClose');
    const statHigh = document.getElementById('statHigh');
    const statLow = document.getElementById('statLow');
    const statVolume = document.getElementById('statVolume');
    const statMarketCap = document.getElementById('statMarketCap');

    function refreshQuote() {
        fetch(`<?= base_url('tracker/api/quote/') ?>` + symbol)
        .then(res => res.json())
        .then(data => {
            // Flash prices on update
            const oldPrice = parseFloat(livePrice.innerText.replace(/[^\d.-]/g, ''));
            const newPrice = data.price;
            
            const cardEl = document.getElementById('liveQuoteGrid').parentElement;
            if (newPrice > oldPrice) {
                cardEl.classList.remove('flash-down');
                cardEl.classList.add('flash-up');
            } else if (newPrice < oldPrice) {
                cardEl.classList.remove('flash-up');
                cardEl.classList.add('flash-down');
            }
            setTimeout(() => cardEl.className = 'card-custom h-100', 1500);

            // Update Header Price
            livePrice.innerText = '₹' + newPrice.toFixed(2);
            
            // Update Header Change
            const isPos = data.change_percent >= 0;
            liveChange.className = isPos ? 'fw-semibold small text-profit' : 'fw-semibold small text-loss';
            liveChange.innerHTML = `
                <i class="fa-solid ${isPos ? 'fa-arrow-trend-up' : 'fa-arrow-trend-down'} me-1"></i>
                ${isPos ? '+' : ''}${data.change_percent.toFixed(2)}%
            `;

            // Update Side Stats Grid
            statPrice.innerText = '₹' + data.price.toFixed(2);
            statOpen.innerText = '₹' + data.open.toFixed(2);
            statPrevClose.innerText = '₹' + data.prev_close.toFixed(2);
            statHigh.innerText = '₹' + data.high.toFixed(2);
            statLow.innerText = '₹' + data.low.toFixed(2);
            statVolume.innerText = data.volume.toLocaleString('en-IN');
            statMarketCap.innerText = '₹' + (data.market_cap / 10000000).toFixed(2) + ' Cr';
        })
        .catch(err => console.log("AJAX price update failed: ", err));
    }

    setInterval(() => {
        countdown--;
        if (countdown <= 0) {
            timerText.innerText = 'Refreshing data...';
            refreshQuote();
            countdown = 30;
        } else {
            timerText.innerText = `Auto-refresh active. Next update in ${countdown} seconds...`;
        }
    }, 1000);


    // 3. CHART.JS INTEGRATION & FILTERS
    // Parse historical records injected from PHP
    const historyRaw = <?= json_encode($history) ?>;
    
    // Structure datasets
    const historyData = historyRaw.map(row => ({
        date: row.date,
        close: parseFloat(row.close),
        open: parseFloat(row.open),
        high: parseFloat(row.high),
        low: parseFloat(row.low),
        volume: parseInt(row.volume)
    }));

    let priceChart = null;
    let rangeChart = null;
    let volumeChart = null;

    function renderCharts(daysFilter = 365) {
        // Slice database history based on requested days lookback
        const filteredData = historyData.slice(-daysFilter);
        
        const labels = filteredData.map(item => item.date);
        const closes = filteredData.map(item => item.close);
        const highs = filteredData.map(item => item.high);
        const lows = filteredData.map(item => item.low);
        const volumes = filteredData.map(item => item.volume);
        
        const isUp = closes[closes.length - 1] >= closes[0];
        const primaryColor = isUp ? '#10B981' : '#EF4444';
        const shadowColor = isUp ? 'rgba(16, 185, 129, 0.15)' : 'rgba(239, 68, 68, 0.15)';

        // DESTROY EXISTING CHARTS
        if (priceChart) priceChart.destroy();
        if (rangeChart) rangeChart.destroy();
        if (volumeChart) volumeChart.destroy();

        // 1. PRICE TREND CHART
        const ctxPrice = document.getElementById('historicalPriceChart').getContext('2d');
        const priceGradient = ctxPrice.createLinearGradient(0, 0, 0, 300);
        priceGradient.addColorStop(0, shadowColor);
        priceGradient.addColorStop(1, 'transparent');

        priceChart = new Chart(ctxPrice, {
            type: 'line',
            data: {
                labels: labels,
                datasets: [{
                    label: 'Close Price',
                    data: closes,
                    borderColor: primaryColor,
                    backgroundColor: priceGradient,
                    fill: true,
                    borderWidth: 2.5,
                    pointRadius: 0,
                    pointHoverRadius: 5,
                    tension: 0.1
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { display: false } },
                scales: {
                    x: {
                        grid: { display: false },
                        ticks: { color: '#A0AEC0', maxTicksLimit: 8 }
                    },
                    y: {
                        grid: { color: 'rgba(255, 255, 255, 0.05)' },
                        ticks: { color: '#A0AEC0' }
                    }
                }
            }
        });

        // 2. HIGH-LOW RANGE CHART
        const ctxRange = document.getElementById('historicalRangeChart').getContext('2d');
        const rangeGradient = ctxRange.createLinearGradient(0, 0, 0, 300);
        rangeGradient.addColorStop(0, 'rgba(16, 185, 129, 0.08)');
        rangeGradient.addColorStop(1, 'transparent');

        rangeChart = new Chart(ctxRange, {
            type: 'line',
            data: {
                labels: labels,
                datasets: [
                    {
                        label: 'Day High',
                        data: highs,
                        borderColor: '#10B981',
                        borderWidth: 1.5,
                        pointRadius: 0,
                        tension: 0.1
                    },
                    {
                        label: 'Day Low',
                        data: lows,
                        borderColor: '#EF4444',
                        borderWidth: 1.5,
                        pointRadius: 0,
                        tension: 0.1
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { labels: { color: '#A0AEC0' } } },
                scales: {
                    x: {
                        grid: { display: false },
                        ticks: { color: '#A0AEC0', maxTicksLimit: 8 }
                    },
                    y: {
                        grid: { color: 'rgba(255, 255, 255, 0.05)' },
                        ticks: { color: '#A0AEC0' }
                    }
                }
            }
        });

        // 3. VOLUME BAR CHART
        const ctxVolume = document.getElementById('historicalVolumeChart').getContext('2d');
        volumeChart = new Chart(ctxVolume, {
            type: 'bar',
            data: {
                labels: labels,
                datasets: [{
                    label: 'Volume Traded',
                    data: volumes,
                    backgroundColor: 'rgba(16, 185, 129, 0.45)',
                    borderWidth: 0,
                    borderRadius: 4
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { display: false } },
                scales: {
                    x: {
                        grid: { display: false },
                        ticks: { color: '#A0AEC0', maxTicksLimit: 8 }
                    },
                    y: {
                        grid: { color: 'rgba(255, 255, 255, 0.05)' },
                        ticks: { color: '#A0AEC0', callback: value => (value / 100000).toFixed(0) + 'L' }
                    }
                }
            }
        });
    }

    // Bind chart lookback filter buttons
    const filterButtons = document.querySelectorAll('.chart-filter-btn');
    filterButtons.forEach(btn => {
        btn.addEventListener('click', (e) => {
            filterButtons.forEach(b => b.classList.remove('active'));
            e.target.classList.add('active');
            
            const days = parseInt(e.target.getAttribute('data-days'));
            renderCharts(days);
        });
    });

    // Initial render
    renderCharts(365);

    // 4. ACCESSIBLE COMPLIANCE MODAL CONTROLS
    const modal = document.getElementById('advisorComplianceModal');
    const modalWindow = document.getElementById('advisorModalWindow');
    const openBtn = document.getElementById('advisorComplianceBtn');
    const closeBtn = document.getElementById('advisorCloseModalBtn');
    let lastFocusedElement = null;

    function openModal() {
        lastFocusedElement = document.activeElement;
        modal.style.display = 'flex';
        closeBtn.focus();
        
        // Trap focus inside modal
        document.addEventListener('keydown', trapFocus);
    }

    function closeModal() {
        modal.style.display = 'none';
        document.removeEventListener('keydown', trapFocus);
        
        // Restore focus
        if (lastFocusedElement) {
            lastFocusedElement.focus();
        }
    }

    function trapFocus(e) {
        if (e.key === 'Escape') {
            closeModal();
            return;
        }
        
        if (e.key === 'Tab') {
            const focusables = modalWindow.querySelectorAll('button, [tabindex="0"]');
            const firstFocusable = focusables[0];
            const lastFocusable = focusables[focusables.length - 1];
            
            if (e.shiftKey) { // Back tab
                if (document.activeElement === firstFocusable) {
                    lastFocusable.focus();
                    e.preventDefault();
                }
            } else { // Forward tab
                if (document.activeElement === lastFocusable) {
                    firstFocusable.focus();
                    e.preventDefault();
                }
            }
        }
    }

    if (openBtn) openBtn.addEventListener('click', openModal);
    if (closeBtn) closeBtn.addEventListener('click', closeModal);
    modal.addEventListener('click', (e) => {
        if (e.target === modal) {
            closeModal();
        }
    });
</script>

<!-- ACCESSIBLE COMPLIANCE MODAL -->
<div class="advisor-modal-overlay" id="advisorComplianceModal" role="dialog" aria-modal="true" aria-labelledby="advisorModalTitle" aria-describedby="advisorModalDesc">
    <div class="advisor-modal-content" id="advisorModalWindow">
        <div class="border-bottom pb-2 mb-3">
            <h3 id="advisorModalTitle" style="font-size: 1.25rem; font-weight: 800; color: var(--text-color);">Legal &amp; Regulatory Compliance Framework</h3>
        </div>
        <div class="modal-body" id="advisorModalDesc" style="font-size: 0.9rem; color: var(--text-muted); line-height: 1.6;">
            <p class="mb-3"><strong>Informational Limitation:</strong> This application executes rule-based computational algorithms based on historical stock market pricing data. It does not evaluate individual user financial situations, risk tolerance, or portfolio assets.</p>
            
            <p class="mb-3"><strong>System Disclaimer:</strong> This tool provides automated data analysis for informational purposes only. It does not constitute formal financial advice. No financial transactions, brokerage operations, or portfolio management decisions are performed by this system.</p>
            
            <p class="mb-3"><strong>Underlying Data Integrity:</strong> Financial numbers (Open, Close, High, Low, Volume) are fetched using public data services. We do not guarantee absolute synchronization, speed, or historical accuracy of this public ticker feed.</p>
            
            <p><strong>Strict AA Accessibility Compliance:</strong> This framework utilizes fully compliant high-contrast layouts (exceeding 4.5:1), semantic labels, full keyboard tab focus locks, and live region announcers for blind, low-vision, or motor-impaired individuals.</p>
        </div>
        <button class="advisor-close-btn mt-3" id="advisorCloseModalBtn" aria-label="Close compliance dialog">Close Dialog</button>
    </div>
</div>
<?= $this->endSection() ?>
