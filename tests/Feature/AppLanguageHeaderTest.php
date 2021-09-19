<?php

namespace Tests\Feature;

use Tests\TestCase;

class AppLanguageHeaderTest extends TestCase
{
    /**
     * Test setting app language by App-Language header.
     *
     * @return void
     */
    public function test_set_locale_by_app_language_header()
    {
        $locales = config('locale.languages');

        foreach ($locales as $locale => $localeProps) {
            $response = $this->withHeaders([
                'App-Language' => $locale,
            ])->getJson(route('testing.app-language-header'));

            $response->assertJson(['locale' => $locale], $strict = false);
        }
    }
    public function test_set_default_locale_by_unsupported_app_language_header()
    {
        $locale = config('app.locale');
        $response = $this->withHeaders([
            'App-Language' => "FAKE_LOCALE",
        ])->getJson(route('testing.app-language-header'));

        $response->assertJson(['locale' => $locale], $strict = false);
    }
    public function test_set_default_locale_if_no_app_language_header()
    {
        $locale = config('app.locale');
        $response = $this->getJson(route('testing.app-language-header'));

        $response->assertJson(['locale' => $locale], $strict = false);
    }
}
