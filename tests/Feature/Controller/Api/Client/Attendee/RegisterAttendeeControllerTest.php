<?php

namespace Tests\Feature\Controller\Api\Client\Attendee;

use App\Domain\Repository\AttendeeRepository;
use App\Domain\Repository\EventRepository;
use Illuminate\Support\Facades\Validator;
use Tests\Feature\AuthHelperTrait;
use Tests\TestCase;

class RegisterAttendeeControllerTest extends TestCase
{
    use AuthHelperTrait;

    private AttendeeRepository $attendeeRepository;
    private EventRepository $eventRepository;

    protected function setUp(): void
    {
        parent::setUp();

        $this->attendeeRepository = $this->getMockBuilder(AttendeeRepository::class)->getMock();
        $this->eventRepository = $this->getMockBuilder(EventRepository::class)->getMock();

        $this->app->instance(AttendeeRepository::class, $this->attendeeRepository);
        $this->app->instance(EventRepository::class, $this->eventRepository);
    }

    public function test_when_attendee_was_registered_returns_status_code_created(): void
    {
        $this->authAsUser();

        $this->eventRepository->method('hasEventWithId')->willReturn(true);
        $this->attendeeRepository->method('isAlreadyAnAttendee')->willReturn(false);

        $this->attendeeRepository->expects($this->once())->method('create');


        $response = $this->post('/api/events/1/attendees');


        $response->assertCreated();
    }

    public function test_when_attendee_was_already_registered_returns_status_code_ok(): void
    {
        $this->authAsUser();

        $this->eventRepository->method('hasEventWithId')->willReturn(true);
        $this->attendeeRepository->method('isAlreadyAnAttendee')->willReturn(true);

        $this->attendeeRepository->expects($this->never())->method('create');


        $response = $this->post('/api/events/1/attendees');


        $response->assertOk();
    }

    public function test_when_event_does_not_exists_returns_status_code_bad_request_with_code_1(): void
    {
        $this->authAsUser();

        $this->eventRepository->method('hasEventWithId')->willReturn(false);

        $this->attendeeRepository->expects($this->never())->method('create');
        $this->attendeeRepository->expects($this->never())->method('isAlreadyAnAttendee');


        $response = $this->post('/api/events/1/attendees');


        $response->assertBadRequest();
        $response->assertJson(['code' => 1]);
    }

    public function test_when_event_id_validation_fails_returns_status_code_bad_request_with_code_0(): void
    {
        $this->authAsUser();

        $this->attendeeRepository->expects($this->never())->method('create');
        $this->attendeeRepository->expects($this->never())->method('isAlreadyAnAttendee');

        $response = $this->post('/api/events/abcd234ef/attendees');

        $response->assertBadRequest();
        $response->assertJson(['code' => 0]);
    }
}
