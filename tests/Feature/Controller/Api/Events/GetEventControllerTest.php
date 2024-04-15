<?php

namespace Tests\Feature\Controller\Api\Events;

use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Symfony\Component\HttpFoundation\Response;
use Tests\TestCase;

class GetEventControllerTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        Carbon::setTestNow('2024-01-01T15:00:00Z');
        parent::setUp();
    }

    public function test_returns_status_code_ok_when_exists(): void
    {
        $slug = $this->createEvent();

        $response = $this->get("/api/admin/events/$slug");

        $response->assertStatus(Response::HTTP_OK);
    }

    public function test_returns_status_code_bad_request_with_code_1_when_not_exists(): void
    {
        $slug = 'my-event';

        $response = $this->get("/api/admin/events/$slug");

        $response->assertStatus(Response::HTTP_BAD_REQUEST);
        $response->assertJson(['code' => 1]);
    }

    public function test_returns_status_code_bad_request_with_code_0_when_validation_fails(): void
    {
        $slug = 'my-event-';

        $response = $this->get("/api/admin/events/$slug");

        $response->assertStatus(Response::HTTP_BAD_REQUEST);
        $response->assertJson(['code' => 0]);
    }

    private function createEvent(): string
    {
        $data = [
            'name' => 'My Event',
            'slug' => 'my-event',
            'subscription_date_start' => '2024-01-02T00:00:00Z',
            'subscription_date_end' => '2024-04-13T12:00:00Z',
            'presentation_at' => '2024-04-13T12:00:00Z',
        ];

        $response = parent::postJson('/api/admin/events', $data);
        $response->assertStatus(Response::HTTP_CREATED);
        return $data['slug'];
    }
}
