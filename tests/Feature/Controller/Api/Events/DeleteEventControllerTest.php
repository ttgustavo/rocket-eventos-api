<?php

namespace Tests\Feature\Controller\Api\Events;

use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DeleteEventControllerTest extends TestCase
{
    use RefreshDatabase;

    private int $eventId = 0;

    protected function setUp(): void
    {
        parent::setUp();
        Carbon::setTestNow('2024-01-01T00:00:00Z');

        $data = [
            'name' => 'My Event',
            'slug' => 'my-event',
            'subscription_date_start' => '2024-01-02T00:00:00Z',
            'subscription_date_end' => '2024-01-03T00:00:00Z',
            'presentation_at' => '2024-01-10T00:00:00Z'
        ];

        $response = parent::postJson('/api/admin/events', $data);

        $this->eventId = json_decode($response->content(), true)['id'];
    }

    public function test_returns_status_code_no_content_when_deleted(): void
    {
        $response = parent::delete("/api/admin/events/{$this->eventId}");

        $response->assertNoContent();
    }

    public function test_returns_status_code_bad_request_with_code_1_when_event_does_not_exists(): void
    {
        $response = parent::delete('/api/admin/events/9541');

        $response->assertBadRequest();
        $response->assertJson(['code' => 1]);
    }

    // ---- Validation
    public function test_returns_status_code_bad_request_with_code_0_data_when_event_id_is_not_a_number(): void
    {
        $response = parent::delete("/api/admin/events/abc");

        $response->assertBadRequest();
        $response->assertJson(['code' => 0]);
    }
}
