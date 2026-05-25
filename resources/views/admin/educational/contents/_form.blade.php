@php
    $c = $content ?? null;
@endphp

<div class="row g-3">
    <div class="col-md-8">
        <label class="form-label fw-semibold">Título *</label>
        <input type="text" name="title" class="form-control @error('title') is-invalid @enderror" value="{{ old('title', $c->title ?? '') }}" required>
        @error('title')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
    <div class="col-md-4">
        <label class="form-label fw-semibold">Quem pode ver</label>
        <select name="profile_type_required" class="form-select @error('profile_type_required') is-invalid @enderror">
            <option value="">Todos os perfis</option>
            <option value="curioso" @selected(old('profile_type_required', $c->profile_type_required ?? '') === 'curioso')>Curioso</option>
            <option value="inteligente" @selected(old('profile_type_required', $c->profile_type_required ?? '') === 'inteligente')>Inteligente</option>
            <option value="sabio" @selected(old('profile_type_required', $c->profile_type_required ?? '') === 'sabio')>Sábio</option>
        </select>
        @error('profile_type_required')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
    <div class="col-md-4">
        <label class="form-label fw-semibold">Tipo *</label>
        <select name="content_type" class="form-select @error('content_type') is-invalid @enderror" required>
            @foreach(['article' => 'Artigo', 'guide' => 'Guia', 'faq' => 'FAQ', 'tutorial' => 'Tutorial'] as $val => $label)
                <option value="{{ $val }}" @selected(old('content_type', $c->content_type ?? 'article') === $val)>{{ $label }}</option>
            @endforeach
        </select>
        @error('content_type')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
    <div class="col-md-4">
        <label class="form-label fw-semibold">Categoria</label>
        <input type="text" name="category" class="form-control @error('category') is-invalid @enderror" value="{{ old('category', $c->category ?? '') }}" placeholder="Opcional">
        @error('category')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
    <div class="col-md-4">
        <label class="form-label fw-semibold">Ordem</label>
        <input type="number" name="order" class="form-control @error('order') is-invalid @enderror" value="{{ old('order', $c->order ?? 0) }}" min="0">
        @error('order')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
    <div class="col-12">
        <label class="form-label fw-semibold">Resumo (lista)</label>
        <textarea name="description" rows="2" class="form-control @error('description') is-invalid @enderror" placeholder="Texto curto exibido na lista">{{ old('description', $c->description ?? '') }}</textarea>
        @error('description')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
    <div class="col-12">
        <label class="form-label fw-semibold">Corpo do texto (HTML permitido)</label>
        <textarea name="body" rows="12" class="form-control font-monospace small @error('body') is-invalid @enderror" placeholder="Conteúdo completo">{{ old('body', $c->body ?? '') }}</textarea>
        @error('body')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
    <div class="col-12">
        <label class="form-label fw-semibold">Tags (separadas por vírgula)</label>
        <input type="text" name="tags" class="form-control @error('tags') is-invalid @enderror" value="{{ old('tags', isset($c->tags) && is_array($c->tags) ? implode(', ', $c->tags) : '') }}">
        @error('tags')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
    <div class="col-12">
        <div class="form-check">
            <input type="hidden" name="is_active" value="0">
            <input type="checkbox" name="is_active" value="1" class="form-check-input" id="is_active_content" @checked(old('is_active', $c->is_active ?? true))>
            <label class="form-check-label" for="is_active_content">Conteúdo ativo (visível na área do cliente)</label>
        </div>
    </div>
</div>
