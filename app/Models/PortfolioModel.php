<?php

namespace App\Models;

use CodeIgniter\Model;

class PortfolioModel extends Model
{
    protected $table            = 'portfolios';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $allowedFields    = ['user_id', 'stock_id', 'quantity', 'purchase_price', 'purchase_date'];

    public function getUserPortfolio($userId)
    {
        return $this->select('portfolios.*, stocks.symbol, stocks.company_name, stocks.sector')
                    ->join('stocks', 'stocks.id = portfolios.stock_id')
                    ->where('user_id', $userId)
                    ->findAll();
    }
}
