<?php

namespace App\Http\Controllers;

use App\Models\SitePage;
use Illuminate\Http\Request;

class AdminSiteInformationController extends Controller
{
    public function index()
    {
        $pages = SitePage::query()
            ->where('slug', '!=', 'perguntas-frequentes')
            ->orderBy('category')
            ->orderBy('sort_order')
            ->orderBy('title')
            ->get();
        $pagesByCategory = $pages->groupBy('category');

        return view('admin.site-information.index', compact('pages', 'pagesByCategory'));
    }

    public function edit(SitePage $sitePage)
    {
        return view('admin.site-information.edit', compact('sitePage'));
    }

    public function update(Request $request, SitePage $sitePage)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'body' => 'nullable|string',
        ]);

        $sitePage->update($validated);

        return redirect()
            ->route('admin.site-information.edit', $sitePage)
            ->with('success', 'Conteúdo de “'.$sitePage->title.'” atualizado.');
    }
}
