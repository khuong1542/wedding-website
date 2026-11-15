<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CoupleProfile;
use App\Models\OurStory;
use App\Models\WeddingRegistry;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class DashboardController extends Controller
{
    public function __invoke(Request $request): Response
    {
        $locale = $request->query('lang', $request->cookie('admin_locale', 'en'));
        $locale = in_array($locale, ['en', 'vn'], true) ? $locale : 'en';
        app()->setLocale($locale);

        $navigation = config('sidebar.navigation', []);
        $pages = collect();
        $collectPages = function (array $items) use (&$collectPages, &$pages): void {
            foreach ($items as $item) {
                if (! empty($item['children'])) {
                    $collectPages($item['children']);
                } else {
                    $pages->put($item['key'], $item['label']);
                }
            }
        };
        $collectPages($navigation);
        $page = $request->query('page', 'overview');
        abort_unless(is_string($page) && $pages->has($page), 404);

        return response()->view(match ($page) {
            'overview' => 'admin.pages.dashboard',
            'wedding' => 'admin.pages.couple',
            'our-story' => 'admin.pages.our-story.index',
            'registry' => 'admin.pages.registry.index',
            default => 'admin.pages.empty',
        }, [
            'navigation' => $navigation,
            'registry' => $page === 'registry' ? WeddingRegistry::find(1) : null,
            'story' => $page === 'our-story' ? OurStory::with('milestones')->find(1) : null,
            'couple' => in_array($page, ['wedding', 'registry'], true) ? CoupleProfile::query()->find(1) : null,
            'page' => $page,
            'title' => __('admin.'.$pages->get($page)),
        ])->cookie('admin_locale', $locale, 60 * 24 * 365);
    }
}
