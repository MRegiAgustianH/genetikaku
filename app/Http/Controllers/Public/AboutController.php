<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\AboutPage;
use Inertia\Inertia;
use Inertia\Response;

class AboutController extends Controller
{
    
    public function show(): Response
    {
        $about = AboutPage::query()->first();

        return Inertia::render('public/about', [
            'about' => $about ? [
                'title' => $about->title,
                'content' => $about->content,
                'image_url' => $about->image_url,
            ] : null,
        ]);
    }
}
