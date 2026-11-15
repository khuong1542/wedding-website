@props(['items', 'page', 'prefix' => 'menu'])
<ul class="admin-menu" data-menu-level>
    @foreach ($items as $index => $item)
        <li class="admin-menu-item" data-menu-item>
            @if (!empty($item['children']))
                <button class="admin-menu-link" type="button" data-menu-toggle aria-expanded="false"
                    aria-controls="{{ $prefix }}-{{ $index }}">
                    <span class="admin-menu-icon" aria-hidden="true"><x-admin::icon :name="$item['icon'] ?? 'circle'" /></span>
                    <span class="admin-menu-label">{{ __('admin.' . $item['label']) }}</span>
                    <span class="admin-menu-chevron" aria-hidden="true"><x-admin::icon name="chevron-down" /></span>
                </button>
                <div class="admin-submenu" id="{{ $prefix }}-{{ $index }}" inert>
                    <div class="admin-submenu-inner">
                        <span class="admin-flyout-title">{{ __('admin.' . $item['label']) }}</span>
                        <x-admin::menu :items="$item['children']" :page="$page" :prefix="$prefix . '-' . $index" />
                    </div>
                </div>
            @else
                <a class="admin-menu-link {{ $page === $item['key'] ? 'is-active' : '' }}"
                    href="{{ route('admin.dashboard', ['page' => $item['key']]) }}"
                    @if ($page === $item['key']) aria-current="page" @endif>
                    <span class="admin-menu-icon" aria-hidden="true"><x-admin::icon :name="$item['icon'] ?? 'circle'" /></span>
                    <span class="admin-menu-label">{{ __('admin.' . $item['label']) }}</span>
                </a>
            @endif
        </li>
    @endforeach
</ul>
