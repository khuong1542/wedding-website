<div class="template-skeleton" role="img" aria-label="{{ __('pages.demo_layout') }}">
    <div class="skeleton-lines" aria-hidden="true"><span class="skeleton-block skeleton-heading"></span><span
            class="skeleton-block skeleton-line"></span><span class="skeleton-block skeleton-line skeleton-short"></span>
    </div>
    <div class="skeleton-hero skeleton-block" aria-hidden="true"></div>
    <div class="skeleton-grid" aria-hidden="true">
        @for ($item = 0; $item < 3; $item++)
            <div class="skeleton-card"><span class="skeleton-block skeleton-picture"></span><span
                    class="skeleton-block skeleton-line"></span><span
                    class="skeleton-block skeleton-line skeleton-short"></span></div>
        @endfor
    </div>
</div>
