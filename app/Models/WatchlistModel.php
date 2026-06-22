<?php

namespace App\Models;

use CodeIgniter\Model;

class WatchlistModel extends Model
{
    protected $table            = 'watchlists';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $allowedFields    = ['user_id', 'stock_id'];

    public function getUserWatchlist($userId)
    {
        return $this->select('watchlists.id as watchlist_id, watchlists.created_at, stocks.*')
                    ->join('stocks', 'stocks.id = watchlists.stock_id')
                    ->where('user_id', $userId)
                    ->findAll();
    }
}
