<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Content;
use App\Models\Media;
use App\Models\User;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('Dashboard', [
            'statistics' => [
                'contents' => [
                    'total'     => Content::count(),
                    'published' => Content::where('cont_cdstat', Content::STATUS_PUBLISHED)->count(),
                    'draft'     => Content::where('cont_cdstat', Content::STATUS_DRAFT)->count(),
                ],
                'media' => [
                    'total'  => Media::count(),
                    'images' => Media::where('medi_cdtype', 'like', 'image/%')->count(),
                    'videos' => Media::where('medi_cdtype', 'like', 'video/%')->count(),
                ],
                'categories' => [
                    'total' => Category::count(),
                ],
                'users' => [
                    'total'  => User::count(),
                    'active' => User::where('user_cdstat', User::STATUS_ACTIVE)->count(),
                ],
            ],
        ]);
    }
}
