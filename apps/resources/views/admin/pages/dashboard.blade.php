@extends('admin.layouts.index')

@push('styles')
    @vite('resources/css/admin/pages/dashboard.css')
@endpush

@push('scripts')
    @vite('resources/js/admin/pages/dashboard.js')
@endpush

@section('content')
    <section class="admin-welcome">
        <div><span class="admin-eyebrow">{{ __('admin.planning') }}</span>
            <h2>{{ __('admin.welcome') }}</h2>
            <p>{{ __('admin.welcome_description') }}</p><a href="{{ route('admin.couple.index') }}"
                class="admin-primary-link">{{ __('admin.manage_wedding') }}<x-admin::icon name="arrow-up-right" /></a>
        </div>
        <div class="admin-welcome-art" aria-hidden="true">
            <div><x-admin::icon name="heart" /></div><span>WEDDING / WORKSPACE</span>
        </div>
    </section>
    <section class="admin-stats" aria-label="{{ __('admin.overview_label') }}">
        @foreach ([['group', 'total_guests'], ['check-circle', 'confirmed'], ['envelope', 'sent'], ['images', 'albums']] as [$icon, $label])
            <article class="admin-stat">
                <div><span>{{ __('admin.' . $label) }}</span><x-admin::icon :name="$icon" /></div>
                <strong>—</strong><span class="admin-stat-note">{{ __('admin.not_connected') }}</span>
            </article>
        @endforeach
    </section>
    <div class="admin-dashboard-grid">
        <section class="admin-panel">
            <div class="admin-panel-heading">
                <p>{{ __('admin.setup') }}</p>
                <h2>{{ __('admin.getting_started') }}</h2>
            </div>
            <div class="admin-steps">
                @foreach ([['wedding', 'calendar', 'step_wedding'], ['guests', 'group', 'step_guests'], ['invitations', 'envelope', 'step_invite']] as [$key, $icon, $label])
                    <a class="admin-step"
                        href="{{ route('admin.' . ($key === 'wedding' ? 'couple' : $key) . '.index') }}"><span
                            class="admin-step-icon"><x-admin::icon
                                :name="$icon" /></span><span><strong>{{ __('admin.' . $label) }}</strong><small>{{ __('admin.' . $label . '_desc') }}</small></span><x-admin::icon
                            name="arrow-right" /></a>
                @endforeach
            </div>
        </section>
        <section class="admin-panel">
            <div class="admin-panel-heading">
                <p>{{ __('admin.workspace') }}</p>
                <h2>{{ __('admin.quick_access') }}</h2>
            </div>
            <div class="admin-quick-links">
                @foreach ([['shared-events', 'calendar', 'schedule_desc'], ['photos', 'images', 'photos_desc'], ['rsvp', 'check-circle', 'rsvp_desc']] as [$key, $icon, $description])
                    <a href="{{ route('admin.' . ($key === 'wedding' ? 'couple' : $key) . '.index') }}"><x-admin::icon
                            :name="$icon" /><span><strong>{{ __('admin.' . str_replace('-', '_', $key)) }}</strong><small>{{ __('admin.' . $description) }}</small></span><x-admin::icon
                            name="arrow-up-right" /></a>
                @endforeach
            </div>
        </section>
    </div>
@endsection
