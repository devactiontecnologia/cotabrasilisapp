{{-- TinyMCE (GPL) — reutilizado em FAQ e Informações do site --}}
@php
    $editorId = $editorId ?? 'rich_text_editor';
    $formId = $formId ?? null;
@endphp

@push('styles')
<style>
    .tox-tinymce { border-radius: 0.375rem !important; }
</style>
@endpush

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/tinymce@7.6.1/tinymce.min.js"></script>
<script>
(function () {
    if (typeof tinymce === 'undefined') return;

    var base = 'https://cdn.jsdelivr.net/npm/tinymce@7.6.1';
    var sel = '#{{ $editorId }}';

    tinymce.init({
        selector: sel,
        height: 460,
        license_key: 'gpl',
        promotion: false,
        branding: false,
        menubar: false,
        base_url: base,
        suffix: '.min',
        skin_url: base + '/skins/ui/oxide',
        content_css: base + '/skins/content/default/content.min.css',
        plugins: 'lists link autoresize code table',
        toolbar: 'undo redo | blocks | bold italic underline strikethrough | alignleft aligncenter alignright | bullist numlist | link table | removeformat | code',
        content_style: 'body { font-family: Inter, system-ui, sans-serif; font-size: 14px; line-height: 1.6; }',
        relative_urls: false,
        convert_urls: false,
        setup: function (editor) {
            editor.on('change input undo redo', function () {
                editor.save();
            });
        },
    });

    @if($formId)
    var form = document.getElementById('{{ $formId }}');
    if (form) {
        form.addEventListener('submit', function () {
            tinymce.triggerSave();
        });
    }
    @endif
})();
</script>
@endpush
