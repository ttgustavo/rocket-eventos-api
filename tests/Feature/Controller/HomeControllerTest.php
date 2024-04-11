<?php

namespace Tests\Feature\Controller;

use Tests\TestCase;

class HomeControllerTest extends TestCase
{
    public function test_home_path_should_return_hello_world_message(): void
    {
        $response = $this->get('/api');
        $response->assertStatus(200)->assertContent('Hello world!');
    }
}
