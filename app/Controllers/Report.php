<?php

namespace App\Controllers;

use App\Models\PortfolioModel;
use App\Models\StockModel;
use App\Libraries\YahooFinance;

class Report extends BaseController
{
    protected $portfolioModel;
    protected $yahooFinance;

    public function __construct()
    {
        $this->portfolioModel = new PortfolioModel();
        $this->yahooFinance = new YahooFinance();
        helper(['url']);
    }

    public function portfolio($format = 'csv')
    {
        $userId = session()->get('userId');
        $holdings = $this->portfolioModel->getUserPortfolio($userId);
        
        $filename = "portfolio_report_" . date('Ymd_His');
        
        if ($format === 'excel') {
            header('Content-Type: application/vnd.ms-excel; charset=utf-8');
            header('Content-Disposition: attachment; filename="' . $filename . '.xls"');
            $delimiter = "\t";
        } else {
            header('Content-Type: text/csv; charset=utf-8');
            header('Content-Disposition: attachment; filename="' . $filename . '.csv"');
            $delimiter = ",";
        }

        $output = fopen('php://output', 'w');
        
        // Write Column Headers
        fputcsv($output, [
            'Symbol',
            'Company Name',
            'Sector',
            'Quantity',
            'Purchase Price (INR)',
            'Purchase Date',
            'Current Live Price (INR)',
            'Current Valuation (INR)',
            'Net Profit/Loss (INR)',
            'ROI (%)'
        ], $delimiter);

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
            
            $totalInvestment += $purchaseValue;
            $totalCurrentValue += $currentValue;

            fputcsv($output, [
                str_replace('.NS', '', $hold['symbol']),
                $hold['company_name'],
                $hold['sector'],
                $quantity,
                number_format($purchasePrice, 2, '.', ''),
                $hold['purchase_date'],
                number_format($currentPrice, 2, '.', ''),
                number_format($currentValue, 2, '.', ''),
                number_format($profitLoss, 2, '.', ''),
                number_format($roi, 2, '.', '')
            ], $delimiter);
        }

        // Add Summary Row
        fputcsv($output, [], $delimiter); // Empty row
        
        $totalProfitLoss = $totalCurrentValue - $totalInvestment;
        $totalROI = $totalInvestment > 0 ? ($totalProfitLoss / $totalInvestment) * 100 : 0.0;

        fputcsv($output, [
            'TOTAL PORTFOLIO SUMMARY',
            '',
            '',
            '',
            'Total Invested:',
            number_format($totalInvestment, 2, '.', ''),
            'Current Value:',
            number_format($totalCurrentValue, 2, '.', ''),
            'Net Return:',
            number_format($totalProfitLoss, 2, '.', '') . ' (' . number_format($totalROI, 2, '.', '') . '%)'
        ], $delimiter);

        fclose($output);
        exit;
    }
}
