<?= $this->extend('layouts/main') ?>

<?= $this->section('title') ?>
Market News
<?= $this->endSection() ?>

<?= $this->section('styles') ?>
<style>
    .category-pill {
        display: inline-block;
        padding: 8px 18px;
        border-radius: 20px;
        background-color: var(--card-bg);
        border: 1px solid var(--border-color);
        color: var(--text-muted);
        font-weight: 500;
        text-decoration: none;
        font-size: 14px;
        transition: all 0.2s ease;
    }
    .category-pill.active, .category-pill:hover {
        background-color: var(--active-bg);
        color: #10B981;
        border-color: #10B981;
    }
    .news-card {
        background-color: var(--card-bg);
        border: 1px solid var(--border-color);
        border-radius: 12px;
        padding: 24px;
        transition: transform 0.2s ease, box-shadow 0.2s ease;
        margin-bottom: 20px;
    }
    .news-card:hover {
        transform: translateY(-2px);
        box-shadow: var(--shadow);
    }
</style>
<?= $this->endSection() ?>

<?= $this->section('content') ?>

<!-- PAGE HEADER -->
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h2 class="fw-bold mb-1">Market News</h2>
        <p class="text-muted mb-0">Stay informed with the latest Indian stock market headlines, policies, and sector updates.</p>
    </div>
</div>

<!-- CATEGORY FILTER PILLS -->
<div class="d-flex gap-2 mb-4 overflow-auto pb-2 flex-nowrap" style="white-space: nowrap;">
    <a href="<?= base_url('news') ?>" class="category-pill <?= empty($active_category) ? 'active' : '' ?>">
        <i class="fa-solid fa-globe me-1"></i> All Markets
    </a>
    <?php foreach ($categories as $cat): ?>
        <a href="<?= base_url('news?category=' . urlencode($cat)) ?>" class="category-pill <?= $active_category === $cat ? 'active' : '' ?>">
            <?php 
                $icon = 'fa-newspaper';
                if ($cat === 'NIFTY') $icon = 'fa-chart-line';
                if ($cat === 'Banking') $icon = 'fa-building-columns';
                if ($cat === 'IT Sector') $icon = 'fa-laptop-code';
                if ($cat === 'Energy Sector') $icon = 'fa-solar-panel';
                if ($cat === 'Auto Sector') $icon = 'fa-car-side';
            ?>
            <i class="fa-solid <?= $icon ?> me-1"></i> <?= esc($cat) ?>
        </a>
    <?php endforeach; ?>
</div>

<!-- NEWS CARDS LIST -->
<div class="row">
    <div class="col-12">
        <?php if (empty($news)): ?>
            <div class="card-custom text-center py-5">
                <div class="mb-3 text-muted">
                    <i class="fa-solid fa-magnifying-glass fs-1 text-emerald" style="color: #10B981;"></i>
                </div>
                <h5 class="fw-bold">No articles found</h5>
                <p class="text-muted small mx-auto" style="max-width: 400px;">There are currently no market updates seeded under this sector. Select another sector or check back later.</p>
            </div>
        <?php else: ?>
            <?php foreach ($news as $n): ?>
                <div class="news-card">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <div class="d-flex align-items-center gap-2">
                            <span class="badge bg-success bg-opacity-10 text-profit px-2 py-1 rounded small">
                                <?= esc($n['category']) ?>
                            </span>
                            <span class="text-muted small"><i class="fa-solid fa-calendar me-1"></i><?= date('F d, Y \a\t h:i A', strtotime($n['published_at'])) ?></span>
                        </div>
                        <span class="text-muted small"><i class="fa-solid fa-circle-nodes me-1"></i><?= esc($n['source']) ?></span>
                    </div>
                    
                    <h4 class="fw-bold text-color mb-3"><?= esc($n['title']) ?></h4>
                    <p class="fw-medium text-color mb-3" style="font-size: 15px;"><?= esc($n['summary']) ?></p>
                    <p class="text-muted small mb-0" style="line-height: 1.6;"><?= esc($n['content']) ?></p>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>

<?= $this->endSection() ?>
