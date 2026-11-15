<?php

return [
    'name' => 'Wedding CMS',
    'navigation' => [
        ['label' => 'overview_label', 'children' => [
            ['label' => 'overview', 'icon' => 'dashboard', 'key' => 'overview', 'route' => 'admin.dashboard'],
        ]],
        ['label' => 'website_management', 'children' => [
            ['label' => 'home_page', 'icon' => 'home', 'key' => 'home', 'route' => 'admin.home.index'],
            ['label' => 'couple_information', 'icon' => 'heart', 'key' => 'wedding', 'route' => 'admin.couple.index'],
            ['label' => 'our_story', 'icon' => 'book-heart', 'key' => 'our-story', 'route' => 'admin.our-story.index'],
            ['label' => 'wedding_events', 'icon' => 'calendar-event', 'children' => [
                ['label' => 'side_events', 'icon' => 'group', 'key' => 'events', 'route' => 'admin.events.index'],
                ['label' => 'shared_events', 'icon' => 'calendar-event', 'key' => 'shared-events', 'route' => 'admin.shared-events.index'],
            ]],
            ['label' => 'gallery', 'icon' => 'images', 'children' => [
                ['label' => 'albums', 'icon' => 'photo-album', 'key' => 'albums', 'route' => 'admin.albums.index'],
                ['label' => 'photos', 'icon' => 'image', 'key' => 'photos', 'route' => 'admin.photos.index'],
                ['label' => 'videos', 'icon' => 'video', 'key' => 'videos', 'route' => 'admin.videos.index'],
            ]],
            ['label' => 'wedding_registry', 'icon' => 'gift', 'key' => 'registry', 'route' => 'admin.registry.index'],
            ['label' => 'pages_sections', 'icon' => 'layout', 'key' => 'pages', 'route' => 'admin.pages.index'],
        ]],
        ['label' => 'guest_management', 'children' => [
            ['label' => 'guest_list', 'icon' => 'group', 'key' => 'guests', 'route' => 'admin.guests.index'],
            ['label' => 'rsvp_management', 'icon' => 'clipboard-check', 'key' => 'rsvp', 'route' => 'admin.rsvp.index'],
            ['label' => 'seating_arrangement', 'icon' => 'table', 'key' => 'seating', 'route' => 'admin.seating.index'],
            ['label' => 'invitations', 'icon' => 'envelope', 'key' => 'invitations', 'route' => 'admin.invitations.index'],
            ['label' => 'wishes_messages', 'icon' => 'message-heart', 'key' => 'wishes', 'route' => 'admin.wishes.index'],
        ]],
        ['label' => 'content_management', 'children' => [
            ['label' => 'blog_news', 'icon' => 'news', 'key' => 'blog', 'route' => 'admin.blog.index'],
            ['label' => 'media_library', 'icon' => 'folder', 'key' => 'media', 'route' => 'admin.media.index'],
            ['label' => 'translations', 'icon' => 'translate', 'key' => 'translations', 'route' => 'admin.translations.index'],
        ]],
        ['label' => 'design_customization', 'children' => [
            ['label' => 'themes_appearance', 'icon' => 'palette', 'key' => 'themes', 'route' => 'admin.themes.index'],
            ['label' => 'page_builder', 'icon' => 'layout', 'key' => 'page-builder', 'route' => 'admin.page-builder.index'],
            ['label' => 'ui_settings', 'icon' => 'slider', 'key' => 'ui-settings', 'route' => 'admin.ui-settings.index'],
        ]],
        ['label' => 'system_group', 'children' => [
            ['label' => 'seo_analytics', 'icon' => 'search', 'key' => 'seo', 'route' => 'admin.seo.index'],
            ['label' => 'navigation_footer', 'icon' => 'menu', 'key' => 'navigation', 'route' => 'admin.navigation.index'],
            ['label' => 'general_settings', 'icon' => 'cog', 'key' => 'settings', 'route' => 'admin.settings.index'],
            ['label' => 'users_permissions', 'icon' => 'user-check', 'key' => 'accounts', 'route' => 'admin.accounts.index'],
            ['label' => 'activity_logs', 'icon' => 'history', 'key' => 'activity-logs', 'route' => 'admin.activity-logs.index'],
        ]],
    ],
];
