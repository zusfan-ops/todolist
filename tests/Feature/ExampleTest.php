<?php

namespace Tests\Feature;

use Tests\TestCase;

class ExampleTest extends TestCase
{
    public function test_landing_page_loads_for_guest(): void
    {
        $response = $this->get('/');

        $response->assertStatus(200);
        $response->assertSee('Daftar Gratis');
    }

    public function test_guest_visiting_app_is_redirected_to_login(): void
    {
        $response = $this->get('/app');

        $response->assertRedirect('/login');
    }

    public function test_login_page_loads(): void
    {
        $response = $this->get('/login');

        $response->assertStatus(200);
    }

    public function test_register_page_loads(): void
    {
        $response = $this->get('/register');

        $response->assertStatus(200);
    }
}
