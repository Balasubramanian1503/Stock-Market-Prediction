<?php

namespace App\Models;

use CodeIgniter\Model;

class LogModel extends Model
{
    protected $table            = 'logs';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $allowedFields    = ['action', 'details', 'user_id'];

    // Dates
    protected $useTimestamps = false;

    public function getLogs($limit = 100)
    {
        return $this->select('logs.*, users.name as user_name')
                    ->join('users', 'users.id = logs.user_id', 'left')
                    ->orderBy('created_at', 'DESC')
                    ->limit($limit)
                    ->findAll();
    }
}
