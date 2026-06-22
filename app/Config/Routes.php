<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */

// 1. Root and Public Auth Routes
$routes->get('/', function() {
    if (session()->get('isLoggedIn')) {
        return redirect()->to('/dashboard');
    }
    return redirect()->to('/login');
});

$routes->get('login', 'Auth::login');
$routes->post('login/submit', 'Auth::loginSubmit');
$routes->get('register', 'Auth::register');
$routes->post('register/submit', 'Auth::registerSubmit');
$routes->get('forgot-password', 'Auth::forgotPassword');
$routes->post('forgot-password/submit', 'Auth::forgotPasswordSubmit');
$routes->get('logout', 'Auth::logout');

// 2. Protected Investor Routes (Auth Filter)
$routes->group('', ['filter' => 'auth'], function($routes) {
    // Dashboard
    $routes->get('dashboard', 'Dashboard::index');
    
    // Tracker & API Quotes Refresh
    $routes->get('tracker/(:any)', 'Tracker::detail/$1');
    $routes->get('tracker/api/quote/(:any)', 'Tracker::getQuoteApi/$1');
    
    // Watchlists
    $routes->get('watchlist', 'Watchlist::index');
    $routes->post('watchlist/toggle/(:num)', 'Watchlist::toggle/$1');
    
    // Portfolios Buy/Sell Simulator
    $routes->get('portfolio', 'Portfolio::index');
    $routes->post('portfolio/buy', 'Portfolio::buy');
    $routes->get('portfolio/sell/(:num)', 'Portfolio::sell/$1');

    // User Profile View & Edit
    $routes->get('profile', 'Profile::index');
    $routes->post('profile/update', 'Profile::update');
    
    // Sector-Wise News
    $routes->get('news', 'News::index');
    
    // Reports CSV/Excel exports
    $routes->get('reports/portfolio/(:any)', 'Report::portfolio/$1');
});

// 3. Protected Administrator Routes (Admin Filter)
$routes->group('admin', ['filter' => 'admin'], function($routes) {
    $routes->get('', 'Admin::index');
    $routes->get('retrain/(:any)', 'Admin::retrain/$1');
    $routes->post('news/save', 'Admin::saveNews');
    $routes->post('stock/save', 'Admin::saveStock');
});
