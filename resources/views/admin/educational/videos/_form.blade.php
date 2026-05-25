@php
    $v = $video ?? null;
@endphp

<div class="row g-3">
    <div class="col-md-8">
        <label class="form-label fw-semibold">Título *</label>
        <input type="text" name="title" class="form-control @error('title') is-invalid @enderror" value="{{ old('title', $v->title ?? '') }}" required>
        @error('title')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
    <div class="col-md-4">
        <label class="form-label fw-semibold">Quem pode ver</label>
        <select name="profile_type_required" class="form-select @error('profile_type_required') is-invalid @enderror">
            <option value="">Todos os perfis</option>
            <option value="curioso" @selected(old('profile_type_required', $v->profile_type_required ?? '') === 'curioso')>Curioso</option>
            <option value="inteligente" @selected(old('profile_type_required', $v->profile_type_required ?? '') === 'inteligente')>Inteligente</option>
            <option value="sabio" @selected(old('profile_type_required', $v->profile_type_required ?? '') === 'sabio')>Sábio</option>
        </select>
        @error('profile_type_required')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
    <div class="col-12">
        <label class="form-label fw-semibold">URL do vídeo *</label>
        <input type="url" name="video_url" class="form-control @error('video_url') is-invalid @enderror" value="{{ old('video_url', $v->video_url ?? '') }}" placeholder="https://www.youtube.com/watch?v=..." required>
        @error('video_url')<div class="invalid-feedback">{{ $message }}</div>@enderror
        <div class="form-text">YouTube ou link direto; na área do cliente o YouTube é exibido em player embutido quando possível.</div>
    </div>
    <div class="col-md-6">
        <label class="form-label fw-semibold">Miniatura (URL)</label>
        <input type="url" name="thumbnail_url" class="form-control @error('thumbnail_url') is-invalid @enderror" value="{{ old('thumbnail_url', $v->thumbnail_url ?? '') }}">
        @error('thumbnail_url')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
    <div class="col-md-6">
        <label class="form-label fw-semibold">Duração (segundos)</label>
        <input type="number" name="duration" class="form-control @error('duration') is-invalid @enderror" value="{{ old('duration', $v->duration ?? '') }}" min="0">
        @error('duration')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
    <div class="col-md-6">
        <label class="form-label fw-semibold">Vincular a texto (opcional)</label>
        <select name="educational_content_id" class="form-select @error('educational_content_id') is-invalid @enderror">
            <option value="">— Nenhum —</option>
            @foreach($contents as $c)
                <option value="{{ $c->id }}" @selected(old('educational_content_id', $v->educational_content_id ?? '') == $c->id)>{{ $c->title }}</option>
            @endforeach
        </select>
        @error('educational_content_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
    <div class="col-md-6">
        <label class="form-label fw-semibold">Categoria</label>
        <input type="text" name="category" class="form-control @error('category') is-invalid @enderror" value="{{ old('category', $v->category ?? '') }}">
        @error('category')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
    <div class="col-md-4">
        <label class="form-label fw-semibold">Ordem</label>
        <input type="number" name="order" class="form-control @error('order') is-invalid @enderror" value="{{ old('order', $v->order ?? 0) }}" min="0">
        @error('order')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
    <div class="col-12">
        <label class="form-label fw-semibold">Descrição</label>
        <textarea name="description" rows="3" class="form-control @error('description') is-invalid @enderror">{{ old('description', $v->description ?? '') }}</textarea>
        @error('description')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
    <div class="col-12">
        <label class="form-label fw-semibold">Tags (separadas por vírgula)</label>
        <input type="text" name="tags" class="form-control @error('tags') is-invalid @enderror" value="{{ old('tags', isset($v->tags) && is_array($v->tags) ? implode(', ', $v->tags) : '') }}">
        @error('tags')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
    <div class="col-12">
        <div class="form-check">
            <input type="hidden" name="is_active" value="0">
            <input type="checkbox" name="is_active" value="1" class="form-check-input" id="is_active_video" @checked(old('is_active', $v->is_active ?? true))>
            <label class="form-check-label" for="is_active_video">Vídeo ativo (visível na área do cliente)</label>
        </div>
    </div>
</div>
