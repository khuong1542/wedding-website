@php($appearance = array_replace(config('appearance.defaults'), $appearance ?? []))
<style>
    :root {
        --wedding-primary: {{ $appearance['primary'] }};
        --wedding-secondary: {{ $appearance['secondary'] }};
        --wedding-accent: {{ $appearance['accent'] }};
        --wedding-background: {{ $appearance['background'] }};
        --wedding-text: {{ $appearance['text'] }};
        --wedding-border: {{ $appearance['border'] }};
        --wedding-radius: {{ (int) $appearance['radius'] }}px;
    }

    body.page-template {
        background: var(--wedding-background);
        color: var(--wedding-text);
        font-family: '{{ config('appearance.fonts')[$appearance['body_font']] ?? 'Noto Sans' }}', sans-serif !important;
        font-size: {{ (int) $appearance['font_size'] }}px;
    }

    .page-template h1,
    .page-template h2,
    .page-template h3,
    .v2-brand,
    .v2-footer strong {
        font-family: '{{ config('appearance.fonts')[$appearance['heading_font']] ?? 'Noto Serif' }}', serif !important;
    }

    .v2-wedding-layout,
    .v2-header,
    .v2-footer {
        box-sizing: border-box;
        max-width: {{ (int) $appearance['content_width'] }}px;
        margin-inline: auto;
    }

    .v2-header {
        position: {{ $appearance['sticky_header'] ? 'sticky' : 'relative' }};
        top: 0;
        z-index: 10;
    }

    .v2-event-grid article,
    .v2-gift-grid article,
    .v2-form-input,
    .v2-form-textarea {
        border-radius: var(--wedding-radius);
    }

    @if (!$appearance['animations'])
        .page-template *,
        .page-template *::before,
        .page-template *::after {
            animation: none !important;
            transition: none !important;
            scroll-behavior: auto !important;
        }
    @endif
</style>
