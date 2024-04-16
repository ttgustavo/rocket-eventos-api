<?php

namespace Tests\Feature\Controller\Api\Events;

use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UpdateEventControllerTest extends TestCase
{
    use RefreshDatabase;

    private int $eventId = 0;

    protected function setUp(): void
    {
        parent::setUp();
        Carbon::setTestNow('2024-01-01T15:00:00Z');

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

    public function test_returns_status_code_ok_with_data_when_updated(): void
    {
        $data = [
            'name' => 'My Event Updated',
            'slug' => 'my-event-updated',
            'details' => 'My event details here.',
            'subscription_date_start' => '2024-01-01T00:00:00Z',
            'subscription_date_end' => '2024-01-01T00:00:00Z',
            'presentation_at' => '2024-01-01T00:00:00Z',
            'status' => 1,
        ];

        $response = parent::patchJson("/api/admin/events/{$this->eventId}", $data);

        $response->assertOk();
        $response->assertJsonStructure(['slug']);
    }

    public function test_returns_status_code_ok_with_empty_data_when_updated_with_same_value(): void
    {
        $data = ['slug' => 'my-event'];

        $response = parent::patchJson("/api/admin/events/{$this->eventId}", $data);

        $response->assertOk();
        $response->assertJsonStructure([]);
    }

    public function test_returns_status_code_ok_with_empty_data_when_updated_with_empty_values(): void
    {
        $data = [];

        $response = parent::patchJson("/api/admin/events/{$this->eventId}", $data);

        $response->assertOk();
        $response->assertJsonStructure([]);
    }

    public function test_returns_status_code_bad_request_with_code_1_when_event_does_not_exists(): void
    {
        $data = ['slug' => 'my-event-updated'];

        $response = parent::patchJson('/api/admin/events/9541', $data);

        $response->assertBadRequest();
        $response->assertJson(['code' => 1]);
    }

    // ---- Validation
    public function test_returns_status_code_bad_request_with_code_0_data_when_one_field_is_invalid(): void
    {
        $data = [
            'name' => 'My Event Updated',
            'slug' => 'my-event-updated',
            'details' => 'My event details here.',
            'subscription_date_start' => '2024-01-01T00:00:00Z',
            'subscription_date_end' => '2024-01-01T00:00:00Z',
            'presentation_at' => '2024-01-01T00:00:00Z',
            'status' => -1,
        ];

        $response = parent::patchJson("/api/admin/events/{$this->eventId}", $data);

        $response->assertBadRequest();
        $response->assertJson(['code' => 0]);
    }

    public function test_returns_status_code_bad_request_with_code_0_data_when_event_id_is_not_a_number(): void
    {
        $response = parent::patch("/api/admin/events/abc");

        $response->assertBadRequest();
        $response->assertJson(['code' => 0]);
    }

    public function test_returns_status_code_bad_request_with_code_0_when_name_is_empty(): void
    {
        $data = ['name' => ''];

        $response = parent::patchJson('/api/admin/events/9541', $data);

        $response->assertBadRequest();
        $response->assertJson(['code' => 0]);
    }

    public function test_returns_status_code_bad_request_with_code_0_when_name_is_less_than_minimum(): void
    {
        $data = ['name' => 'Abc'];

        $response = parent::patchJson("/api/admin/events/{$this->eventId}", $data);

        $response->assertBadRequest();
        $response->assertJson(['code' => 0]);
    }

    public function test_returns_status_code_bad_request_with_code_0_when_slug_is_empty(): void
    {
        $data = ['slug' => ''];

        $response = parent::patchJson("/api/admin/events/{$this->eventId}", $data);

        $response->assertBadRequest();
        $response->assertJson(['code' => 0]);
    }

    public function test_returns_status_code_bad_request_with_code_0_when_slug_is_less_than_minimum(): void
    {
        $data = ['slug' => 'abc'];

        $response = parent::patchJson("/api/admin/events/{$this->eventId}", $data);

        $response->assertBadRequest();
        $response->assertJson(['code' => 0]);
    }

    public function test_returns_status_code_bad_request_with_code_0_when_details_is_empty(): void
    {
        $data = ['details' => ''];

        $response = parent::patchJson("/api/admin/events/{$this->eventId}", $data);

        $response->assertBadRequest();
        $response->assertJson(['code' => 0]);
    }

    public function test_returns_status_code_bad_request_with_code_0_when_details_is_longer_than_maximum(): void
    {
        $details = '';
        $details = str_pad($details, 1001, 'a');
        $data = ['details' => $details];

        $response = parent::patchJson("/api/admin/events/{$this->eventId}", $data);

        $response->assertBadRequest();
        $response->assertJson(['code' => 0]);
    }

    public function test_returns_status_code_bad_request_with_code_0_when_subscription_date_start_is_empty(): void
    {
        $data = ['subscription_date_start' => ''];

        $response = parent::patchJson("/api/admin/events/{$this->eventId}", $data);

        $response->assertBadRequest();
        $response->assertJson(['code' => 0]);
    }

    public function test_returns_status_code_bad_request_with_code_0_when_subscription_date_start_year_less_than_2020(): void
    {
        $data = ['subscription_date_start' => '2019-03-01T15:00:00Z'];

        $response = parent::patchJson("/api/admin/events/{$this->eventId}", $data);

        $response->assertBadRequest();
        $response->assertJson(['code' => 0]);
    }

    public function test_returns_status_code_bad_request_with_code_0_when_subscription_date_end_year_less_than_2020(): void
    {
        $data = ['subscription_date_end' => '2019-03-01T15:00:00Z'];

        $response = parent::patchJson("/api/admin/events/{$this->eventId}", $data);

        $response->assertBadRequest();
        $response->assertJson(['code' => 0]);
    }

    public function test_returns_status_code_bad_request_with_code_0_when_presentation_at_year_less_than_2020(): void
    {
        $data = ['presentation_at' => '2019-03-01T15:00:00Z'];

        $response = parent::patchJson("/api/admin/events/{$this->eventId}", $data);

        $response->assertBadRequest();
        $response->assertJson(['code' => 0]);
    }

    public function test_returns_status_code_bad_request_with_code_0_when_status_is_empty(): void
    {
        $data = ['status' => -1];

        $response = parent::patchJson("/api/admin/events/{$this->eventId}", $data);

        $response->assertBadRequest();
        $response->assertJson(['code' => 0]);
    }

    public function test_returns_status_code_bad_request_with_code_0_when_status_is_not_valid(): void
    {
        $data1 = ['status' => 5];
        $data2 = ['status' => -1];

        $response1 = parent::patchJson("/api/admin/events/{$this->eventId}", $data1);
        $response2 = parent::patchJson("/api/admin/events/{$this->eventId}", $data2);

        $response1->assertBadRequest();
        $response1->assertJson(['code' => 0]);
        $response2->assertBadRequest();
        $response2->assertJson(['code' => 0]);
    }
}
