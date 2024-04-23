<?php

namespace Tests\Feature\Controller\Api\Client\Attendee;

use App\Domain\Model\EventModel;
use App\Domain\Model\EventStatus;
use App\Domain\Repository\AttendeeRepository;
use App\Domain\Repository\EventRepository;
use Carbon\Carbon;
use Tests\Feature\AuthHelperTrait;
use Tests\TestCase;

class RemoveAttendeeControllerTest extends TestCase
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

    public function test_when_attendee_is_removed_returns_status_code_no_content(): void
    {
        $event = $this->createEvent(EventStatus::SubscriptionsOpen);

        $this->authAsUser();

        $this->eventRepository->method('getById')->willReturn($event);
        $this->attendeeRepository->method('hasUserInEvent')->willReturn(true);

        $this->attendeeRepository->expects($this->once())->method('remove');


        $response = $this->delete('/api/events/1/attendees');


        $response->assertNoContent();
    }

    public function test_when_attendee_is_not_registered_returns_status_code_bad_request_with_code_1(): void
    {
        $event = $this->createEvent(EventStatus::SubscriptionsOpen);

        $this->authAsUser();

        $this->eventRepository->method('getById')->willReturn($event);
        $this->attendeeRepository->method('hasUserInEvent')->willReturn(false);

        $this->attendeeRepository->expects($this->never())->method('create');


        $response = $this->delete('/api/events/1/attendees');


        $response->assertBadRequest();
    }

    public function test_when_event_does_not_exists_returns_status_code_bad_request_with_code_1(): void
    {
        $this->authAsUser();

        $this->eventRepository->method('getById')->willReturn(null);

        $this->attendeeRepository->expects($this->never())->method('remove');
        $this->attendeeRepository->expects($this->never())->method('hasUserInEvent');


        $response = $this->delete('/api/events/1/attendees');


        $response->assertBadRequest();
        $response->assertJson(['code' => 1]);
    }

    public function test_when_subscribing_to_event_where_event_is_done_returns_status_code_bad_request_with_code_1(): void
    {
        $start = Carbon::now()->addHour();
        $end = $start->addHour();
        $event = $this->createEvent(EventStatus::Done, $start, $end);

        $this->authAsUser();

        $this->eventRepository->method('getById')->willReturn($event);
        $this->eventRepository->method('hasEventWithId')->willReturn(true);


        $response = $this->delete('/api/events/1/attendees');


        $response->assertBadRequest();
        $response->assertJson(['code' => 1]);
    }

    public function test_when_event_id_validation_fails_returns_status_code_bad_request_with_code_0(): void
    {
        $this->authAsUser();

        $this->attendeeRepository->expects($this->never())->method('remove');
        $this->attendeeRepository->expects($this->never())->method('hasUserInEvent');

        $response = $this->delete('/api/events/abcd234ef/attendees');

        $response->assertBadRequest();
        $response->assertJson(['code' => 0]);
    }

    private function createEvent(
        EventStatus $status,
        ?Carbon $subscriptionStart = null,
        ?Carbon $subscriptionEnd = null
    ): EventModel
    {
        return new EventModel(
            1,
            'My Event',
            'my-event',
            '',
            $subscriptionStart ?? Carbon::now(),
            $subscriptionEnd ?? Carbon::now()->addHour(),
            Carbon::now(),
            Carbon::now(),
            Carbon::now(),
            $status
        );
    }
}
