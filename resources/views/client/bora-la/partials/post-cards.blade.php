@php
    /** @var \Illuminate\Support\Collection<int, \App\Models\BoraLaPost>|array<\App\Models\BoraLaPost> $posts */
    $posts = $posts ?? collect();
    if (!($posts instanceof \Illuminate\Support\Collection)) {
        $posts = collect($posts);
    }
    $youtubeEmbed = function (?string $url): ?string {
        if (! $url) {
            return null;
        }
        if (preg_match('~(youtube\.com/watch\?v=|youtu\.be/)([a-zA-Z0-9_-]{6,})~', $url, $m)) {
            return 'https://www.youtube.com/embed/'.$m[2];
        }

        return null;
    };
@endphp

@if($posts->isEmpty())
    <div class="text-center py-5">
        <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
        <p class="text-muted mb-0">{{ $emptyMessage ?? 'Nenhuma publicação no momento.' }}</p>
    </div>
@else
    <div class="vstack gap-3">
        @foreach($posts as $post)
            @php $p = is_array($post->payload) ? $post->payload : []; @endphp
            <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
                <div class="card-body p-4">
                    <div class="d-flex flex-wrap justify-content-between gap-2 align-items-start mb-2">
                        <h5 class="fw-bold mb-0">{{ $post->title }}</h5>
                        <span class="badge bg-secondary-subtle text-secondary text-uppercase small">
                            {{ \App\Models\BoraLaPost::TYPES[$post->type] ?? $post->type }}
                        </span>
                    </div>
                    @if($post->published_at)
                        <p class="text-muted small mb-3">
                            <i class="far fa-clock me-1"></i>{{ $post->published_at->timezone(config('app.timezone'))->format('d/m/Y H:i') }}
                        </p>
                    @endif

                    @if($post->type === \App\Models\BoraLaPost::TYPE_OFERTA_UNICA)
                        <p class="small text-muted mb-2">
                            <i class="fas fa-calendar-alt me-1 text-warning"></i>
                            De {{ ($p['start_date'] ?? '') }} {{ $p['start_time'] ?? '' }}
                            até {{ ($p['end_date'] ?? '') }} {{ $p['end_time'] ?? '' }}
                        </p>
                    @endif

                    @if($post->type === \App\Models\BoraLaPost::TYPE_ENQUETE && !empty($p['question']))
                        <p class="fw-semibold mb-2">{{ $p['question'] }}</p>
                        @if(!empty($p['options']) && is_array($p['options']))
                            <ul class="list-group list-group-flush rounded-3 border mb-3">
                                @foreach($p['options'] as $opt)
                                    <li class="list-group-item small">{{ $opt }}</li>
                                @endforeach
                            </ul>
                        @endif
                    @endif

                    @if(filled($post->body))
                        <div class="post-body text-muted mb-3">{!! $post->displayBodyHtml() !!}</div>
                    @endif

                    @php
                        $ctype = $p['content_type'] ?? 'text';
                        $ctext = $p['content_text'] ?? null;
                        $cvideo = $p['content_video'] ?? null;
                        $embed = $youtubeEmbed($cvideo);
                    @endphp
                    @if(in_array($ctype, ['text', 'both'], true) && $ctext)
                        <div class="border-start border-3 border-primary ps-3 mb-3 small">{!! nl2br(e($ctext)) !!}</div>
                    @endif
                    @if(in_array($ctype, ['video', 'both'], true) && $cvideo)
                        @if($embed)
                            <div class="ratio ratio-16x9 rounded-3 overflow-hidden mb-2">
                                <iframe src="{{ $embed }}" title="Vídeo" allowfullscreen loading="lazy"></iframe>
                            </div>
                        @else
                            <p class="mb-0"><a href="{{ $cvideo }}" target="_blank" rel="noopener" class="btn btn-sm btn-outline-primary"><i class="fas fa-play me-1"></i>Abrir vídeo</a></p>
                        @endif
                    @endif
                </div>
            </div>
        @endforeach
    </div>
@endif
