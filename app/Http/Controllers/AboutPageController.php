<?php

namespace App\Http\Controllers;

use App\Models\AboutPage;
use Illuminate\Contracts\View\View;

class AboutPageController extends Controller
{
    public function __invoke(): View
    {
        $page = $this->visiblePage();

        if (! $page) {
            return view('pages.placeholder', [
                'title' => 'About',
                'subtitle' => 'Locally owned, globally trusted, best prices.',
            ]);
        }

        return view('pages.about', compact('page'));
    }

    /** The "Our Team" page: the people on the About page, grouped. */
    public function team(): View
    {
        $page = $this->visiblePage();

        if (! $page || ! $page->hasTeam()) {
            return view('pages.placeholder', [
                'title' => 'Our Team',
                'subtitle' => 'Meet the people behind your safari — coming soon.',
            ]);
        }

        return view('pages.team', compact('page'));
    }

    /** Logged-in admins can preview the page before it is published. */
    private function visiblePage(): ?AboutPage
    {
        $page = AboutPage::first();

        return $page && ($page->is_published || auth()->check()) ? $page : null;
    }
}
