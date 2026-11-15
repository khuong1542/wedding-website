<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminDashboardTest extends TestCase
{
    use RefreshDatabase;

    public function test_dashboard_uses_english_by_default(): void
    {
        $this->get('/admin')->assertOk()
            ->assertSee('<html lang="en">', false)
            ->assertSee('Make room for the moments that matter.')
            ->assertSee('Appearance', false)
            ->assertSee('Notifications')
            ->assertCookie('admin_locale', 'en');
    }

    public function test_language_selection_is_saved_and_translates_navigation(): void
    {
        $this->get('/admin?lang=vn')->assertOk()
            ->assertSee('<html lang="vi">', false)
            ->assertSee('Tổng quan')
            ->assertSee('Quản lý khách mời')
            ->assertSee('Theo máy')
            ->assertCookie('admin_locale', 'vn');

        $this->withCookie('admin_locale', 'vn')->get('/admin?page=guests')
            ->assertOk()->assertSee('Danh sách khách mời');
    }

    public function test_explicit_language_overrides_saved_preference(): void
    {
        $this->withCookie('admin_locale', 'vn')->get('/admin?lang=en')
            ->assertOk()->assertSee('<html lang="en">', false)
            ->assertCookie('admin_locale', 'en');
    }

    public function test_invalid_language_falls_back_to_english(): void
    {
        $this->get('/admin?lang[]=vn')->assertOk()
            ->assertSee('<html lang="en">', false);
    }

    public function test_unknown_module_returns_not_found(): void
    {
        $this->get('/admin?page=missing')->assertNotFound();
        $this->get('/admin?page[]=guests')->assertNotFound();
    }

    public function test_sidebar_groups_and_nested_modules_are_available_in_both_languages(): void
    {
        foreach (['en', 'vn'] as $locale) {
            $response = $this->get('/admin?lang='.$locale)->assertOk();
            $checkItems = function (array $items) use (&$checkItems, $response, $locale): void {
                foreach ($items as $item) {
                    $response->assertSee(__('admin.'.$item['label'], [], $locale));
                    if (! empty($item['children'])) {
                        $checkItems($item['children']);
                    } else {
                        $this->get('/admin?lang='.$locale.'&page='.$item['key'])->assertOk();
                    }
                }
            };
            $checkItems(config('sidebar.navigation'));
        }
    }
}
