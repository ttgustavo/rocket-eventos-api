<?php

namespace Tests\Feature\Controller\Api\Events;

use App\Domain\Repository\EventRepository;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\MockObject\MockObject;
use Tests\TestCase;

class DeleteEventControllerTest extends TestCase
{
    use RefreshDatabase;

    private EventRepository|MockObject $repository;

    protected function setUp(): void
    {
        parent::setUp();

        Carbon::setTestNow('2024-01-01T00:00:00Z');

        $this->repository = $this->getMockBuilder(EventRepository::class)->getMock();

        $this->app->bind(EventRepository::class, function() {
            return $this->repository;
        });
    }

    public function test_when_deleted_returns_status_code_no_content(): void
    {
        $this->repository->method('hasEventWithId')->willReturn(true);
        $this->repository->expects($this->once())->method('delete');

        $response = $this->deleteJson('/api/admin/events/1');

        $response->assertNoContent();
    }

    public function test_when_event_does_not_exists_returns_status_code_bad_request_with_code_1(): void
    {
        $this->repository->method('hasEventWithId')->willReturn(false);

        $response = parent::delete('/api/admin/events/1');

        $response->assertBadRequest();
        $response->assertJson(['code' => 1]);
    }

    // ---- Validation
    public function test_when_event_id_is_not_a_number_returns_status_code_bad_request_with_code_0_data(): void
    {
        $response = parent::delete("/api/admin/events/abc");

        $response->assertBadRequest();
        $response->assertJson(['code' => 0]);
    }
}
