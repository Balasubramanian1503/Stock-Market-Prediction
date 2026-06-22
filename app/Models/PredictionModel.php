<?php

namespace App\Models;

use CodeIgniter\Model;

class PredictionModel extends Model
{
    protected $table            = 'predictions';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $allowedFields    = ['stock_id', 'model_type', 'target_date', 'predicted_price', 'mae', 'rmse', 'r2_score', 'confidence_score'];

    public function getPredictionsByStock($stockId)
    {
        return $this->select('predictions.*, stocks.symbol')
                    ->join('stocks', 'stocks.id = predictions.stock_id')
                    ->where('stock_id', $stockId)
                    ->orderBy('target_date', 'ASC')
                    ->findAll();
    }
}
