<?php

namespace App\Controllers;

use App\Models\StockModel;
use App\Models\NewsModel;
use App\Libraries\YahooFinance;

class Dashboard extends BaseController
{
    protected $stockModel;
    protected $newsModel;
    protected $yahooFinance;

    public function __construct()
    {
        $this->stockModel = new StockModel();
        $this->newsModel = new NewsModel();
        $this->yahooFinance = new YahooFinance();
        helper(['url']);
    }

    public function index()
    {
        // 1. Fetch Index Quotes
        $nifty = $this->yahooFinance->getQuote('^NSEI');
        $sensex = $this->yahooFinance->getQuote('^BSESN');
        $banknifty = $this->yahooFinance->getQuote('^NSEBANK');
        
        $indices = [
            'nifty'     => $nifty,
            'sensex'    => $sensex,
            'banknifty' => $banknifty
        ];

        // 2. Fetch all supported stocks
        $supportedStocks = $this->stockModel->findAll();
        
        $stocksData = [];
        $sectorPerformance = [];
        
        foreach ($supportedStocks as $stock) {
            $quote = $this->yahooFinance->getQuote($stock['symbol']);
            // Merge database sector metadata with live quote
            $quote['company_name'] = $stock['company_name'];
            $quote['sector'] = $stock['sector'];
            $quote['id'] = $stock['id'];
            
            $stocksData[] = $quote;
            
            // Group by sector
            $sector = $stock['sector'];
            if (!isset($sectorPerformance[$sector])) {
                $sectorPerformance[$sector] = [
                    'name' => $sector,
                    'total_change' => 0.0,
                    'count' => 0
                ];
            }
            $sectorPerformance[$sector]['total_change'] += $quote['change_percent'];
            $sectorPerformance[$sector]['count']++;
        }

        // Calculate average sector performance
        foreach ($sectorPerformance as $sector => $data) {
            $sectorPerformance[$sector]['avg_change'] = $data['total_change'] / $data['count'];
        }

        // 3. Compute Top Gainers and Losers
        // Sort by change_percent descending
        usort($stocksData, function($a, $b) {
            return $b['change_percent'] <=> $a['change_percent'];
        });
        
        $gainers = array_slice($stocksData, 0, 3);
        
        // Filter out only positive change for gainers (if all are negative, handle cleanly)
        $gainers = array_filter($gainers, function($item) {
            return $item['change_percent'] > 0;
        });
        
        // Sort by change_percent ascending for losers
        usort($stocksData, function($a, $b) {
            return $a['change_percent'] <=> $b['change_percent'];
        });
        
        $losers = array_slice($stocksData, 0, 3);
        
        $losers = array_filter($losers, function($item) {
            return $item['change_percent'] < 0;
        });

        // 4. Fetch Latest News
        $news = $this->newsModel->orderBy('published_at', 'DESC')->limit(5)->findAll();

        // 5. Render dashboard view
        return view('dashboard', [
            'indices'           => $indices,
            'stocks'            => $stocksData,
            'gainers'           => $gainers,
            'losers'            => $losers,
            'sectors'           => $sectorPerformance,
            'news'              => $news,
            'title'             => 'Dashboard'
        ]);
    }
}
