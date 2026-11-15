<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\View;
use Symfony\Component\HttpFoundation\Response;

class AdminContext
{
    public function handle(Request $request, Closure $next): Response
    {
        $locale = $request->query('lang', $request->cookie('admin_locale', 'en'));
        $locale = in_array($locale, ['en', 'vn'], true) ? $locale : 'en';
        app()->setLocale($locale);
        $page = $request->route('module');
        if ($page !== null) {
            $navigation = config('sidebar.navigation', []);
            $findPage = function (array $items) use (&$findPage, $page): ?array {
                foreach ($items as $item) {
                    if (($item['key'] ?? null) === $page) {
                        return $item;
                    }
                    if (! empty($item['children']) && ($found = $findPage($item['children']))) {
                        return $found;
                    }
                }

                return null;
            };
            $item = $findPage($navigation);
            abort_unless($item, 404);
            View::share([
                'navigation' => $navigation,
                'page' => $page,
                'pageRoute' => $request->route()->getName(),
                'title' => __('admin.'.$item['label']),
            ]);
        }
        $response = $next($request);
        if ($request->expectsJson() && $response instanceof RedirectResponse) {
            $response = response()->json(['redirect' => $response->getTargetUrl()]);
        }
        $response->headers->setCookie(cookie('admin_locale', $locale, 60 * 24 * 365));

        return $response;
    }
}
