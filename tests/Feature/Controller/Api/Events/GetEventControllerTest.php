<?php

namespace Tests\Feature\Controller\Api\Events;

use App\Domain\Model\EventModel;
use App\Domain\Repository\EventRepository;
use Carbon\Carbon;
use Database\Factories\EventFactory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Component\HttpFoundation\Response;
use Tests\TestCase;

class GetEventControllerTest extends TestCase
{
    use RefreshDatabase;

    private EventRepository|MockObject $repository;

    protected function setUp(): void
    {
        parent::setUp();

        Carbon::setTestNow('2024-01-01T15:00:00Z');

        $this->repository = $this->getMockBuilder(EventRepository::class)->getMock();

        $this->app->bind(EventRepository::class, function () {
            return $this->repository;
        });
    }

    public function test_when_event_exists_returns_status_code_ok_with_event_json(): void
    {
        $slug = 'my-event';
        $event = $this->createEvent($slug);
        $this->repository->method('getBySlug')->willReturn($event);

        $response = $this->get("/api/admin/events/$slug");

        $response->assertOk();
        $response->assertJsonIsObject();
    }

    public function test_when_event_does_not_exists_returns_status_code_bad_request_with_code_1(): void
    {
        $slug = 'my-event';

        $response = $this->get("/api/admin/events/$slug");

        $response->assertBadRequest();
        $response->assertJson(['code' => 1]);
    }

    public function test_when_slug_is_invalid_returns_status_code_bad_request_with_code_0(): void
    {
        $slug = 'my-event-';

        $response = $this->get("/api/admin/events/$slug");

        $response->assertStatus(Response::HTTP_BAD_REQUEST);
        $response->assertJson(['code' => 0]);
    }

    private function createEvent(string $slug): EventModel
    {
        return EventFactory::new(['slug' => $slug])->create()->toDomainModel();
    }
}
