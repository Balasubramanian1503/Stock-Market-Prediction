<?php

namespace App\Controllers;

use App\Models\StockModel;
use App\Models\StockHistoryModel;
use App\Models\PredictionModel;
use App\Libraries\YahooFinance;

class Tracker extends BaseController
{
    protected $stockModel;
    protected $stockHistoryModel;
    protected $predictionModel;
    protected $yahooFinance;

    public function __construct()
    {
        $this->stockModel = new StockModel();
        $this->stockHistoryModel = new StockHistoryModel();
        $this->predictionModel = new PredictionModel();
        $this->yahooFinance = new YahooFinance();
        helper(['url']);
    }

    public function detail($symbol)
    {
        // 1. Fetch stock metadata from database
        $stock = $this->stockModel->where('symbol', $symbol)->first();
        if (!$stock) {
            return redirect()->to('/dashboard')->with('error', 'Unsupported stock symbol requested.');
        }

        // 2. Fetch live quote stats from Yahoo Finance
        $quote = $this->yahooFinance->getQuote($symbol);
        
        // 3. Fetch historical data (Limit to 5 years / 1300 records to cover all timeline options)
        $history = $this->stockHistoryModel->where('stock_id', $stock['id'])
                                            ->orderBy('date', 'ASC')
                                            ->findAll();

        // 4. Fetch predictions and metrics
        $predictions = $this->predictionModel->where('stock_id', $stock['id'])
                                            ->orderBy('target_date', 'ASC')
                                            ->findAll();
                                            
        // Separate by model type
        $lrPredictions = [];
        $lstmPredictions = [];
        
        foreach ($predictions as $p) {
            if ($p['model_type'] === 'linear_regression') {
                $lrPredictions[] = $p;
            } elseif ($p['model_type'] === 'lstm') {
                $lstmPredictions[] = $p;
            }
        }

        // Check if stock is in user's watchlist
        $watchlistModel = new \App\Models\WatchlistModel();
        $userId = session()->get('userId');
        $inWatchlist = $watchlistModel->where('user_id', $userId)
                                      ->where('stock_id', $stock['id'])
                                      ->first() !== null;

        // Rule-based Technical Analysis Engine calculations in PHP
        $closes = [];
        $volumes = [];
        foreach ($history as $h) {
            $closes[] = (float)$h['close'];
            $volumes[] = (int)$h['volume'];
        }
        
        $sma20 = null;
        $sma50 = null;
        $rsi = null;
        $macd = null;
        $macdSignal = null;
        $volAvg20 = null;
        $score = 0;
        $rulesBreakdown = [];
        $verdict = 'AVOID';
        $confidence = 0;
        
        $historyCount = count($closes);
        if ($historyCount >= 50) {
            $latestClose = end($closes);
            $latestVolume = end($volumes);
            
            // SMA 20 & SMA 50
            $sma20 = array_sum(array_slice($closes, -20)) / 20;
            $sma50 = array_sum(array_slice($closes, -50)) / 50;
            
            // RSI 14
            $rsi = 50;
            $gains = 0;
            $losses = 0;
            $lastCloses = array_slice($closes, -15);
            for ($i = 1; $i < count($lastCloses); $i++) {
                $diff = $lastCloses[$i] - $lastCloses[$i-1];
                if ($diff > 0) {
                    $gains += $diff;
                } else {
                    $losses += abs($diff);
                }
            }
            $avgGain = $gains / 14;
            $avgLoss = $losses / 14;
            if ($avgLoss == 0) {
                $rsi = 100;
            } else {
                $rs = $avgGain / $avgLoss;
                $rsi = 100 - (100 / (1 + $rs));
            }
            
            // MACD (12, 26, 9 EMA)
            $k12 = 2 / (12 + 1);
            $k26 = 2 / (26 + 1);
            $ema12 = $closes[0];
            $ema26 = $closes[0];
            $macdSeries = [];
            for ($i = 1; $i < $historyCount; $i++) {
                $ema12 = ($closes[$i] * $k12) + ($ema12 * (1 - $k12));
                $ema26 = ($closes[$i] * $k26) + ($ema26 * (1 - $k26));
                $macdSeries[] = $ema12 - $ema26;
            }
            $macd = end($macdSeries);
            
            $k9 = 2 / (9 + 1);
            $macdSignal = $macdSeries[0];
            for ($i = 1; $i < count($macdSeries); $i++) {
                $macdSignal = ($macdSeries[$i] * $k9) + ($macdSignal * (1 - $k9));
            }
            
            // Volume Avg 20
            $volAvg20 = array_sum(array_slice($volumes, -20)) / 20;
            
            // Rule 1: Short-Term Trend
            $isAboveSma20 = $latestClose > $sma20;
            if ($isAboveSma20) {
                $score++;
                $rulesBreakdown[] = [
                    'name' => 'Short-Term Trend Alignment',
                    'status' => 'BULLISH',
                    'detail' => sprintf("Stock price (₹%s) is trading above its 20-day moving average (₹%s), confirming positive short-term momentum.", number_format($latestClose, 2), number_format($sma20, 2)),
                    'value' => 'Price > SMA20'
                ];
            } else {
                $rulesBreakdown[] = [
                    'name' => 'Short-Term Trend Alignment',
                    'status' => 'BEARISH',
                    'detail' => sprintf("Stock price (₹%s) is below its 20-day moving average (₹%s), showing short-term weakness.", number_format($latestClose, 2), number_format($sma20, 2)),
                    'value' => 'Price < SMA20'
                ];
            }
            
            // Rule 2: Medium-Term Trend
            $isGoldenTrend = $sma20 > $sma50;
            if ($isGoldenTrend) {
                $score++;
                $rulesBreakdown[] = [
                    'name' => 'Medium-Term Trend Alignment',
                    'status' => 'BULLISH',
                    'detail' => sprintf("The 20-day moving average (₹%s) is above the 50-day moving average (₹%s), indicating a healthy intermediate-term uptrend.", number_format($sma20, 2), number_format($sma50, 2)),
                    'value' => 'SMA20 > SMA50'
                ];
            } else {
                $rulesBreakdown[] = [
                    'name' => 'Medium-Term Trend Alignment',
                    'status' => 'BEARISH',
                    'detail' => sprintf("The 20-day moving average (₹%s) sits below the 50-day moving average (₹%s), displaying intermediate-term downward pressure.", number_format($sma20, 2), number_format($sma50, 2)),
                    'value' => 'SMA20 < SMA50'
                ];
            }
            
            // Rule 3: RSI
            if ($rsi <= 32) {
                $score++;
                $rulesBreakdown[] = [
                    'name' => 'Relative Strength Index (RSI)',
                    'status' => 'BULLISH (OVERSOLD)',
                    'detail' => sprintf("RSI is extremely low at %s, indicating the stock is oversold. This historically marks accumulation zones and trend reversals.", number_format($rsi, 1)),
                    'value' => sprintf("RSI = %s", number_format($rsi, 1))
                ];
            } elseif ($rsi >= 68) {
                $rulesBreakdown[] = [
                    'name' => 'Relative Strength Index (RSI)',
                    'status' => 'BEARISH (OVERBOUGHT)',
                    'detail' => sprintf("RSI is high at %s, indicating the asset is overbought. Risk of short-term price exhaustion or profit-taking is high.", number_format($rsi, 1)),
                    'value' => sprintf("RSI = %s", number_format($rsi, 1))
                ];
            } else {
                $isHealthyRsi = $rsi > 45;
                if ($isHealthyRsi) {
                    $score++;
                }
                $rulesBreakdown[] = [
                    'name' => 'Relative Strength Index (RSI)',
                    'status' => $isHealthyRsi ? 'BULLISH' : 'NEUTRAL',
                    'detail' => sprintf("RSI is at a stable level of %s. This indicates a sustainable momentum cycle with room to grow.", number_format($rsi, 1)),
                    'value' => 'RSI Healthy Range'
                ];
            }
            
            // Rule 4: MACD Signal crossover
            $isMacdBullish = $macd > $macdSignal;
            if ($isMacdBullish) {
                $score++;
                $rulesBreakdown[] = [
                    'name' => 'Moving Average Convergence Divergence (MACD)',
                    'status' => 'BULLISH',
                    'detail' => sprintf("The MACD line (%s) is trading above its signal line (%s), representing an active buying crossover signal.", number_format($macd, 3), number_format($macdSignal, 3)),
                    'value' => 'MACD > Signal'
                ];
            } else {
                $rulesBreakdown[] = [
                    'name' => 'Moving Average Convergence Divergence (MACD)',
                    'status' => 'BEARISH',
                    'detail' => sprintf("The MACD line (%s) is below its signal line (%s), showing an active selling or momentum slowdown signal.", number_format($macd, 3), number_format($macdSignal, 3)),
                    'value' => 'MACD < Signal'
                ];
            }
            
            // Rule 5: Volume strength
            $isVolumeSupport = $latestVolume > $volAvg20;
            if ($isVolumeSupport) {
                $score++;
                $rulesBreakdown[] = [
                    'name' => 'Volume Support Check',
                    'status' => 'BULLISH',
                    'detail' => sprintf("Current volume (%s) exceeds the 20-day average volume (%s), showing strong participation support.", number_format($latestVolume), number_format($volAvg20)),
                    'value' => 'Volume > Avg'
                ];
            } else {
                $rulesBreakdown[] = [
                    'name' => 'Volume Support Check',
                    'status' => 'NEUTRAL',
                    'detail' => sprintf("Current volume (%s) is below the 20-day average volume (%s), suggesting low retail or institutional interest.", number_format($latestVolume), number_format($volAvg20)),
                    'value' => 'Volume < Avg'
                ];
            }
            
            $verdict = ($score >= 3) ? 'INVEST' : 'AVOID';
            $confidence = intval(($score / 5) * 100);
        }

        return view('tracker/detail', [
            'stock'            => $stock,
            'quote'            => $quote,
            'history'          => $history,
            'lr_preds'         => $lrPredictions,
            'lstm_preds'       => $lstmPredictions,
            'in_watchlist'     => $inWatchlist,
            'verdict'          => $verdict,
            'confidence_score' => $confidence,
            'rules_breakdown'  => $rulesBreakdown,
            'title'            => $stock['company_name'] . ' (' . str_replace('.NS', '', $stock['symbol']) . ')'
        ]);
    }

    /**
     * JSON Endpoint for AJAX auto-refresh
     */
    public function getQuoteApi($symbol)
    {
        $quote = $this->yahooFinance->getQuote($symbol);
        return $this->response->setJSON($quote);
    }
}
