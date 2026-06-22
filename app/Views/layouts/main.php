<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $this->renderSection('title') ?> - StockScribe</title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" onerror="this.onerror=null;this.href='https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css';">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <!-- FontAwesome Icons -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    
    <style>
        :root {
            --bg-color: #0B132B;
            --card-bg: #1C2541;
            --text-color: #F8F9FA;
            --text-muted: #A0AEC0;
            --border-color: rgba(255, 255, 255, 0.1);
            --sidebar-bg: #0B132B;
            --sidebar-text: #F8F9FA;
            --sidebar-border: rgba(255, 255, 255, 0.08);
            --active-bg: rgba(16, 185, 129, 0.15);
            --active-color: #10B981;
            --shadow: 0 4px 20px rgba(0, 0, 0, 0.25);
        }

        body.light-mode {
            --bg-color: #F3F4F6;
            --card-bg: #FFFFFF;
            --text-color: #1F2937;
            --text-muted: #6B7280;
            --border-color: rgba(0, 0, 0, 0.08);
            --sidebar-bg: #111827; /* Keep sidebar dark for premium contrast */
            --sidebar-text: #F9FAFB;
            --sidebar-border: rgba(255, 255, 255, 0.05);
            --active-bg: rgba(16, 185, 129, 0.12);
            --active-color: #059669;
            --shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
        }

        body {
            font-family: 'Inter', sans-serif;
            background-color: var(--bg-color);
            color: var(--text-color);
            min-height: 100vh;
            display: flex;
            overflow-x: hidden;
            transition: background-color 0.3s ease, color 0.3s ease;
        }

        /* Sidebar styling */
        #sidebar {
            width: 260px;
            background-color: var(--sidebar-bg);
            border-right: 1px solid var(--sidebar-border);
            display: flex;
            flex-direction: column;
            position: fixed;
            top: 0;
            bottom: 0;
            left: 0;
            z-index: 1000;
            transition: all 0.3s ease;
        }

        .sidebar-brand {
            padding: 24px;
            font-size: 22px;
            font-weight: 700;
            color: #10B981;
            border-bottom: 1px solid var(--sidebar-border);
            letter-spacing: -0.5px;
            display: flex;
            align-items: center;
        }

        .sidebar-brand i {
            margin-right: 10px;
        }

        .sidebar-user {
            padding: 20px 24px;
            border-bottom: 1px solid var(--sidebar-border);
            display: flex;
            align-items: center;
        }

        .user-avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background-color: #10B981;
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 600;
            font-size: 16px;
            margin-right: 12px;
        }

        .user-info {
            line-height: 1.2;
        }

        .user-name {
            font-weight: 600;
            color: var(--sidebar-text);
            font-size: 15px;
        }

        .user-role {
            font-size: 12px;
            color: #10B981;
            font-weight: 500;
            text-transform: uppercase;
        }

        .sidebar-menu {
            list-style: none;
            padding: 20px 12px;
            margin: 0;
            flex-grow: 1;
            overflow-y: auto;
        }

        .menu-item {
            margin-bottom: 5px;
        }

        .menu-link {
            display: flex;
            align-items: center;
            padding: 12px 16px;
            color: #A0AEC0;
            text-decoration: none;
            border-radius: 8px;
            font-weight: 500;
            font-size: 15px;
            transition: all 0.2s ease;
        }

        .menu-link:hover {
            color: var(--sidebar-text);
            background-color: rgba(255, 255, 255, 0.05);
        }

        .menu-item.active .menu-link {
            background-color: var(--active-bg);
            color: #10B981;
            font-weight: 600;
        }

        .menu-link i {
            width: 24px;
            font-size: 16px;
            margin-right: 10px;
        }

        .sidebar-footer {
            padding: 16px 24px;
            border-top: 1px solid var(--sidebar-border);
        }

        .logout-btn {
            color: #EF4444;
            text-decoration: none;
            font-weight: 600;
            display: flex;
            align-items: center;
            font-size: 14px;
            transition: transform 0.2s ease;
        }

        .logout-btn:hover {
            transform: translateX(3px);
            color: #F87171;
        }

        .logout-btn i {
            margin-right: 8px;
        }

        /* Main Content Wrapper */
        #content-wrapper {
            margin-left: 260px;
            flex-grow: 1;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            transition: all 0.3s ease;
        }

        /* Top Navbar */
        .top-navbar {
            height: 70px;
            background-color: var(--card-bg);
            border-bottom: 1px solid var(--border-color);
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0 30px;
            position: sticky;
            top: 0;
            z-index: 999;
            transition: background-color 0.3s ease, border-color 0.3s ease;
        }

        .search-container {
            position: relative;
            width: 320px;
        }

        .search-input {
            background-color: var(--bg-color) !important;
            border: 1px solid var(--border-color) !important;
            color: var(--text-color) !important;
            border-radius: 20px;
            padding: 8px 16px 8px 40px;
            font-size: 14px;
            width: 100%;
            transition: all 0.2s ease;
        }

        .search-input:focus {
            border-color: #10B981 !important;
            box-shadow: 0 0 0 0.2rem rgba(16, 185, 129, 0.15) !important;
        }

        .search-icon {
            position: absolute;
            left: 15px;
            top: 50%;
            transform: translateY(-50%);
            color: var(--text-muted);
            font-size: 14px;
        }

        .navbar-actions {
            display: flex;
            align-items: center;
            gap: 20px;
        }

        .mode-toggle-btn {
            background: none;
            border: none;
            color: var(--text-color);
            font-size: 20px;
            cursor: pointer;
            padding: 5px;
            transition: transform 0.2s ease;
        }

        .mode-toggle-btn:hover {
            transform: rotate(15deg) scale(1.1);
            color: #10B981;
        }

        /* Standard dashboard cards */
        .dashboard-container {
            padding: 30px;
            flex-grow: 1;
        }

        .card-custom {
            background-color: var(--card-bg);
            border: 1px solid var(--border-color);
            border-radius: 12px;
            padding: 24px;
            box-shadow: var(--shadow);
            transition: background-color 0.3s ease, border-color 0.3s ease, box-shadow 0.3s ease, transform 0.2s ease;
        }

        .card-custom:hover {
            transform: translateY(-2px);
        }

        /* Profit/Loss coloring */
        .text-profit {
            color: #10B981 !important; /* Emerald */
        }
        .text-loss {
            color: #EF4444 !important; /* Crimson */
        }

        .bg-profit-soft {
            background-color: rgba(16, 185, 129, 0.1) !important;
        }
        
        .bg-loss-soft {
            background-color: rgba(239, 68, 68, 0.1) !important;
        }

        /* Responsive breakpoints */
        @media (max-width: 991.98px) {
            #sidebar {
                left: -260px;
            }
            #sidebar.active {
                left: 0;
            }
            #content-wrapper {
                margin-left: 0;
            }
            .sidebar-toggle {
                display: block !important;
            }
        }

        .sidebar-toggle {
            display: none;
            background: none;
            border: none;
            color: var(--text-color);
            font-size: 22px;
            cursor: pointer;
            margin-right: 15px;
        }

        /* Dropdown suggestions list */
        .suggestions-dropdown {
            position: absolute;
            top: 100%;
            left: 0;
            right: 0;
            background: var(--card-bg);
            border: 1px solid var(--border-color);
            border-radius: 8px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.3);
            list-style: none;
            padding: 5px 0;
            margin: 5px 0 0 0;
            display: none;
            z-index: 1050;
        }

        .suggestion-item {
            padding: 8px 16px;
            cursor: pointer;
            color: var(--text-color);
            font-size: 14px;
            transition: background 0.2s ease;
            display: flex;
            justify-content: space-between;
        }

        .suggestion-item:hover {
            background: rgba(16, 185, 129, 0.15);
            color: #10B981;
        }

        .suggestion-sym {
            font-weight: 600;
        }
        
        .suggestion-name {
            font-size: 12px;
            color: var(--text-muted);
            max-width: 180px;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }
    </style>
    <?= $this->renderSection('styles') ?>
</head>
<body>

    <!-- SIDEBAR -->
    <div id="sidebar">
        <div class="sidebar-brand">
            <i class="fa-solid fa-chart-line"></i> StockScribe
        </div>
        
        <div class="sidebar-user">
            <div class="user-avatar">
                <?= strtoupper(substr(session()->get('name'), 0, 1)) ?>
            </div>
            <div class="user-info">
                <div class="user-name"><?= esc(session()->get('name')) ?></div>
                <div class="user-role"><?= esc(session()->get('role')) ?></div>
            </div>
        </div>
        
        <ul class="sidebar-menu">
            <li class="menu-item <?= current_url() == base_url('dashboard') ? 'active' : '' ?>">
                <a href="<?= base_url('dashboard') ?>" class="menu-link">
                    <i class="fa-solid fa-gauge"></i> Dashboard
                </a>
            </li>
            
            <li class="menu-item <?= strpos(current_url(), 'watchlist') !== false ? 'active' : '' ?>">
                <a href="<?= base_url('watchlist') ?>" class="menu-link">
                    <i class="fa-solid fa-star"></i> Watchlist
                </a>
            </li>
            
            <li class="menu-item <?= strpos(current_url(), 'portfolio') !== false ? 'active' : '' ?>">
                <a href="<?= base_url('portfolio') ?>" class="menu-link">
                    <i class="fa-solid fa-wallet"></i> Portfolio Simulator
                </a>
            </li>

            <li class="menu-item <?= strpos(current_url(), 'profile') !== false ? 'active' : '' ?>">
                <a href="<?= base_url('profile') ?>" class="menu-link">
                    <i class="fa-solid fa-user"></i> My Profile
                </a>
            </li>

            <li class="menu-item <?= strpos(current_url(), 'news') !== false ? 'active' : '' ?>">
                <a href="<?= base_url('news') ?>" class="menu-link">
                    <i class="fa-solid fa-newspaper"></i> Market News
                </a>
            </li>
            
            <?php if (session()->get('role') === 'admin'): ?>
                <li class="menu-item <?= strpos(current_url(), 'admin') !== false ? 'active' : '' ?>">
                    <a href="<?= base_url('admin') ?>" class="menu-link" style="color: #FBBF24;">
                        <i class="fa-solid fa-user-shield" style="color: #FBBF24;"></i> Admin Control
                    </a>
                </li>
            <?php endif; ?>
        </ul>
        
        <div class="sidebar-footer">
            <a href="<?= base_url('logout') ?>" class="logout-btn">
                <i class="fa-solid fa-power-off"></i> Logout Account
            </a>
        </div>
    </div>

    <!-- CONTENT WRAPPER -->
    <div id="content-wrapper">
        
        <!-- NAVBAR -->
        <div class="top-navbar">
            <div class="d-flex align-items-center">
                <button class="sidebar-toggle" id="sidebarToggleBtn">
                    <i class="fa-solid fa-bars"></i>
                </button>
                
                <div class="search-container">
                    <i class="fa-solid fa-magnifying-glass search-icon"></i>
                    <input type="text" class="search-input" id="stockSearchInput" placeholder="Search NSE Tickers (e.g. RELIANCE)..." autocomplete="off">
                    <ul class="suggestions-dropdown" id="searchSuggestionsList"></ul>
                </div>
            </div>
            
            <div class="navbar-actions">
                <!-- Market Status Badge -->
                <span class="badge bg-profit-soft text-profit border border-success px-3 py-2 rounded-pill font-weight-bold">
                    <i class="fa-solid fa-circle-play me-1 small"></i> Live Tracking Active
                </span>
                
                <!-- Dark Mode Toggle -->
                <button class="mode-toggle-btn" id="darkModeToggleBtn" title="Toggle Dark/Light Mode">
                    <i class="fa-solid fa-moon" id="modeIcon"></i>
                </button>
            </div>
        </div>
        
        <!-- DASHBOARD CONTAINER -->
        <div class="dashboard-container">
            <?= $this->renderSection('content') ?>
        </div>
        
    </div>

    <!-- Bootstrap 5 Bundle JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        // Dark Mode Logic
        const body = document.body;
        const darkModeBtn = document.getElementById('darkModeToggleBtn');
        const modeIcon = document.getElementById('modeIcon');
        
        // Load preference
        if (localStorage.getItem('theme') === 'light') {
            body.classList.add('light-mode');
            modeIcon.className = 'fa-solid fa-sun';
        } else {
            body.classList.remove('light-mode');
            modeIcon.className = 'fa-solid fa-moon';
        }
        
        darkModeBtn.addEventListener('click', () => {
            if (body.classList.contains('light-mode')) {
                body.classList.remove('light-mode');
                modeIcon.className = 'fa-solid fa-moon';
                localStorage.setItem('theme', 'dark');
            } else {
                body.classList.add('light-mode');
                modeIcon.className = 'fa-solid fa-sun';
                localStorage.setItem('theme', 'light');
            }
        });

        // Sidebar Responsive Toggle
        const sidebar = document.getElementById('sidebar');
        const sidebarToggle = document.getElementById('sidebarToggleBtn');
        
        sidebarToggle.addEventListener('click', () => {
            sidebar.classList.toggle('active');
        });

        // Close sidebar when clicking outside on mobile
        document.addEventListener('click', (event) => {
            if (window.innerWidth < 992) {
                if (!sidebar.contains(event.target) && !sidebarToggle.contains(event.target)) {
                    sidebar.classList.remove('active');
                }
            }
        });

        // Search Auto-Suggestions List
        // Pre-seeded NSE stock symbols list
        const nseStocksList = [
            { symbol: 'RELIANCE.NS', name: 'Reliance Industries Limited' },
            { symbol: 'TCS.NS', name: 'Tata Consultancy Services Limited' },
            { symbol: 'INFY.NS', name: 'Infosys Limited' },
            { symbol: 'HDFCBANK.NS', name: 'HDFC Bank Limited' },
            { symbol: 'ICICIBANK.NS', name: 'ICICI Bank Limited' },
            { symbol: 'SBIN.NS', name: 'State Bank of India' },
            { symbol: 'TATASTEEL.NS', name: 'Tata Steel Limited' },
            { symbol: 'WIPRO.NS', name: 'Wipro Limited' },
            { symbol: 'ITC.NS', name: 'ITC Limited' },
            { symbol: 'LT.NS', name: 'Larsen & Toubro Limited' }
        ];

        const searchInput = document.getElementById('stockSearchInput');
        const suggestionsList = document.getElementById('searchSuggestionsList');

        searchInput.addEventListener('input', (e) => {
            const val = e.target.value.toUpperCase().trim();
            suggestionsList.innerHTML = '';
            
            if (!val) {
                suggestionsList.style.display = 'none';
                return;
            }

            const matches = nseStocksList.filter(item => 
                item.symbol.includes(val) || 
                item.name.toUpperCase().includes(val)
            );

            if (matches.length > 0) {
                matches.forEach(item => {
                    const li = document.createElement('li');
                    li.className = 'suggestion-item';
                    li.innerHTML = `
                        <span class="suggestion-sym">${item.symbol}</span>
                        <span class="suggestion-name">${item.name}</span>
                    `;
                    li.addEventListener('click', () => {
                        window.location.href = `<?= base_url('tracker/') ?>` + item.symbol;
                    });
                    suggestionsList.appendChild(li);
                });
                suggestionsList.style.display = 'block';
            } else {
                suggestionsList.style.display = 'none';
            }
        });

        // Hide suggestions when clicking outside
        document.addEventListener('click', (e) => {
            if (!searchInput.contains(e.target) && !suggestionsList.contains(e.target)) {
                suggestionsList.style.display = 'none';
            }
        });
    </script>
    <?= $this->renderSection('scripts') ?>
</body>
</html>
