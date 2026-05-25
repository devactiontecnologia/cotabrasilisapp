<?php

namespace App\Http\Controllers;

use App\Models\EducationalContent;
use App\Models\EducationalVideo;
use Illuminate\Http\Request;

class AdminEducationalVideoController extends Controller
{
    public function index()
    {
        $videos = EducationalVideo::query()
            ->with('educationalContent')
            ->orderBy('order')
            ->orderByDesc('id')
            ->paginate(20);

        return view('admin.educational.videos.index', compact('videos'));
    }

    public function create()
    {
        $contents = EducationalContent::query()
            ->orderBy('title')
            ->get(['id', 'title']);

        return view('admin.educational.videos.create', compact('contents'));
    }

    public function store(Request $request)
    {
        $data = $this->validatedVideo($request);
        EducationalVideo::create($data);

        return redirect()->route('admin.educational.videos.index')
            ->with('success', 'Vídeo educativo criado com sucesso.');
    }

    public function edit(EducationalVideo $video)
    {
        $contents = EducationalContent::query()
            ->orderBy('title')
            ->get(['id', 'title']);

        return view('admin.educational.videos.edit', compact('video', 'contents'));
    }

    public function update(Request $request, EducationalVideo $video)
    {
        $video->update($this->validatedVideo($request));

        return redirect()->route('admin.educational.videos.index')
            ->with('success', 'Vídeo educativo atualizado com sucesso.');
    }

    public function destroy(EducationalVideo $video)
    {
        $video->delete();

        return redirect()->route('admin.educational.videos.index')
            ->with('success', 'Vídeo removido.');
    }

    private function validatedVideo(Request $request): array
    {
        $validated = $request->validate([
            'educational_content_id' => 'nullable|exists:educational_contents,id',
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'video_url' => 'required|url|max:2000',
            'thumbnail_url' => 'nullable|url|max:2000',
            'duration' => 'nullable|integer|min:0|max:86400',
            'profile_type_required' => 'nullable|in:curioso,inteligente,sabio',
            'category' => 'nullable|string|max:255',
            'tags' => 'nullable|string|max:1000',
            'is_active' => 'sometimes|boolean',
            'order' => 'nullable|integer|min:0|max:99999',
        ]);

        $validated['educational_content_id'] = $request->filled('educational_content_id')
            ? (int) $request->educational_content_id
            : null;
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
