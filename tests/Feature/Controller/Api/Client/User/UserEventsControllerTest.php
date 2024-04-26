<?php

namespace Tests\Feature\Controller\Api\Client\User;

use App\Domain\Model\AttendeeStatus;
use App\Domain\Model\EventModel;
use App\Domain\Model\EventStatus;
use App\Domain\Model\UserEventAttendee;
use App\Domain\Repository\AttendeeRepository;
use Carbon\Carbon;
use Tests\Feature\AuthHelperTrait;
use Tests\TestCase;

class UserEventsControllerTest extends TestCase
{
    use AuthHelperTrait;

    private AttendeeRepository $repository;

    protected function setUp(): void
    {
        parent::setUp();

        $this->repository = $this->getMockBuilder(AttendeeRepository::class)->getMock();

        $this->app->instance(AttendeeRepository::class, $this->repository);
    }

    public function test_whenUserIsSubscribedToEvent_returnsStatusCodeOkWithEventDataAndAttendeeStatus(): void
    {
        $this->authAsUser();

        $event = new EventModel(1, '', '', '', Carbon::now(), Carbon::now(), Carbon::now(), Carbon::now(), Carbon::now(), EventStatus::Created);
        $attendeeStatus = AttendeeStatus::Subscribed;
        $data[] = new UserEventAttendee($event, $attendeeStatus);

        $this->repository->method('getEventsFromUser')->willReturn($data);


        $response = $this->getJson('/api/users/events');


        $response->assertOk();
        $response->assertJsonIsArray();
    }
}
