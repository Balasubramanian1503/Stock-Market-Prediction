<?php

namespace App\Controllers;

use App\Models\UserModel;
use App\Models\StockModel;
use App\Models\NewsModel;
use App\Models\LogModel;
use App\Models\PredictionModel;

class Admin extends BaseController
{
    protected $userModel;
    protected $stockModel;
    protected $newsModel;
    protected $logModel;
    protected $predictionModel;

    public function __construct()
    {
        $this->userModel = new UserModel();
        $this->stockModel = new StockModel();
        $this->newsModel = new NewsModel();
        $this->logModel = new LogModel();
        $this->predictionModel = new PredictionModel();
        helper(['form', 'url']);
    }

    public function index()
    {
        // Fetch counters
        $totalUsers = $this->userModel->countAllResults();
        $totalStocks = $this->stockModel->countAllResults();
        $totalPredictions = $this->predictionModel->countAllResults();

        // Fetch lists
        $stocks = $this->stockModel->findAll();
        $logs = $this->logModel->getLogs(50);
        $users = $this->userModel->findAll();

        return view('admin/dashboard', [
            'total_users'       => $totalUsers,
            'total_stocks'      => $totalStocks,
            'total_predictions' => $totalPredictions,
            'stocks'            => $stocks,
            'logs'              => $logs,
            'users'             => $users,
            'title'             => 'Admin Control Panel'
        ]);
    }

    /**
     * Retrain ML models by executing Python script
     */
    public function retrain($symbol = 'all')
    {
        $userId = session()->get('userId');
        
        // Construct the CLI command safely
        if ($symbol === 'all') {
            $cmd = "python ml_module/predict.py --all 2>&1";
            $message = "All stock prediction models retrained successfully!";
        } else {
            // Validate stock symbol exists
            $stock = $this->stockModel->where('symbol', $symbol)->first();
            if (!$stock) {
                return redirect()->to('/admin')->with('error', 'Requested stock symbol is not supported.');
            }
            $symbolEscaped = escapeshellarg($symbol);
            $cmd = "python ml_module/predict.py --stock {$symbolEscaped} 2>&1";
            $message = "Prediction models for {$symbol} retrained successfully!";
        }

        // Execute the script and capture console output
        echo "Executing: " . $cmd . "<br>Please wait...<br>";
        $output = shell_exec($cmd);

        // Record admin task in log
        $this->logModel->save([
            'action'  => 'ADMIN_RETRAIN',
            'details' => "Triggered ML model retraining for symbol: " . ($symbol === 'all' ? 'ALL' : $symbol),
            'user_id' => $userId
        ]);

        // Format and return output
        $session = session();
        $session->setFlashdata('success', $message);
        $session->setFlashdata('cmd_output', $output);

        return redirect()->to('/admin');
    }

    /**
     * Add new financial news
     */
    public function saveNews()
    {
        $userId = session()->get('userId');
        
        $rules = [
            'title'    => 'required|min_length[5]|max_length[255]',
            'summary'  => 'required',
            'content'  => 'required',
            'category' => 'required'
        ];

        if (!$this->validate($rules)) {
            return redirect()->to('/admin')->with('error', 'Failed to publish news. Please verify input fields.');
        }

        $this->newsModel->save([
            'title'    => $this->request->getPost('title'),
            'summary'  => $this->request->getPost('summary'),
            'content'  => $this->request->getPost('content'),
            'category' => $this->request->getPost('category'),
            'source'   => 'Admin Desk'
        ]);

        $this->logModel->save([
            'action'  => 'NEWS_PUBLISH',
            'details' => "Published news headline: " . substr($this->request->getPost('title'), 0, 50) . "...",
            'user_id' => $userId
        ]);

        return redirect()->to('/admin')->with('success', 'News article published successfully!');
    }

    /**
     * Add new stock symbol
     */
    public function saveStock()
    {
        $userId = session()->get('userId');
        
        $rules = [
            'symbol'       => 'required|is_unique[stocks.symbol]',
            'company_name' => 'required',
            'sector'       => 'required'
        ];

        if (!$this->validate($rules)) {
            return redirect()->to('/admin')->with('error', 'Failed to add stock. Symbol must be unique.');
        }

        $this->stockModel->save([
            'symbol'       => strtoupper($this->request->getPost('symbol')),
            'company_name' => $this->request->getPost('company_name'),
            'sector'       => $this->request->getPost('sector')
        ]);

        $this->logModel->save([
            'action'  => 'STOCK_ADD',
            'details' => "Added new supported asset: " . strtoupper($this->request->getPost('symbol')),
            'user_id' => $userId
        ]);

        return redirect()->to('/admin')->with('success', 'Stock symbol added successfully! Please run data collection and model training for this stock.');
    }
}
