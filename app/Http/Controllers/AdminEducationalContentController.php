<?php

namespace App\Http\Controllers;

use App\Models\EducationalContent;
use Illuminate\Http\Request;

class AdminEducationalContentController extends Controller
{
    public function index()
    {
        $contents = EducationalContent::query()
            ->orderBy('order')
            ->orderByDesc('id')
            ->paginate(20);

        return view('admin.educational.contents.index', compact('contents'));
    }

    public function create()
    {
        return view('admin.educational.contents.create');
    }

    public function store(Request $request)
    {
        $data = $this->validatedContent($request);
        EducationalContent::create($data);

        return redirect()->route('admin.educational.contents.index')
            ->with('success', 'Conteúdo em texto criado com sucesso.');
    }

    public function edit(EducationalContent $content)
    {
        return view('admin.educational.contents.edit', ['content' => $content]);
    }

    public function update(Request $request, EducationalContent $content)
    {
        $content->update($this->validatedContent($request));

        return redirect()->route('admin.educational.contents.index')
            ->with('success', 'Conteúdo em texto atualizado com sucesso.');
    }

    public function destroy(EducationalContent $content)
    {
        $content->delete();

        return redirect()->route('admin.educational.contents.index')
            ->with('success', 'Conteúdo removido.');
    }

    private function validatedContent(Request $request): array
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'body' => 'nullable|string',
            'content_type' => 'required|in:article,guide,faq,tutorial',
            'profile_type_required' => 'nullable|in:curioso,inteligente,sabio',
            'category' => 'nullable|string|max:255',
            'tags' => 'nullable|string|max:1000',
            'is_active' => 'sometimes|boolean',
            'order' => 'nullable|integer|min:0|max:99999',
        ]);

        $validated['profile_type_required'] = $request->filled('profile_type_required')
            ? $request->profile_type_required
            : null;
        $validated['is_active'] = $request->boolean('is_active');
        $validated['order'] = (int) ($validated['order'] ?? 0);

        if (!empty($validated['tags'])) {
            $validated['tags'] = array_values(array_filter(array_map('trim', explode(',', $validated['tags']))));
        } else {
            $validated['tags'] = null;
        }

        return $validated;
    }
}
