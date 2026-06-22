<?php

namespace App\Controllers;

use App\Models\PortfolioModel;
use App\Models\StockModel;
use App\Models\LogModel;
use App\Libraries\YahooFinance;

class Portfolio extends BaseController
{
    protected $portfolioModel;
    protected $stockModel;
    protected $logModel;
    protected $yahooFinance;

    public function __construct()
    {
        $this->portfolioModel = new PortfolioModel();
        $this->stockModel = new StockModel();
        $this->logModel = new LogModel();
        $this->yahooFinance = new YahooFinance();
        helper(['form', 'url']);
    }

    public function index()
    {
        $userId = session()->get('userId');
        
        // 1. Fetch user's holdings
        $holdings = $this->portfolioModel->getUserPortfolio($userId);
        
        $portfolioData = [];
        $totalInvestment = 0.0;
        $totalCurrentValue = 0.0;
        
        foreach ($holdings as $hold) {
            $quote = $this->yahooFinance->getQuote($hold['symbol']);
            
            $currentPrice = $quote['price'];
            $quantity = intval($hold['quantity']);
            $purchasePrice = floatval($hold['purchase_price']);
            
            $purchaseValue = $quantity * $purchasePrice;
            $currentValue = $quantity * $currentPrice;
            $profitLoss = $currentValue - $purchaseValue;
            $roi = $purchaseValue > 0 ? ($profitLoss / $purchaseValue) * 100 : 0.0;
            
            $hold['current_price'] = $currentPrice;
            $hold['purchase_value'] = $purchaseValue;
            $hold['current_value'] = $currentValue;
            $hold['profit_loss'] = $profitLoss;
            $hold['roi'] = $roi;
            
            $totalInvestment += $purchaseValue;
            $totalCurrentValue += $currentValue;
            
            $portfolioData[] = $hold;
        }

        $totalProfitLoss = $totalCurrentValue - $totalInvestment;
        $totalROI = $totalInvestment > 0 ? ($totalProfitLoss / $totalInvestment) * 100 : 0.0;

        $summary = [
            'total_investment'    => $totalInvestment,
            'total_current_value' => $totalCurrentValue,
            'total_profit_loss'   => $totalProfitLoss,
            'total_roi'           => $totalROI
        ];

        // Fetch all supported stocks to populate the dropdown in the buy form
        $stocks = $this->stockModel->findAll();

        return view('portfolio', [
            'portfolio' => $portfolioData,
            'summary'   => $summary,
            'stocks'    => $stocks,
            'title'     => 'Portfolio Simulator'
        ]);
    }

    public function buy()
    {
        $userId = session()->get('userId');
        
        $rules = [
            'stock_id'       => 'required|is_not_unique[stocks.id]',
            'quantity'       => 'required|integer|greater_than[0]',
            'purchase_price' => 'required|decimal|greater_than[0]',
            'purchase_date'  => 'required|valid_date[Y-m-d]'
        ];

        if (!$this->validate($rules)) {
            return redirect()->to('/portfolio')->withInput()->with('error', 'Invalid transaction inputs. Please check your data.');
        }

        $stockId = $this->request->getPost('stock_id');
        $quantity = $this->request->getPost('quantity');
        $purchasePrice = $this->request->getPost('purchase_price');
        $purchaseDate = $this->request->getPost('purchase_date');

        // Fetch stock metadata
        $stock = $this->stockModel->find($stockId);

        $this->portfolioModel->save([
            'user_id'        => $userId,
            'stock_id'       => $stockId,
            'quantity'       => $quantity,
            'purchase_price' => $purchasePrice,
            'purchase_date'  => $purchaseDate
        ]);

        // Record logs
        $this->logModel->save([
            'action'  => 'PORTFOLIO_BUY',
            'details' => "Bought virtual asset: {$stock['company_name']} ({$stock['symbol']}) | Quantity: {$quantity} | Price: ₹" . number_format($purchasePrice, 2),
            'user_id' => $userId
        ]);

        return redirect()->to('/portfolio')->with('success', 'Virtual asset transaction added successfully!');
    }

    public function sell($id)
    {
        $userId = session()->get('userId');
        
        // Find holding record and ensure it belongs to the user
        $holding = $this->portfolioModel->where('id', $id)->where('user_id', $userId)->first();
        if (!$holding) {
            return redirect()->to('/portfolio')->with('error', 'Holding transaction record not found.');
        }

        $stock = $this->stockModel->find($holding['stock_id']);
        
        // Delete transaction (simulates liquidating the virtual asset)
        $this->portfolioModel->delete($id);

        // Record logs
        $this->logModel->save([
            'action'  => 'PORTFOLIO_SELL',
            'details' => "Liquidated virtual asset: {$stock['company_name']} ({$stock['symbol']}) | Quantity: {$holding['quantity']} | Purchase Price: ₹" . number_format($holding['purchase_price'], 2),
            'user_id' => $userId
        ]);

        return redirect()->to('/portfolio')->with('success', 'Virtual holding liquidated successfully!');
    }
}
