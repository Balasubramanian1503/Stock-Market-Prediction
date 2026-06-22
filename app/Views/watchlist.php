<?= $this->extend('layouts/main') ?>

<?= $this->section('title') ?>
My Watchlist
<?= $this->endSection() ?>

<?= $this->section('content') ?>

<!-- PAGE HEADER -->
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h2 class="fw-bold mb-1">My Watchlist</h2>
        <p class="text-muted mb-0">Monitor your favorite Indian stocks and track next-day machine learning predictions.</p>
    </div>
</div>

<!-- WATCHLIST TABLE -->
<div class="card-custom">
    <?php if (empty($watchlist)): ?>
        <div class="text-center py-5">
            <div class="mb-3 text-muted">
                <i class="fa-regular fa-star fs-1 text-emerald" style="color: #10B981;"></i>
            </div>
            <h5 class="fw-bold">Your Watchlist is Empty</h5>
            <p class="text-muted small mx-auto" style="max-width: 400px;">Use the top search bar to find major NSE tickers like RELIANCE, TCS, or INFY, and click "Add Watchlist" to begin monitoring them here.</p>
            <a href="<?= base_url('dashboard') ?>" class="btn btn-sm btn-success rounded-pill px-4 py-2 mt-2 font-weight-bold">
                <i class="fa-solid fa-compass me-1"></i> Explore Stocks
            </a>
        </div>
    <?php else: ?>
        <div class="table-responsive">
            <table class="table table-hover align-middle" style="color: var(--text-color);">
                <thead>
                    <tr style="border-bottom: 2px solid var(--border-color); color: var(--text-muted); font-size: 13px;">
                        <th>SYMBOL</th>
                        <th>COMPANY</th>
                        <th>SECTOR</th>
                        <th class="text-end">LIVE PRICE (₹)</th>
                        <th class="text-end">DAILY CHANGE</th>
                        <th class="text-end text-profit">TOMORROW (LSTM)</th>
                        <th class="text-end text-profit">TOMORROW (LR)</th>
                        <th class="text-center">ACTIONS</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($watchlist as $item): ?>
                        <?php 
                            $isPositive = $item['change_percent'] >= 0;
                            $classColor = $isPositive ? 'text-profit' : 'text-loss';
                            $caretIcon = $isPositive ? 'fa-arrow-trend-up' : 'fa-arrow-trend-down';
                        ?>
                        <tr style="border-bottom: 1px solid var(--border-color);" id="row-<?= $item['watchlist_id'] ?>">
                            <td class="fw-bold"><?= esc(str_replace('.NS', '', $item['symbol'])) ?></td>
                            <td>
                                <span class="fw-semibold d-block"><?= esc($item['company_name']) ?></span>
                            </td>
                            <td>
                                <span class="badge bg-secondary bg-opacity-10 text-muted px-2 py-1 rounded small">
                                    <?= esc($item['sector']) ?>
                                </span>
                            </td>
                            <td class="text-end fw-semibold">₹<?= number_format($item['price'], 2) ?></td>
                            <td class="text-end <?= $classColor ?> fw-medium">
                                <i class="fa-solid <?= $caretIcon ?> me-1 small"></i><?= $isPositive ? '+' : '' ?><?= number_format($item['change_percent'], 2) ?>%
                            </td>
                            <td class="text-end fw-bold text-profit">
                                <?= $item['predicted_lstm'] > 0 ? '₹' . number_format($item['predicted_lstm'], 2) : '<span class="text-muted fw-normal small">Not trained</span>' ?>
                            </td>
                            <td class="text-end fw-bold text-profit">
                                <?= $item['predicted_lr'] > 0 ? '₹' . number_format($item['predicted_lr'], 2) : '<span class="text-muted fw-normal small">Not trained</span>' ?>
                            </td>
                            <td class="text-center">
                                <a href="<?= base_url('tracker/' . $item['symbol']) ?>" class="btn btn-sm btn-outline-success rounded-pill px-3 py-1 font-weight-bold" style="font-size: 12px;" title="View stock charts & predictions">
                                    <i class="fa-solid fa-chart-line me-1"></i> Analyze
                                </a>
                                <button class="btn btn-sm btn-outline-danger rounded-pill px-3 py-1 font-weight-bold ms-1" style="font-size: 12px;" onclick="removeFavorite(<?= $item['id'] ?>, <?= $item['watchlist_id'] ?>)" title="Remove from watchlist">
                                    <i class="fa-solid fa-trash-can"></i>
                                </button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</div>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
    function removeFavorite(stockId, watchlistId) {
        if (confirm("Are you sure you want to remove this stock from your watchlist?")) {
            fetch(`<?= base_url('watchlist/toggle/') ?>` + stockId, {
                method: 'POST',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Content-Type': 'application/json'
                }
            })
            .then(res => res.json())
            .then(data => {
                if (data.status === 'removed') {
                    // Remove row from table
                    const row = document.getElementById('row-' + watchlistId);
                    if (row) {
                        row.remove();
                    }
                    
                    // Reload page if all favorited items are removed to show empty card
                    const tbody = document.querySelector('tbody');
                    if (tbody && tbody.children.length === 0) {
                        window.location.reload();
                    }
                }
            })
            .catch(err => console.log("AJAX remove favorite failed: ", err));
        }
    }
</script>
<?= $this->endSection() ?>
