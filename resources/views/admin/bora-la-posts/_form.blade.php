@php
    $pl = is_array($post->payload ?? null) ? $post->payload : [];
    $typeVal = old('type', $post->type ?? 'atualizacao');
    $isEdit = $post->exists;
@endphp

@if($isEdit)
    <div class="alert alert-light border mb-3">
        <strong>Tipo:</strong> {{ \App\Models\BoraLaPost::TYPES[$post->type] ?? $post->type }}
        <span class="text-muted small">(não é possível alterar o tipo após a criação)</span>
    </div>
@else
    <div class="mb-3">
        <label class="form-label fw-semibold">Tipo de publicação *</label>
        <select name="type" id="bora_la_type" class="form-select" required>
            @foreach(\App\Models\BoraLaPost::TYPES as $value => $label)
                <option value="{{ $value }}" {{ $typeVal === $value ? 'selected' : '' }}>{{ $label }}</option>
            @endforeach
        </select>
    </div>
@endif

<div class="row g-3 mb-3">
    <div class="col-md-8">
        <label class="form-label fw-semibold">Título *</label>
        <input type="text" name="title" class="form-control" value="{{ old('title', $post->title) }}" required maxlength="255">
    </div>
    <div class="col-md-4">
        <label class="form-label fw-semibold">Ordem</label>
        <input type="number" name="sort_order" class="form-control" value="{{ old('sort_order', $post->sort_order ?? 0) }}" min="0">
    </div>
    <div class="col-md-6">
        <label class="form-label fw-semibold">Publicar em</label>
        <input type="datetime-local" name="published_at" class="form-control"
               value="{{ old('published_at', $post->published_at ? $post->published_at->format('Y-m-d\TH:i') : '') }}">
        <div class="form-text">Opcional. Vazio = disponível assim que estiver marcado como publicado.</div>
    </div>
    <div class="col-md-6 d-flex align-items-end">
        <div class="form-check">
            <input class="form-check-input" type="checkbox" name="is_published" value="1" id="is_published"
                   {{ old('is_published', $post->is_published ?? true) ? 'checked' : '' }}>
            <label class="form-check-label fw-semibold" for="is_published">Publicado (Bora lá + painel do cotista)</label>
        </div>
    </div>
</div>

<div class="mb-3">
    <label class="form-label fw-semibold" for="bora_body">Texto principal</label>
    <textarea name="body" id="bora_body" class="form-control" rows="5">{{ old('body', $post->body) }}</textarea>
    <div class="form-text">Obrigatório exceto para <strong>enquete</strong> (pode ficar em branco se a pergunta e opções forem suficientes).</div>
</div>

<div class="bora-la-section border rounded-3 p-3 mb-3 d-none" data-borala-section="{{ \App\Models\BoraLaPost::TYPE_OFERTA_UNICA }}">
    <h6 class="fw-bold text-warning mb-3"><i class="fas fa-star me-1"></i>Período da oferta única *</h6>
    <div class="row g-2">
        <div class="col-md-3">
            <label class="form-label small">Início (data)</label>
            <input type="date" name="start_date" class="form-control" value="{{ old('start_date', $pl['start_date'] ?? '') }}">
        </div>
        <div class="col-md-3">
            <label class="form-label small">Início (hora)</label>
            <input type="time" name="start_time" class="form-control" value="{{ old('start_time', $pl['start_time'] ?? '') }}">
        </div>
        <div class="col-md-3">
            <label class="form-label small">Término (data)</label>
            <input type="date" name="end_date" class="form-control" value="{{ old('end_date', $pl['end_date'] ?? '') }}">
        </div>
        <div class="col-md-3">
            <label class="form-label small">Término (hora)</label>
            <input type="time" name="end_time" class="form-control" value="{{ old('end_time', $pl['end_time'] ?? '') }}">
        </div>
    </div>
</div>

<div class="bora-la-section border rounded-3 p-3 mb-3 d-none" data-borala-section="{{ \App\Models\BoraLaPost::TYPE_ENQUETE }}">
    <h6 class="fw-bold mb-3" style="color:#7c3aed"><i class="fas fa-poll me-1"></i>Enquete *</h6>
    <div class="mb-3">
        <label class="form-label small">Pergunta</label>
        <textarea name="question" class="form-control" rows="3">{{ old('question', $pl['question'] ?? '') }}</textarea>
    </div>
    <label class="form-label small">Opções (mínimo 2)</label>
    <div id="admin_enquete_options">
        @php
            $opts = old('options', $pl['options'] ?? ['', '']);
            if (! is_array($opts)) {
                $opts = ['', ''];
            }
        @endphp
        @foreach($opts as $i => $opt)
            <div class="input-group mb-2">
                <input type="text" name="options[]" class="form-control" value="{{ $opt }}" placeholder="Opção {{ $i + 1 }}">
            </div>
        @endforeach
    </div>
    <button type="button" class="btn btn-sm btn-outline-primary mb-0" id="admin_add_option">+ Opção</button>
</div>

<div class="border rounded-3 p-3 mb-3">
    <h6 class="fw-bold text-primary mb-2"><i class="fas fa-photo-video me-1"></i>Conteúdo complementar (texto e/ou vídeo)</h6>
    <p class="text-muted small mb-3">Usado em todos os tipos, conforme regras de validação do tipo selecionado.</p>
    @include('admin.bora-la-posts._content-type-fields', ['p' => $pl])
</div>

@push('scripts')
<script>
(function () {
    const select = document.getElementById('bora_la_type');
    const sections = document.querySelectorAll('[data-borala-section]');
    const body = document.getElementById('bora_body');

    function currentType() {
        if (select) return select.value;
        return @json($post->type ?? 'atualizacao');
    }

    function syncSections() {
        const t = currentType();
        sections.forEach(function (el) {
            const key = el.getAttribute('data-borala-section');
            el.classList.toggle('d-none', key !== t);
        });
        if (body) {
            body.required = (t !== 'enquete');
        }
    }

    if (select) select.addEventListener('change', syncSections);
    syncSections();

    const addBtn = document.getElementById('admin_add_option');
    if (addBtn) {
        addBtn.addEventListener('click', function () {
            const wrap = document.getElementById('admin_enquete_options');
            if (!wrap) return;
            const div = document.createElement('div');
            div.className = 'input-group mb-2';
            div.innerHTML = '<input type="text" name="options[]" class="form-control" value="" placeholder="Nova opção">';
            wrap.appendChild(div);
        });
    }

    const radios = document.querySelectorAll('input[name="content_type"]');
    const tw = document.getElementById('admin_ct_text_wrap');
    const vw = document.getElementById('admin_ct_video_wrap');
    const ti = document.getElementById('content_text');
    const vi = document.getElementById('content_video');
    function syncContentType() {
        if (!radios.length || !tw || !vw) return;
        const v = document.querySelector('input[name="content_type"]:checked')?.value || 'text';
        if (v === 'text') {
            tw.classList.remove('d-none'); vw.classList.add('d-none');
            if (ti) ti.required = true; if (vi) vi.required = false;
        } else if (v === 'video') {
            tw.classList.add('d-none'); vw.classList.remove('d-none');
            if (ti) ti.required = false; if (vi) vi.required = true;
        } else {
            tw.classList.remove('d-none'); vw.classList.remove('d-none');
            if (ti) ti.required = true; if (vi) vi.required = true;
        }
    }
    radios.forEach(r => r.addEventListener('change', syncContentType));
    syncContentType();
})();
</script>
@endpush
