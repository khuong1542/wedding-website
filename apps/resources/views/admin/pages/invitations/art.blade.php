<div class="iv-art iv-art-{{ $invitation->template ?? 'floral' }}"
    style="--iv-accent: {{ $invitation->accent ?? '#D81B60' }}">
    @if ($image ?? null)
        <img class="iv-art-image" src="{{ $image }}" alt="">
    @endif
    <span class="iv-ornament iv-ornament-left" aria-hidden="true">❧</span><span class="iv-ornament iv-ornament-right"
        aria-hidden="true">❧</span>
    <div class="iv-art-copy"><span class="iv-art-eyebrow">{{ __('invitations.wedding') }}</span><span
            class="iv-art-heart" aria-hidden="true">♡</span>
        <h3>{{ $invitation->groom_name ?: __('invitations.groom_placeholder') }} <span>&amp;</span>
            {{ $invitation->bride_name ?: __('invitations.bride_placeholder') }}</h3>
        <span>{{ $invitation->date?->format('d.m.Y') ?? '— · — · —' }}</span>
        <div class="iv-art-meta"><span>{{ $invitation->time }}</span><span>{{ $invitation->venue }}</span></div>
    </div>
</div>
