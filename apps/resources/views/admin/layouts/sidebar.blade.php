<aside class="admin-sidebar" id="admin-sidebar" aria-label="{{ __('admin.management') }}">
    <a class="admin-brand" href="{{ route('admin.dashboard') }}"><span class="admin-brand-mark"><x-admin::icon
                name="heart" /></span><span
            class="admin-brand-text">{{ config('sidebar.name') }}<small>{{ __('admin.administration_panel') }}</small></span></a>
    <nav class="admin-navigation" aria-label="{{ __('admin.management') }}">
        @foreach ($navigation as $index => $group)
            <section class="admin-nav-group" aria-labelledby="sidebar-group-{{ $index }}">
                <h2 class="admin-nav-caption" id="sidebar-group-{{ $index }}">
                    {{ __('admin.' . $group['label']) }}</h2>
                <x-admin::menu :items="$group['children']" :page="$page" :prefix="'menu-' . $index" />
            </section>
        @endforeach
    </nav>
    <div class="admin-sidebar-footer"><span class="admin-brand-mark"><x-admin::icon name="heart-circle" /></span><span
            class="admin-brand-text">{{ __('admin.made_with_care') }}<small>Wedding workspace</small></span>
    </div>
</aside>
