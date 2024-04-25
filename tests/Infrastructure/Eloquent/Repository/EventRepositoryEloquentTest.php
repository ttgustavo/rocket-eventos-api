<?php

namespace Tests\Infrastructure\Eloquent\Repository;

use App\Domain\Model\EventStatus;
use App\Domain\Repository\EventRepository;
use App\Infrastructure\Eloquent\Models\Event;
use App\Infrastructure\Eloquent\Repository\EventRepositoryEloquent;
use Database\Factories\EventFactory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EventRepositoryEloquentTest extends TestCase
{
    use RefreshDatabase;

    private EventRepository $repository;

    protected function setUp(): void
    {
        parent::setUp();

        $this->repository = new EventRepositoryEloquent;
    }

    public function test_hasEventWithId_returnsTrueWhenExists(): void
    {
        $this->createEvent();

        $hasEvent = $this->repository->hasEventWithId(1);

        $this->assertSame(true, $hasEvent);
    }

    public function test_hasEventWithSlug_returnsTrueWhenExists(): void
    {
        $this->createEvent(slug: 'my-event');

        $hasEvent = $this->repository->hasEventWithSlug('my-event');

        $this->assertSame(true, $hasEvent);
    }

    public function test_pagination(): void
    {
        EventFactory::new(['status' => EventStatus::Created])->createMany(25);
        EventFactory::new(['status' => EventStatus::SubscriptionsOpen])->createMany(25);


        $page1 = $this->repository->getAll();
        $page2 = $this->repository->getAll(2);
        $page3 = $this->repository->getAll(3);
        $page4 = $this->repository->getAll(4);


        // Page 1
        $this->assertSame(15, count($page1->items));
        $this->assertSame(50, $page1->totalItems);
        $this->assertNotNull($page1->nextPage);
        $this->assertNull($page1->previousPage);

        // Page 2
        $this->assertSame(15, count($page2->items));
        $this->assertSame(50, $page2->totalItems);
        $this->assertNotNull($page2->nextPage);
        $this->assertNotNull($page2->previousPage);

        // Page 3
        $this->assertSame(15, count($page3->items));
        $this->assertSame(50, $page3->totalItems);
        $this->assertNotNull($page3->nextPage);
        $this->assertNotNull($page3->previousPage);

        // Page 4
        $this->assertSame(5, count($page4->items));
        $this->assertSame(50, $page4->totalItems);
        $this->assertNull($page4->nextPage);
        $this->assertNotNull($page4->previousPage);
    }

    public function test_pagination_includes_only_events_that_are_created_and_opened(): void
    {
        EventFactory::new(['status' => EventStatus::Draft])->createMany(3);
        EventFactory::new(['status' => EventStatus::Done])->createMany(1);
        EventFactory::new(['status' => EventStatus::Created])->createMany(4);
        EventFactory::new(['status' => EventStatus::SubscriptionsOpen])->createMany(4);

        $pagination = $this->repository->getAll();

        $this->assertSame(8, ($pagination->totalItems));
    }

    public function test_getBySlug_returnsModelWhenExists(): void
    {
        $this->createEvent(slug: 'my-event');

        $event = $this->repository->getBySlug('my-event');

        $this->assertNotNull($event);
    }

    public function test_getBySlug_returnsNullWhenNotExists(): void
    {
        $event = $this->repository->getBySlug('my-event');

        $this->assertNull($event);
    }

    public function test_create_isCreatedInDatabase(): void
    {
        $this->repository->create(
            'My Event',
            'my-event',
            'My event details.',
            '2024-01-01 00:00:00',
            '2024-01-01 01:00:00',
            '2024-01-01 02:00:00',
        );

        $this->assertDatabaseCount(Event::class, 1);
    }

    public function test_update_isUpdatedInDatabase(): void
    {
        $this->createEvent(777);
        $data = ['slug' => 'my-event'];

        $event = $this->repository->update(777, $data);

        $this->assertDatabaseHas(Event::class, ['slug' => 'my-event']);
    }

    public function test_update_returnsModelWhenChanged(): void
    {
        $this->createEvent(777);
        $data = ['slug' => 'my-event'];

        $event = $this->repository->update(777, $data);

        $this->assertNotNull($event);
        $this->assertDatabaseHas(Event::class, ['slug' => 'my-event']);
    }

    public function test_update_returnsNullWhenNotChanged(): void
    {
        $this->createEvent(id: 777, slug: 'my-event');
        $data = ['slug' => 'my-event'];

        $event = $this->repository->update(777, $data);

        $this->assertNull($event);
        $this->assertDatabaseHas(Event::class, ['slug' => 'my-event']);
    }

    public function test_delete_isDeletedFromDatabase(): void
    {
        $this->createEvent(555);

        $this->repository->delete(555);

        $this->assertDatabaseEmpty(Event::class);
    }

    private function createEvent(int $id = 1, string $name = '', string $slug = ''): Event
    {
        $data = [];

        if ($id > 1) $data['id'] = $id;
        if (empty($name) === false) $data['name'] = $name;
        if (empty($slug) === false) $data['slug'] = $slug;

        return EventFactory::new($data)->create();
    }
}
