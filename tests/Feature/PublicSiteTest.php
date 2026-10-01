<?php

namespace Tests\Feature;

use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PublicSiteTest extends TestCase
{
    use RefreshDatabase;

    /**
     * The home page renders and bootstraps window.App.* with the exact
     * shapes the untouched front-end JS modules expect (design B.16).
     */
    public function test_home_page_bootstraps_app_data_shapes(): void
    {
        $this->seed(DatabaseSeeder::class);

        $response = $this->get('/');

        $response->assertStatus(200);

        $body = $response->getContent();

        // CARS is a JS array literal with all 9 seeded entries.
        $this->assertStringContainsString('App.CARS = [', $body);
        $this->assertSame(9, substr_count($body, '"model":'));

        // CAT_STYLE is a keyed object literal, NEVER an array.
        $this->assertStringContainsString('App.CAT_STYLE = {', $body);
        $this->assertStringNotContainsString('App.CAT_STYLE = [', $body);

        // QUIZ has 4 question objects.
        $this->assertStringContainsString('App.QUIZ = [', $body);
        $this->assertSame(4, substr_count($body, '"q":'));
    }
}
