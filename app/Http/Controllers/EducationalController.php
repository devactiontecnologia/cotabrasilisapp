<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\EducationalContent;
use App\Models\EducationalVideo;
use App\Models\VideoComment;

class EducationalController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $profileType = $user->profile->profile_type ?? null;
        
        $query = EducationalContent::active();
        
        if ($profileType) {
            $query->where(function($q) use ($profileType) {
                $q->whereNull('profile_type_required')
                  ->orWhere('profile_type_required', $profileType);
            });
        } else {
            $query->whereNull('profile_type_required');
        }
        
        $contents = $query->orderBy('order')->get();

        return view('educational.index', compact('contents'));
    }

    public function showContent(EducationalContent $content)
    {
        $user = Auth::user();

        if (!$content->canUserAccess($user)) {
            return redirect()->route('educational.index')
                ->with('error', 'Você não tem acesso a este conteúdo.');
        }

        return view('educational.content-show', compact('content'));
    }

    public function videos(Request $request)
    {
        $user = Auth::user();
        $profileType = $user->profile->profile_type ?? null;
        
        $query = EducationalVideo::active();
        
        if ($profileType) {
            $query->where(function($q) use ($profileType) {
                $q->whereNull('profile_type_required')
                  ->orWhere('profile_type_required', $profileType);
            });
        } else {
            $query->whereNull('profile_type_required');
        }

        if ($request->filled('category')) {
            $query->where('category', $request->category);
        }

        $videos = $query->orderBy('order')->paginate(12);

        return view('educational.videos', compact('videos'));
    }

    public function show(EducationalVideo $video)
    {
        $user = Auth::user();
        
        if (!$video->canUserAccess($user)) {
            return redirect()->route('educational.index')
                ->with('error', 'Você não tem acesso a este conteúdo.');
        }

        $video->load(['comments.user', 'comments.replies.user']);
        
        return view('educational.video-show', compact('video'));
    }

    public function comment(Request $request, EducationalVideo $video)
    {
        $user = Auth::user();
        
        $request->validate([
            'comment' => 'required|string|max:1000',
            'parent_id' => 'nullable|exists:video_comments,id',
        ]);

        VideoComment::create([
            'educational_video_id' => $video->id,
            'user_id' => $user->id,
            'comment' => $request->comment,
            'parent_id' => $request->parent_id,
            'is_approved' => true,
        ]);

        return redirect()->back()->with('success', 'Comentário adicionado com sucesso!');
    }

    public function recordView(Request $request, EducationalVideo $video)
    {
        $user = Auth::user();
        
        $durationWatched = $request->input('duration', 0);
        $completed = $request->input('completed', false);

        $video->recordView($user, $durationWatched, $completed);

        return response()->json(['success' => true]);
    }
}
