@php
    $f = $faq ?? null;
    $answerValue = old('answer', $f->answer ?? '');
    // Evita que um </textarea> no HTML salvo quebre o markup da página
    $answerValue = str_ireplace('</textarea>', '<\/textarea>', $answerValue);
@endphp

<div class="row g-3">
    <div class="col-md-8">
        <label class="form-label fw-semibold">Pergunta *</label>
        <input type="text" name="question" class="form-control @error('question') is-invalid @enderror" value="{{ old('question', $f->question ?? '') }}" required maxlength="500">
        @error('question')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
    <div class="col-md-2">
        <label class="form-label fw-semibold">Ordem</label>
        <input type="number" name="sort_order" class="form-control @error('sort_order') is-invalid @enderror" value="{{ old('sort_order', $f->sort_order ?? 0) }}" min="0">
        @error('sort_order')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
    <div class="col-md-2 d-flex align-items-end">
        <div class="form-check mb-2">
            <input type="hidden" name="is_active" value="0">
            <input type="checkbox" name="is_active" value="1" class="form-check-input" id="faq_is_active" @checked(old('is_active', $f->is_active ?? true))>
            <label class="form-check-label" for="faq_is_active">Ativa no site</label>
        </div>
    </div>
    <div class="col-12">
        <label class="form-label fw-semibold">Resposta *</label>
        <textarea id="faq_answer" name="answer" class="form-control @error('answer') is-invalid @enderror" rows="14">{!! $answerValue !!}</textarea>
        @error('answer')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
        <p class="form-text small text-muted mb-0">Edite no modo visual: negrito, listas e links. O conteúdo é salvo em HTML.</p>
    </div>
</div>

@include('admin.partials.tinymce-init', ['editorId' => 'faq_answer', 'formId' => 'faq_form'])
