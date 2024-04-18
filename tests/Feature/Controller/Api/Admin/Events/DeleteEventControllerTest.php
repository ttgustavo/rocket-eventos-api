<?php

namespace Tests\Feature\Controller\Api\Admin\Events;

use App\Domain\Repository\EventRepository;
use Carbon\Carbon;
use PHPUnit\Framework\MockObject\MockObject;
use Tests\Feature\AuthHelperTrait;
use Tests\TestCase;

class DeleteEventControllerTest extends TestCase
{
    use AuthHelperTrait;

    private EventRepository|MockObject $repository;

    protected function setUp(): void
    {
        parent::setUp();

        Carbon::setTestNow('2024-01-01T00:00:00Z');

        $this->authAsAdminAndSuper();

        $this->repository = $this->getMockBuilder(EventRepository::class)->getMock();

        $this->app->instance(EventRepository::class, $this->repository);
    }

    public function test_when_deleted_returns_status_code_no_content(): void
    {
        $this->repository->method('hasEventWithId')->willReturn(true);
        $this->repository->expects($this->once())->method('delete');

        $response = $this->deleteJson('/api/admin/events/1');

        $response->assertNoContent();
    }

    public function test_when_is_deleted_by_admin_user_returns_status_code_no_content(): void
    {
        $this->authAsAdmin();
        $this->repository->method('hasEventWithId')->willReturn(true);
        $this->repository->expects($this->once())->method('delete');

        $response = $this->deleteJson('/api/admin/events/1');

        $response->assertNoContent();
    }

    public function test_when_is_deleted_by_super_user_returns_status_code_no_content(): void
    {
        $this->authAsSuper();
        $this->repository->method('hasEventWithId')->willReturn(true);
        $this->repository->expects($this->once())->method('delete');

        $response = $this->deleteJson('/api/admin/events/1');

        $response->assertNoContent();
    }

    public function test_when_event_does_not_exists_returns_status_code_bad_request_with_code_1(): void
    {
        $this->repository->method('hasEventWithId')->willReturn(false);
        $this->repository->expects($this->never())->method('delete');

        $response = parent::delete('/api/admin/events/1');

        $response->assertBadRequest();
        $response->assertJson(['code' => 1]);
    }

    public function test_when_is_deleted_by_user_not_admin_and_not_super_returns_status_code_no_content(): void
    {
        $this->authAsUser();
        $this->repository->method('hasEventWithId')->willReturn(true);
        $this->repository->expects($this->never())->method('delete');

        $response = $this->deleteJson('/api/admin/events/1');

        $response->assertForbidden();
    }

    // ---- Validation
    public function test_when_event_id_is_not_a_number_returns_status_code_bad_request_with_code_0_data(): void
    {
        $this->repository->expects($this->never())->method('delete');

        $response = parent::delete("/api/admin/events/abc");

        $response->assertBadRequest();
        $response->assertJson(['code' => 0]);
    }
}
