<?php

namespace App\Controllers;

use App\Models\NewsModel;

class News extends BaseController
{
    protected $newsModel;

    public function __construct()
    {
        $this->newsModel = new NewsModel();
        helper(['url']);
    }

    public function index()
    {
        $category = $this->request->getGet('category');
        
        if (!empty($category)) {
            $news = $this->newsModel->where('category', $category)
                                    ->orderBy('published_at', 'DESC')
                                    ->findAll();
        } else {
            $news = $this->newsModel->orderBy('published_at', 'DESC')
                                    ->findAll();
        }

        $categories = ['NIFTY', 'Banking', 'IT Sector', 'Energy Sector', 'Auto Sector'];

        return view('news', [
            'news'            => $news,
            'categories'      => $categories,
            'active_category' => $category,
            'title'           => 'Market News'
        ]);
    }
}
