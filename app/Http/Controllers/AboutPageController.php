<?php

namespace App\Http\Controllers;

use App\Models\AboutPage;
use Illuminate\Contracts\View\View;

class AboutPageController extends Controller
{
    public function __invoke(): View
    {
        $page = AboutPage::first();

        // Logged-in admins can preview the page before it is published.
        if (! $page || (! $page->is_published && ! auth()->check())) {
            return view('pages.placeholder', [
                'title' => 'About',
                'subtitle' => 'Locally owned, globally trusted, best prices.',
            ]);
        }

        return view('pages.about', compact('page'));
    }
}
