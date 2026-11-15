<?php

return [
    'name' => 'Wedding CMS',
    'navigation' => [
        ['label' => 'overview_label', 'children' => [
            ['label' => 'overview', 'icon' => 'dashboard', 'key' => 'overview'],
        ]],
        ['label' => 'website_management', 'children' => [
            ['label' => 'home_page', 'icon' => 'home', 'key' => 'home'],
            ['label' => 'couple_information', 'icon' => 'heart', 'key' => 'wedding'],
            ['label' => 'our_story', 'icon' => 'book-heart', 'key' => 'our-story'],
            ['label' => 'wedding_events', 'icon' => 'calendar-event', 'children' => [
                ['label' => 'all_events', 'icon' => 'calendar', 'key' => 'events'],
                ['label' => 'event_schedule', 'icon' => 'calendar-check', 'key' => 'schedule'],
                ['label' => 'venues_locations', 'icon' => 'map', 'key' => 'venues'],
            ]],
            ['label' => 'gallery', 'icon' => 'images', 'children' => [
                ['label' => 'albums', 'icon' => 'photo-album', 'key' => 'albums'],
                ['label' => 'photos', 'icon' => 'image', 'key' => 'photos'],
                ['label' => 'videos', 'icon' => 'video', 'key' => 'videos'],
            ]],
            ['label' => 'wedding_registry', 'icon' => 'gift', 'key' => 'registry'],
            ['label' => 'pages_sections', 'icon' => 'layout', 'key' => 'pages'],
        ]],
        ['label' => 'guest_management', 'children' => [
            ['label' => 'guest_list', 'icon' => 'group', 'key' => 'guests'],
            ['label' => 'rsvp_management', 'icon' => 'clipboard-check', 'key' => 'rsvp'],
            ['label' => 'seating_arrangement', 'icon' => 'table', 'key' => 'seating'],
            ['label' => 'invitations', 'icon' => 'envelope', 'key' => 'invitations'],
            ['label' => 'wishes_messages', 'icon' => 'message-heart', 'key' => 'wishes'],
        ]],
        ['label' => 'content_management', 'children' => [
            ['label' => 'blog_news', 'icon' => 'news', 'key' => 'blog'],
            ['label' => 'media_library', 'icon' => 'folder', 'key' => 'media'],
            ['label' => 'translations', 'icon' => 'translate', 'key' => 'translations'],
        ]],
        ['label' => 'design_customization', 'children' => [
            ['label' => 'themes_appearance', 'icon' => 'palette', 'key' => 'themes'],
            ['label' => 'page_builder', 'icon' => 'layout', 'key' => 'page-builder'],
            ['label' => 'ui_settings', 'icon' => 'slider', 'key' => 'ui-settings'],
        ]],
        ['label' => 'system_group', 'children' => [
            ['label' => 'seo_analytics', 'icon' => 'search', 'key' => 'seo'],
            ['label' => 'navigation_footer', 'icon' => 'menu', 'key' => 'navigation'],
            ['label' => 'general_settings', 'icon' => 'cog', 'key' => 'settings'],
            ['label' => 'users_permissions', 'icon' => 'user-check', 'key' => 'accounts'],
            ['label' => 'activity_logs', 'icon' => 'history', 'key' => 'activity-logs'],
        ]],
    ],
];
