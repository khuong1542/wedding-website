@php
    $ui = array_replace(config('appearance.defaults'), $appearance ?? []);
    $shadows = [
        'none' => 'none',
        'soft' => '0 3px 14px #58152512',
        'medium' => '0 5px 20px #58152526',
        'strong' => '0 8px 30px #58152540',
    ];
    $buttonPadding = ['small' => '7px 14px', 'medium' => '12px 24px', 'large' => '16px 32px'];
@endphp
<style>
    :root {
        --wedding-spacing: {{ (int) $ui['spacing'] }}px;
    }

    html {
        scroll-behavior: {{ $ui['smooth_scroll'] ? 'smooth' : 'auto' }};
    }

    @if ($ui['layout'] === 'boxed')
        .page-template main {
            max-width: {{ (int) $ui['content_width'] }}px;
            margin-inline: auto;
            box-shadow: {{ $shadows[$ui['shadow']] }};
        }
    @endif
    @if ($ui['layout'] === 'wide')
        .page-template main {
            max-width: {{ (int) $ui['content_width'] + 200 }}px;
            margin-inline: auto;
        }

        .v2-wedding-layout,
        .builder-published-content,
        .v2-header,
        .v2-footer {
            max-width: none;
        }
    @endif
    .v2-event-grid,
    .v2-gallery-grid,
    .v2-couple-grid,
    .v2-gift-grid,
    .v2-wishes-layout {
        gap: var(--wedding-spacing);
    }

    .v2-section {
        padding-block: calc(32px + var(--wedding-spacing));
    }

    .v2-event-grid article,
    .v2-gift-grid article,
    .v2-demo-form,
    .v2-person,
    .builder-content-button {
        box-shadow: {{ $shadows[$ui['shadow']] }};
        border-radius: var(--wedding-radius);
    }

    .v2-button-placeholder,
    .builder-content-button {
        padding: {{ $buttonPadding[$ui['button_size']] }};
        border: 1px solid var(--wedding-primary);

        @if ($ui['button_style'] === 'outline')
            background: transparent;
            color: var(--wedding-primary);
        @endif
    }

    @if ($ui['hover_effect'])
        .builder-content-button {
            transition: transform 180ms ease, opacity 180ms ease;
        }

        .builder-content-button:hover {
            transform: translateY(-2px);
            opacity: 0.85;
        }

        .v2-nav a:hover {
            color: var(--wedding-primary);
        }
    @endif
    @if ($ui['mode'] === 'dark')
        :root {
            color-scheme: dark;
            --wedding-background: #18111a;
            --wedding-text: #f8edf2;
            --wedding-border: #513441;
            --wedding-secondary: #36212d;
        }

        .v2-hero,
        .v2-hero-image,
        .v4-story,
        .v2-events,
        .v2-wishes {
            background: var(--wedding-secondary);
        }
    @endif
    @if ($ui['mode'] === 'system')
        @media (prefers-color-scheme: dark) {
            :root {
                color-scheme: dark;
                --wedding-background: #18111a;
                --wedding-text: #f8edf2;
                --wedding-border: #513441;
                --wedding-secondary: #36212d;
            }

            .v2-hero,
            .v2-hero-image,
            .v4-story,
            .v2-events,
            .v2-wishes {
                background: var(--wedding-secondary);
            }
        }
    @endif
    @if ($ui['mode'] === 'light')
        :root {
            color-scheme: light;
        }
    @endif
    @media (prefers-reduced-motion: reduce) {
        html {
            scroll-behavior: auto;
        }

        .builder-content-button {
            transition: none;
        }

        .builder-content-button:hover {
            transform: none;
        }
    }
</style>
