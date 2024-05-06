<?php

namespace Tests\Feature\Controller\Api\Admin\Events;

use App\Domain\Model\EventModel;
use App\Domain\Model\EventStatus;
use App\Domain\Pagination\ModelPagination;
use App\Domain\Repository\EventRepository;
use Carbon\Carbon;
use Faker\Factory;
use Tests\Feature\AuthHelperTrait;
use Tests\TestCase;

class GetEventsAdminControllerTest extends TestCase
{
    use AuthHelperTrait;

    private EventRepository $repository;


    protected function setUp(): void
    {
        parent::setUp();

        $this->repository = $this->getMockBuilder(EventRepository::class)->getMock();

        $this->app->instance(EventRepository::class, $this->repository);
    }

    public function test_pagination_returnsStatusCodeOk()
    {
        $modelPagination = $this->createEventModelPagination(15, 'page1');
        $this->repository->method('list')->willReturn($modelPagination);
        $this->authAsAdmin();

        $response = $this->getJson('/api/admin/events');

        $response->assertOk();
        $response->assertJson(['totalItems' => 15, 'nextPage' => 'page1']);
    }

    public function test_paginationNotValid_returnsStatusCodeOk()
    {
        $modelPagination = $this->createEventModelPagination(0, );
        $this->repository->method('list')->willReturn($modelPagination);
        $this->authAsAdmin();

        $response = $this->getJson('/api/admin/events?page=50');

        $response->assertOk();
        $response->assertExactJson(['data' => [], 'totalItems' => 0]);
    }

    public function test_notAuthenticated_returnsStatusCodeUnauthorized()
    {
        $this->repository->expects($this->never())->method('list');

        $response = $this->getJson('/api/admin/events');

        $response->assertUnauthorized();
    }

    public function test_notAdmin_returnsStatusCodeForbidden()
    {
        $this->repository->expects($this->never())->method('list');
        $this->authAsUser();

        $response = $this->getJson('/api/admin/events');

        $response->assertForbidden();
    }

    public function test_pageParamNotValid_returnsStatusCodeBadRequest()
    {
        $this->authAsAdmin();
        $this->repository->expects($this->never())->method('list');

        $response = $this->getJson('/api/admin/events?page=1.1');

        $response->assertBadRequest();
        $response->assertJson(['code' => 0]);
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
