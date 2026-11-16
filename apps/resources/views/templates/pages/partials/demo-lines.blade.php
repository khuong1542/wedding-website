<div class="v2-demo-lines" aria-hidden="true">
    <span class="skeleton-block v2-line v2-line-title"></span>
    @for ($line = 0; $line < ($count ?? 2); $line++)
        <span class="skeleton-block v2-line {{ $line === ($count ?? 2) - 1 ? 'v2-line-short' : '' }}"></span>
    @endfor
</div>
