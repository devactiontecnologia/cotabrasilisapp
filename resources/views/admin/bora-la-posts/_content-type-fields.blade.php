@php
    $p = $p ?? [];
    $ct = old('content_type', $p['content_type'] ?? 'text');
@endphp
<div class="row g-3 mt-1">
    <div class="col-12">
        <label class="form-label small fw-semibold">Tipo de conteúdo complementar *</label>
        <div class="btn-group w-100 flex-wrap" role="group">
            <input type="radio" class="btn-check" name="content_type" id="ct_text" value="text" {{ $ct === 'text' ? 'checked' : '' }} required>
            <label class="btn btn-outline-primary" for="ct_text">Texto</label>
            <input type="radio" class="btn-check" name="content_type" id="ct_video" value="video" {{ $ct === 'video' ? 'checked' : '' }}>
            <label class="btn btn-outline-primary" for="ct_video">Vídeo</label>
            <input type="radio" class="btn-check" name="content_type" id="ct_both" value="both" {{ $ct === 'both' ? 'checked' : '' }}>
            <label class="btn btn-outline-primary" for="ct_both">Texto + vídeo</label>
        </div>
    </div>
    <div class="col-12" id="admin_ct_text_wrap">
        <label class="form-label small" for="content_text">Texto complementar</label>
        <textarea name="content_text" id="content_text" class="form-control" rows="4">{{ old('content_text', $p['content_text'] ?? '') }}</textarea>
    </div>
    <div class="col-12" id="admin_ct_video_wrap">
        <label class="form-label small" for="content_video">URL do vídeo</label>
        <input type="url" name="content_video" id="content_video" class="form-control" value="{{ old('content_video', $p['content_video'] ?? '') }}" placeholder="https://...">
    </div>
</div>
