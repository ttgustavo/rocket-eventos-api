<?php

namespace Tests\Feature\Controller\Api\Client\Attendee;

use App\Domain\Model\EventModel;
use App\Domain\Model\EventStatus;
use App\Domain\Repository\AttendeeRepository;
use App\Domain\Repository\EventRepository;
use Carbon\Carbon;
use Tests\Feature\AuthHelperTrait;
use Tests\TestCase;

class SubscribeAttendeeControllerTest extends TestCase
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
        $event = $this->createEvent(EventStatus::SubscriptionsOpen);

        $this->authAsUser();

        $this->eventRepository->method('getById')->willReturn($event);
        $this->attendeeRepository->method('hasUserInEvent')->willReturn(false);

        $this->attendeeRepository->expects($this->once())->method('create');


        $response = $this->post('/api/events/1/attendees');


        $response->assertCreated();
    }

    public function test_when_attendee_was_already_registered_returns_status_code_ok(): void
    {
        $event = $this->createEvent(EventStatus::SubscriptionsOpen);

        $this->authAsUser();

        $this->eventRepository->method('getById')->willReturn($event);
        $this->attendeeRepository->method('hasUserInEvent')->willReturn(true);

        $this->attendeeRepository->expects($this->never())->method('create');


        $response = $this->post('/api/events/1/attendees');


        $response->assertOk();
    }

    public function test_when_event_does_not_exists_returns_status_code_bad_request_with_code_1(): void
    {
        $this->authAsUser();

        $this->eventRepository->method('getById')->willReturn(null);

        $this->attendeeRepository->expects($this->never())->method('create');
        $this->attendeeRepository->expects($this->never())->method('hasUserInEvent');


        $response = $this->post('/api/events/1/attendees');


        $response->assertBadRequest();
        $response->assertJson(['code' => 1]);
    }

    public function test_when_subscribing_to_event_where_subscriptions_not_opened_returns_status_code_bad_request_with_code_1(): void
    {
        $start = Carbon::now()->addHour();
        $end = $start->addHour();
        $event = $this->createEvent(EventStatus::SubscriptionsOpen, $start, $end);

        $this->authAsUser();

        $this->eventRepository->method('getById')->willReturn($event);
        $this->eventRepository->method('hasEventWithId')->willReturn(true);


        $response = $this->post('/api/events/1/attendees');


        $response->assertBadRequest();
        $response->assertJson(['code' => 1]);
    }

    public function test_when_event_id_validation_fails_returns_status_code_bad_request_with_code_0(): void
    {
        $this->authAsUser();

        $this->attendeeRepository->expects($this->never())->method('create');
        $this->attendeeRepository->expects($this->never())->method('hasUserInEvent');

        $response = $this->post('/api/events/abcd234ef/attendees');

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
