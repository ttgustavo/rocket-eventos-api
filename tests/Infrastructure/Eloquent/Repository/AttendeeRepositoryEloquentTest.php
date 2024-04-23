<?php

namespace Tests\Infrastructure\Eloquent\Repository;

use App\Domain\Repository\AttendeeRepository;
use App\Infrastructure\Eloquent\Models\Attendee;
use App\Infrastructure\Eloquent\Models\Event;
use App\Infrastructure\Eloquent\Models\User;
use App\Infrastructure\Eloquent\Repository\AttendeeRepositoryEloquent;
use Database\Factories\AttendeeFactory;
use Database\Factories\EventFactory;
use Database\Factories\UserFactory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AttendeeRepositoryEloquentTest extends TestCase
{
    use RefreshDatabase;

    private AttendeeRepository $repository;

    protected function setUp(): void
    {
        parent::setUp();

        $this->repository = new AttendeeRepositoryEloquent;
    }

    public function test_isAlreadyAnAttendee_returnsTrueWhenExists(): void
    {
        AttendeeFactory::new(['event_id' => 22, 'user_id' => 10])->create();

        $isAlreadyAnAttendee = $this->repository->hasUserInEvent(22, 10);

        $this->assertSame(true, $isAlreadyAnAttendee);
    }

    public function test_isAlreadyAnAttendee_returnsFalseWhenNotExists(): void
    {
        $isAlreadyAnAttendee = $this->repository->hasUserInEvent(10, 23);

        $this->assertSame(false, $isAlreadyAnAttendee);
    }

    public function test_create(): void
    {
        $event = EventFactory::new()->create();
        $user = UserFactory::new()->create();


        $this->repository->create($event->id, $user->id);


        $attendeeExists = Attendee::whereEventId($event->id)->whereUserId($user->id)->exists();
        $attendee = Attendee::whereEventId($event->id)->whereUserId($user->id)->first();

        /** @var User $userFromAttendee */
        $userFromAttendee = $attendee->user()->first();
        /** @var Event $eventFromAttendee */
        $eventFromAttendee = $attendee->event()->first();

        $this->assertSame(true, $attendeeExists);
        $this->assertSame($event->name, $eventFromAttendee->name);
        $this->assertSame($user->name, $userFromAttendee->name);
    }

    public function test_remove(): void
    {
        AttendeeFactory::new(['event_id' => 10, 'user_id' => 23])->create();

        $this->repository->remove(10, 23);

        $this->assertDatabaseMissing(Attendee::class, ['event_id' => 10, 'user_id' => 23]);
    }
}
