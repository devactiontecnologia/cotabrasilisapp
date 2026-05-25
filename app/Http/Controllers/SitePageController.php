<?php

namespace App\Http\Controllers;

use App\Models\SitePage;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class SitePageController extends Controller
{
    public function show(string $slug): RedirectResponse|View
    {
        if ($slug === 'perguntas-frequentes') {
            return redirect()->route('faq', [], 301);
        }

        $page = SitePage::where('slug', $slug)->firstOrFail();

        return view('site.page', compact('page'));
    }
}
