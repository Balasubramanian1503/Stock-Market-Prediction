<?php

namespace App\Models;

use CodeIgniter\Model;

class StockHistoryModel extends Model
{
    protected $table            = 'stock_history';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $allowedFields    = ['stock_id', 'date', 'open', 'high', 'low', 'close', 'volume'];

    // Retrieve full history with stock symbol joined
    public function getHistoryWithSymbol($stockId, $limit = 365)
    {
        return $this->select('stock_history.*, stocks.symbol, stocks.company_name')
                    ->join('stocks', 'stocks.id = stock_history.stock_id')
                    ->where('stock_id', $stockId)
                    ->orderBy('date', 'ASC')
                    ->limit($limit)
                    ->findAll();
    }
}
