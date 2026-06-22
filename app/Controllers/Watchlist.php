<?php

namespace App\Controllers;

use App\Models\WatchlistModel;
use App\Models\StockModel;
use App\Models\PredictionModel;
use App\Libraries\YahooFinance;

class Watchlist extends BaseController
{
    protected $watchlistModel;
    protected $stockModel;
    protected $predictionModel;
    protected $yahooFinance;

    public function __construct()
    {
        $this->watchlistModel = new WatchlistModel();
        $this->stockModel = new StockModel();
        $this->predictionModel = new PredictionModel();
        $this->yahooFinance = new YahooFinance();
        helper(['url']);
    }

    public function index()
    {
        $userId = session()->get('userId');
        
        // 1. Fetch user's watchlist stocks
        $favorites = $this->watchlistModel->getUserWatchlist($userId);
        
        $watchlistData = [];
        foreach ($favorites as $fav) {
            $symbol = $fav['symbol'];
            $quote = $this->yahooFinance->getQuote($symbol);
            
            // Fetch next-day price forecast for both models if available
            $forecastQuery = $this->predictionModel->where('stock_id', $fav['id'])
                                                   ->orderBy('target_date', 'ASC')
                                                   ->findAll();
                                                   
            $nextDayLR = 0.0;
            $nextDayLSTM = 0.0;
            
            foreach ($forecastQuery as $f) {
                // Tomorrow is the first index (index 0) in predictions
                if ($f['model_type'] === 'linear_regression' && $nextDayLR == 0.0) {
                    $nextDayLR = $f['predicted_price'];
                } elseif ($f['model_type'] === 'lstm' && $nextDayLSTM == 0.0) {
                    $nextDayLSTM = $f['predicted_price'];
                }
            }
            
            $fav['price'] = $quote['price'];
            $fav['change_percent'] = $quote['change_percent'];
            $fav['predicted_lr'] = $nextDayLR;
            $fav['predicted_lstm'] = $nextDayLSTM;
            
            $watchlistData[] = $fav;
        }

        return view('watchlist', [
            'watchlist' => $watchlistData,
            'title'     => 'My Watchlist'
        ]);
    }

    /**
     * AJAX Endpoint to toggle watchlist
     */
    public function toggle($stockId)
    {
        if (!$this->request->isAJAX()) {
            return $this->response->setStatusCode(403)->setJSON(['error' => 'Forbidden']);
        }

        $userId = session()->get('userId');
        
        // Verify stock exists
        $stock = $this->stockModel->find($stockId);
        if (!$stock) {
            return $this->response->setJSON(['error' => 'Invalid stock ID']);
        }

        $existing = $this->watchlistModel->where('user_id', $userId)
                                         ->where('stock_id', $stockId)
                                         ->first();

        if ($existing) {
            // Remove
            $this->watchlistModel->delete($existing['id']);
            return $this->response->setJSON(['status' => 'removed']);
        } else {
            // Add
            $this->watchlistModel->save([
                'user_id'  => $userId,
                'stock_id' => $stockId
            ]);
            return $this->response->setJSON(['status' => 'added']);
        }
    }
}
