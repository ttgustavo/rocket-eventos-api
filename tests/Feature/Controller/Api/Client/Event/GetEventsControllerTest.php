<?php

namespace Tests\Feature\Controller\Api\Client\Event;

use App\Domain\Model\EventModel;
use App\Domain\Model\EventStatus;
use App\Domain\Pagination\ModelPagination;
use App\Domain\Repository\EventRepository;
use Carbon\Carbon;
use Faker\Factory;
use Tests\Feature\AuthHelperTrait;
use Tests\TestCase;

class GetEventsControllerTest extends TestCase
{
    use AuthHelperTrait;

    private EventRepository $repository;

    protected function setUp(): void
    {
        parent::setUp();

        $this->repository = $this->getMockBuilder(EventRepository::class)->getMock();

        $this->app->instance(EventRepository::class, $this->repository);

        $this->authAsUser();
    }

    public function test_when_is_first_page_returns_status_code_ok_with_next_page(): void
    {
        $pagination = $this->createEventModelPagination(15, 'next');

        $this->repository->method('getAll')->willReturn($pagination);


        $response = $this->getJson('/api/events');

        $response->assertOk();
        $response->assertJsonStructure(['data', 'totalItems', 'nextPage']);
        $response->assertJsonMissing(['previousPage' => 'previous']);
        $response->assertJsonFragment([
            'totalItems' => 15,
            'nextPage' => 'next'
        ]);
    }

    public function test_when_is_middle_page_returns_status_code_ok_with_next_page_and_previous_page(): void
    {
        $pagination = $this->createEventModelPagination(15, 'next', 'previous');

        $this->repository->method('getAll')->willReturn($pagination);


        $response = $this->getJson('/api/events');

        $response->assertOk();
        $response->assertJsonStructure(['data', 'totalItems', 'nextPage', 'previousPage']);
        $response->assertJsonMissing(['']);
        $response->assertJsonFragment([
            'totalItems' => 15,
            'nextPage' => 'next',
            'previousPage' => 'previous'
        ]);
    }

    public function test_when_is_last_page_returns_status_code_ok_with_previous_page(): void
    {
        $pagination = $this->createEventModelPagination(15, null, 'previous');

        $this->repository->method('getAll')->willReturn($pagination);


        $response = $this->getJson('/api/events');

        $response->assertOk();
        $response->assertJsonStructure(['data', 'totalItems', 'previousPage']);
        $response->assertJsonMissing(['nextPage' => 'next']);
        $response->assertJsonFragment([
            'totalItems' => 15,
            'previousPage' => 'previous'
        ]);
    }

    public function test_when_empty_returns_status_code_ok_with_only_data_and_total_items(): void
    {
        $pagination = $this->createEventModelPagination(0);

        $this->repository->method('getAll')->willReturn($pagination);


        $response = $this->getJson('/api/events');

        $response->assertOk();
        $response->assertJsonStructure(['data', 'totalItems']);
        $response->assertJsonMissing(['nextPage' => 'next', 'previousPage' => 'previous']);
        $response->assertJsonFragment(['totalItems' => 0]);
    }

    // ---- Validation
    public function test_when_page_validation_fails_returns_status_code_bad_request_with_code_0(): void
    {
        $response1 = $this->getJson('/api/events?page=0');
        $response2 = $this->getJson('/api/events?page=-2');
        $response3 = $this->getJson('/api/events?page=a');

        $response1->assertBadRequest();
        $response1->assertJson(['code' => 0]);
        $response2->assertBadRequest();
        $response1->assertJson(['code' => 0]);
        $response3->assertBadRequest();
        $response1->assertJson(['code' => 0]);
    }

    private function createEventModelPagination(
        int $total,
        ?string $nextPage = null,
        ?string $previousPage = null
    ): ModelPagination
    {
        $faker = Factory::create();
        $data = [];

        for ($i = 0; $i < $total; $i ++) {
            $id = $faker->numberBetween(100000, 999999);
            $name = $faker->name();
            $slug = $faker->slug(3);
            $details = $faker->text();
            $subscriptionStartAt = Carbon::now();
            $subscriptionEndAt = Carbon::now();
            $presentationAt = Carbon::now();
            $createdAt = Carbon::now();
            $updatedAt = Carbon::now();
            $status = EventStatus::Created;

            $event = new EventModel($id,
                $name,
                $slug,
                $details,
                $subscriptionStartAt,
                $subscriptionEndAt,
                $presentationAt,
                $createdAt,
                $updatedAt,
                $status
            );

            $data[] = $event;
        }

        return new ModelPagination($data, $total, $nextPage, $previousPage);
    }
}
