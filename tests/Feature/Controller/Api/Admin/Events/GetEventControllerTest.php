<?php

namespace Tests\Feature\Controller\Api\Admin\Events;

use App\Domain\Model\EventModel;
use App\Domain\Model\EventStatus;
use App\Domain\Repository\EventRepository;
use Carbon\Carbon;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Component\HttpFoundation\Response;
use Tests\Feature\AuthHelperTrait;
use Tests\TestCase;

class GetEventControllerTest extends TestCase
{
    use AuthHelperTrait;

    private EventRepository|MockObject $repository;

    protected function setUp(): void
    {
        parent::setUp();

        $this->authAsAdminAndSuper();

        $this->repository = $this->getMockBuilder(EventRepository::class)->getMock();

        $this->app->instance(EventRepository::class, $this->repository);
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

    public function test_when_event_exists_with_user_admin_returns_status_code_ok_with_event_json(): void
    {
        $this->authAsAdmin();

        $slug = 'my-event';
        $event = $this->createEvent($slug);
        $this->repository->method('getBySlug')->willReturn($event);

        $response = $this->get("/api/admin/events/$slug");

        $response->assertOk();
        $response->assertJsonIsObject();
    }

    public function test_when_event_exists_with_user_super_returns_status_code_ok_with_event_json(): void
    {
        $this->authAsSuper();

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

    public function test_when_user_is_not_admin_or_super_returns_status_code_forbidden(): void
    {
        $slug = 'my-event';
        $this->authAsUser();

        $response = $this->get("/api/admin/events/$slug");

        $response->assertForbidden();
    }

    // ---- Validation
    public function test_when_slug_is_invalid_returns_status_code_bad_request_with_code_0(): void
    {
        $slug = 'my-event-';

        $response = $this->get("/api/admin/events/$slug");

        $response->assertStatus(Response::HTTP_BAD_REQUEST);
        $response->assertJson(['code' => 0]);
    }

    private function createEvent(string $name = '', string $slug = '', string $details = ''): EventModel
    {
        return new EventModel(
            1,
            $name,
            $slug,
            $details,
            Carbon::now(),
            Carbon::now()->addHour(),
            Carbon::now()->addDay(),
            Carbon::now(),
            Carbon::now(),
            EventStatus::Draft
        );
    }
}
