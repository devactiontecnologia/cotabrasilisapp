<?php

namespace App\Http\Controllers;

use App\Models\BoraLaPost;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
class AdminBoraLaPostController extends Controller
{
    public function index(Request $request)
    {
        $type = $request->query('type');
        $query = BoraLaPost::query()->orderBy('sort_order')->orderByDesc('published_at')->orderByDesc('id');
        if ($type && array_key_exists($type, BoraLaPost::TYPES)) {
            $query->where('type', $type);
        }
        $posts = $query->paginate(25)->withQueryString();

        return view('admin.bora-la-posts.index', compact('posts', 'type'));
    }

    public function create()
    {
        $post = new BoraLaPost([
            'is_published' => true,
            'type' => BoraLaPost::TYPE_ATUALIZACAO,
        ]);

        return view('admin.bora-la-posts.create', compact('post'));
    }

    public function store(Request $request)
    {
        $type = $request->validate([
            'type' => 'required|in:'.implode(',', array_keys(BoraLaPost::TYPES)),
        ])['type'];

        $data = $this->validated($request, $type, false);
        $data['created_by'] = Auth::id();
        BoraLaPost::create($data);

        return redirect()->route('admin.bora-la-posts.index', ['type' => $type])
            ->with('success', 'Publicação cadastrada com sucesso.');
    }

    public function edit(BoraLaPost $boraLaPost)
    {
        return view('admin.bora-la-posts.edit', ['post' => $boraLaPost]);
    }

    public function update(Request $request, BoraLaPost $boraLaPost)
    {
        $data = $this->validated($request, $boraLaPost->type, true);
        $boraLaPost->update($data);

        return redirect()->route('admin.bora-la-posts.index', ['type' => $boraLaPost->type])
            ->with('success', 'Publicação atualizada com sucesso.');
    }

    public function destroy(BoraLaPost $boraLaPost)
    {
        $t = $boraLaPost->type;
        $boraLaPost->delete();

        return redirect()->route('admin.bora-la-posts.index', ['type' => $t])
            ->with('success', 'Publicação removida.');
    }

    private function validated(Request $request, string $type, bool $isUpdate): array
    {
        $bodyRule = $type === BoraLaPost::TYPE_ENQUETE
            ? 'nullable|string|max:200000'
            : 'required|string|max:200000';

        $rules = [
            'title' => 'required|string|max:255',
            'body' => $bodyRule,
            'is_published' => 'sometimes|boolean',
            'published_at' => 'nullable|date',
            'sort_order' => 'nullable|integer|min:0|max:999999',
        ];

        $base = $request->validate($rules);
        $base['is_published'] = $request->boolean('is_published');
        $base['sort_order'] = (int) ($base['sort_order'] ?? 0);
        $base['published_at'] = $request->filled('published_at') ? $request->input('published_at') : null;

        $base['payload'] = match ($type) {
            BoraLaPost::TYPE_OFERTA_UNICA => $this->validatePayloadOferta($request),
            BoraLaPost::TYPE_ENQUETE => $this->validatePayloadEnquete($request),
            BoraLaPost::TYPE_ATUALIZACAO,
            BoraLaPost::TYPE_AVISO,
            BoraLaPost::TYPE_DICA => $this->validatePayloadContentExtras($request),
        };

        if (! $isUpdate) {
            $base['type'] = $type;
        }

        return $base;
    }

    private function validatePayloadOferta(Request $request): array
    {
        $v = $request->validate([
            'start_date' => 'required|date',
            'start_time' => 'required|date_format:H:i',
            'end_date' => 'required|date|after_or_equal:start_date',
            'end_time' => 'required|date_format:H:i',
            'content_type' => 'required|in:text,video,both',
            'content_text' => 'nullable|string|max:200000',
            'content_video' => 'nullable|url|max:2000',
        ]);

        if (in_array($v['content_type'], ['text', 'both'], true) && empty($v['content_text'])) {
            $request->validate(['content_text' => 'required|string|max:200000']);
        }
        if (in_array($v['content_type'], ['video', 'both'], true) && empty($v['content_video'])) {
            $request->validate(['content_video' => 'required|url|max:2000']);
        }

        return $v;
    }

    private function validatePayloadEnquete(Request $request): array
    {
        $v = $request->validate([
            'question' => 'required|string|max:2000',
            'options' => 'required|array|min:2',
            'options.*' => 'required|string|max:500',
            'content_type' => 'required|in:text,video,both',
            'content_text' => 'nullable|string|max:200000',
            'content_video' => 'nullable|url|max:2000',
        ]);

        if (in_array($v['content_type'], ['text', 'both'], true) && empty($v['content_text'])) {
            $request->validate(['content_text' => 'required|string|max:200000']);
        }
        if (in_array($v['content_type'], ['video', 'both'], true) && empty($v['content_video'])) {
            $request->validate(['content_video' => 'required|url|max:2000']);
        }

        $opts = array_values(array_filter($v['options'], fn ($x) => trim((string) $x) !== ''));
        if (count($opts) < 2) {
            throw ValidationException::withMessages([
                'options' => 'Informe ao menos duas opções com texto.',
            ]);
        }
        $v['options'] = $opts;

        return $v;
    }

    private function validatePayloadContentExtras(Request $request): array
    {
        $v = $request->validate([
            'content_type' => 'required|in:text,video,both',
            'content_text' => 'nullable|string|max:200000',
            'content_video' => 'nullable|url|max:2000',
        ]);

        if (in_array($v['content_type'], ['text', 'both'], true) && empty($v['content_text'])) {
            $request->validate(['content_text' => 'required|string|max:200000']);
        }
        if (in_array($v['content_type'], ['video', 'both'], true) && empty($v['content_video'])) {
            $request->validate(['content_video' => 'required|url|max:2000']);
        }

        return $v;
    }
}
