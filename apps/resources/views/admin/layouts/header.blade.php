<header class="admin-header">
    <div class="admin-header-start">
        <button type="button" class="admin-toggle" data-sidebar-toggle data-label-expand="{{ __('admin.expand') }}"
            data-label-collapse="{{ __('admin.collapse') }}" aria-controls="admin-sidebar" aria-expanded="true"
            aria-label="{{ __('admin.collapse') }}"><x-admin::icon name="menu" /></button>
        <span class="admin-breadcrumb">{{ __('admin.admin') }} <span>/</span>
            <strong>{{ $title }}</strong></span>
    </div>
    <div class="admin-header-actions">
        <div class="admin-language" data-language>
            <button class="admin-language-toggle" type="button" data-language-toggle aria-expanded="false"
                aria-controls="admin-language-panel" aria-label="{{ __('admin.language') }}">
                <span class="admin-flag admin-flag-{{ app()->getLocale() }}"
                    aria-hidden="true">{{ app()->getLocale() === 'vn' ? '★' : '' }}</span>
                <x-admin::icon name="chevron-down" />
            </button>
            <div class="admin-language-panel" id="admin-language-panel" hidden>
                @foreach (['vn' => 'Tiếng Việt', 'en' => 'English'] as $locale => $label)
                    <a class="admin-language-option {{ app()->getLocale() === $locale ? 'is-selected' : '' }}"
                        href="{{ route('admin.dashboard', ['page' => $page, 'lang' => $locale]) }}"
                        lang="{{ $locale === 'vn' ? 'vi' : 'en' }}"
                        @if (app()->getLocale() === $locale) aria-current="true" @endif>
                        <span class="admin-flag admin-flag-{{ $locale }}"
                            aria-hidden="true">{{ $locale === 'vn' ? '★' : '' }}</span><span>{{ $label }}</span>
                        @if (app()->getLocale() === $locale)
                            <x-admin::icon name="check" />
                        @endif
                    </a>
                @endforeach
            </div>
        </div>
        <div class="admin-theme-switch" role="group" aria-label="{{ __('admin.theme') }}">
            @foreach (['system' => 'desktop', 'light' => 'sun', 'dark' => 'moon'] as $mode => $icon)
                <button type="button" data-theme-option="{{ $mode }}" aria-pressed="false"
                    aria-label="{{ __('admin.' . $mode) }}" title="{{ __('admin.' . $mode) }}"><x-admin::icon
                        :name="$icon" /></button>
            @endforeach
        </div>
        <div class="admin-notifications">
            <button class="admin-toggle" type="button" data-notification-toggle
                aria-controls="admin-notification-panel" aria-expanded="false"
                aria-label="{{ __('admin.notifications') }}"><x-admin::icon name="bell" /></button>
            <section class="admin-notification-panel" id="admin-notification-panel"
                aria-labelledby="notification-heading" hidden>
                <h2 id="notification-heading">{{ __('admin.notifications') }}</h2>
                <div><span class="admin-notification-icon"><x-admin::icon name="check-circle" /></span>
                    <h3>{{ __('admin.all_caught_up') }}</h3>
                    <p>{{ __('admin.no_notifications') }}</p>
                </div>
            </section>
        </div>
        <span class="admin-account"><span class="admin-avatar"
                aria-hidden="true">W</span><span>{{ __('admin.administrator') }}<small>{{ __('admin.admin') }}</small></span></span>
    </div>
</header>
