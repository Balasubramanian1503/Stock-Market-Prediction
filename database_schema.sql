CREATE DATABASE IF NOT EXISTS stock_tracker_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE stock_tracker_db;

-- 1. Users Table
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    role ENUM('user', 'admin') DEFAULT 'user',
    status ENUM('active', 'inactive') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_email (email)
) ENGINE=InnoDB;

-- 2. Stocks Supported Table
CREATE TABLE IF NOT EXISTS stocks (
    id INT AUTO_INCREMENT PRIMARY KEY,
    symbol VARCHAR(20) NOT NULL UNIQUE,
    company_name VARCHAR(150) NOT NULL,
    sector VARCHAR(100) DEFAULT 'General',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- 3. Stock History Table
CREATE TABLE IF NOT EXISTS stock_history (
    id INT AUTO_INCREMENT PRIMARY KEY,
    stock_id INT NOT NULL,
    date DATE NOT NULL,
    open DECIMAL(15, 4) NOT NULL,
    high DECIMAL(15, 4) NOT NULL,
    low DECIMAL(15, 4) NOT NULL,
    close DECIMAL(15, 4) NOT NULL,
    volume BIGINT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (stock_id) REFERENCES stocks(id) ON DELETE CASCADE,
    UNIQUE KEY uq_stock_date (stock_id, date),
    INDEX idx_date (date)
) ENGINE=InnoDB;

-- 4. Predictions Table
CREATE TABLE IF NOT EXISTS predictions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    stock_id INT NOT NULL,
    model_type ENUM('linear_regression', 'lstm') NOT NULL,
    target_date DATE NOT NULL,
    predicted_price DECIMAL(15, 4) NOT NULL,
    mae DECIMAL(10, 4) DEFAULT 0.0000,
    rmse DECIMAL(10, 4) DEFAULT 0.0000,
    r2_score DECIMAL(10, 4) DEFAULT 0.0000,
    confidence_score DECIMAL(5, 2) DEFAULT 0.00,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (stock_id) REFERENCES stocks(id) ON DELETE CASCADE,
    INDEX idx_stock_date_model (stock_id, target_date, model_type)
) ENGINE=InnoDB;

-- 5. Watchlist Table
CREATE TABLE IF NOT EXISTS watchlists (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    stock_id INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (stock_id) REFERENCES stocks(id) ON DELETE CASCADE,
    UNIQUE KEY uq_user_stock (user_id, stock_id)
) ENGINE=InnoDB;

-- 6. Portfolios Table
CREATE TABLE IF NOT EXISTS portfolios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    stock_id INT NOT NULL,
    quantity INT NOT NULL CHECK (quantity > 0),
    purchase_price DECIMAL(15, 4) NOT NULL CHECK (purchase_price > 0),
    purchase_date DATE NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (stock_id) REFERENCES stocks(id) ON DELETE CASCADE,
    INDEX idx_user_portfolio (user_id)
) ENGINE=InnoDB;

-- 7. Market News Table
CREATE TABLE IF NOT EXISTS news (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    summary TEXT,
    content TEXT,
    category ENUM('NIFTY', 'Banking', 'IT Sector', 'Energy Sector', 'Auto Sector') NOT NULL,
    source VARCHAR(100) DEFAULT 'Market News Feed',
    published_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- 8. Admin & Prediction Logs Table
CREATE TABLE IF NOT EXISTS logs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    action VARCHAR(255) NOT NULL,
    details TEXT,
    user_id INT DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB;

-- ==========================================
-- SEED DATA
-- ==========================================

-- Seed Stocks
INSERT INTO stocks (symbol, company_name, sector) VALUES
('RELIANCE.NS', 'Reliance Industries Limited', 'Energy Sector'),
('TCS.NS', 'Tata Consultancy Services Limited', 'IT Sector'),
('INFY.NS', 'Infosys Limited', 'IT Sector'),
('HDFCBANK.NS', 'HDFC Bank Limited', 'Banking'),
('ICICIBANK.NS', 'ICICI Bank Limited', 'Banking'),
('SBIN.NS', 'State Bank of India', 'Banking'),
('TATAMOTORS.NS', 'Tata Motors Limited', 'Auto Sector'),
('WIPRO.NS', 'Wipro Limited', 'IT Sector'),
('ITC.NS', 'ITC Limited', 'Consumer Goods'),
('LT.NS', 'Larsen & Toubro Limited', 'Infrastructure')
ON DUPLICATE KEY UPDATE company_name = VALUES(company_name), sector = VALUES(sector);

-- Seed Users
-- Default Admin: admin@stocktracker.in / Admin@1234
-- Default User: investor@stocktracker.in / User@1234
INSERT INTO users (name, email, password, role, status) VALUES
('System Admin', 'admin@stocktracker.in', '$2y$10$DAZ44/TsopvsrIuj84jDhebeU2iKvQJgvUXSN9tdCvJUUd3DDj5u6', 'admin', 'active'),
('Retail Investor', 'investor@stocktracker.in', '$2y$10$vhc2CHlxXiMKojQfPwI5d.0yvNKin9bcfVVs0AwcBznlwO4.Sirn6', 'user', 'active')
ON DUPLICATE KEY UPDATE password = VALUES(password);

-- Seed Market News
INSERT INTO news (title, summary, content, category, source) VALUES
('NIFTY 50 Touches All-Time High Amid Strong Foreign Institutional Inflows', 'Nifty gains 150 points to cross key benchmark milestones supported by strong buying in large caps.', 'The NIFTY 50 index surged to a record high today as global investor confidence increased and domestic institutional buying remained robust. Financial, automobile, and IT shares led the rally, with major gains in Reliance and HDFC Bank. Analysts expect the momentum to continue into the upcoming corporate earnings season.', 'NIFTY', 'NSE India Press'),
('Banking Sector Reform: RBI Announces New Guidelines for Digital Lending Systems', 'Reserve Bank of India updates compliance rules for scheduled commercial banks and digital credit apps.', 'In a bid to safeguard retail borrowers, the Reserve Bank of India (RBI) has introduced stricter compliance standards for digital lending. Under the new frameworks, all loans must be disbursed directly to the bank accounts of borrowers, preventing third-party transit. Banking stocks responded positively with ICICI and SBI leading the gainers.', 'Banking', 'RBI Bulletin'),
('IT Sector Outlook: Emerging Cloud and AI Contracts Drive FY27 Bookings', 'India IT majors TCS and Infosys secure multi-billion dollar enterprise cloud transformation deals.', 'Tata Consultancy Services and Infosys have announced separate multi-billion dollar AI and cloud integration contracts with leading European firms. Despite global headwinds, demand for automated enterprise solutions remains strong, driving active talent acquisition and strong operating margins.', 'IT Sector', 'Tech Insights'),
('Energy Sector Evolution: Reliance Expands Renewable Energy Infras in Gujarat', 'Reliance Industries moves closer to its net-zero goal with the installation of a new solar gigafactory.', 'Reliance Industries is fast-tracking the deployment of its renewable energy ecosystem. The conglomerate is building a fully integrated solar PV giga-factory in Jamnagar, Gujarat. The facility will manufacture high-efficiency solar cells and panels, bolstering India self-reliance in clean energy systems.', 'Energy Sector', 'CleanEnergy Wire'),
('Auto Sector Growth: EV Sales Skyrocket in India with Tata Motors Dominating', 'Electric passenger vehicles witness record quarterly growth, driven by Tata Motors affordable EV models.', 'Tata Motors continues to command the Indian electric vehicle market, securing over 70% market share in the EV passenger segment. The success of the Nexon EV and Tiago EV has prompted competitors to accelerate their launch pipelines. Sector analysts project double-digit EV penetration by 2030.', 'Auto Sector', 'AutoNews India')
ON DUPLICATE KEY UPDATE summary = VALUES(summary);
