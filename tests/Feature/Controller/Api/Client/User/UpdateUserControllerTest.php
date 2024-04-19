<?php

namespace Tests\Presenter\Http\Controllers\Api\Client\User;

use App\Domain\Model\UserModel;
use App\Domain\Model\UserStatus;
use App\Domain\Repository\UserRepository;
use App\Presenter\Http\Controllers\Api\Client\User\UpdateUserValidation;
use Carbon\Carbon;
use Database\Factories\UserFactory;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use PHPUnit\Framework\MockObject\MockObject;
use Tests\Feature\AuthHelperTrait;
use Tests\TestCase;

class UpdateUserControllerTest extends TestCase
{
    use AuthHelperTrait;

    private UserRepository|MockObject $repository;

    protected function setUp(): void
    {
        parent::setUp();

        $this->repository = $this->getMockBuilder(UserRepository::class)->getMock();

        $this->app->instance(UserRepository::class, $this->repository);
    }

    public function test_when_updating_name_and_email_returns_status_code_ok_with_user_json(): void
    {
        $user = $this->createUser('Gustavo', 'email@email.com');

        $this->repository->method('update')->willReturn($user);
        $this->authAsUser();


        $data = [
            'name' => 'Gustavo',
            'email' => 'email@email.com'
        ];
        $response = $this->patchJson('/api/users', $data);


        $response->assertOk();
        $response->assertJson(['name' => 'Gustavo', 'email' => 'email@email.com']);
    }

    public function test_when_updating_password_returns_status_code_ok_with_user_json(): void
    {
        $oldPasswordHashed = Hash::make('12345678');
        $user = $this->createUser('Gustavo', 'email@email.com');

        $this->repository->method('getPasswordFromUser')->willReturn($oldPasswordHashed);
        $this->repository->method('update')->willReturn($user);
        $this->authAsUser();


        $data = [
            'old_password' => '12345678',
            'new_password' => '12345678',
        ];
        $response = $this->patchJson('/api/users', $data);


        $response->assertOk();
        $response->assertJson(['name' => 'Gustavo', 'email' => 'email@email.com']);
    }

    public function test_when_updating_and_not_authenticated_returns_status_code_ok_with_user_json(): void
    {
        $data = ['name' => 'Gustavo', 'email' => 'email@email.com'];
        $response = $this->patchJson('/api/users', $data);

        $response->assertUnauthorized();
    }

    public function test_when_updating_validation_must_be_called(): void
    {
        $data = ['name' => 'Gustavo', 'email' => 'email@email.com'];
        $user = $this->createUser('Gustavo', 'email@email.com');
        $validator =  UpdateUserValidation::validate($data);

        $this->authAsUser();
        $this->repository->method('update')->willReturn($user);
        Validator::shouldReceive('make')->once()->andReturn($validator);


        $response = $this->patchJson('/api/users', $data);


        $response->assertOk();
        $response->assertJson(['name' => 'Gustavo', 'email' => 'email@email.com']);
    }

    public function test_when_updating_with_wrong_old_password_returns_status_code_bad_request_with_code_1(): void
    {
        $oldPasswordHashed = Hash::make('123456789');
        $user = $this->createUser('Gustavo', 'email@email.com');

        $this->repository->method('getPasswordFromUser')->willReturn($oldPasswordHashed);
        $this->repository->method('update')->willReturn($user);
        $this->authAsUser();


        $data = ['old_password' => '12345678', 'new_password' => '12345678'];
        $response = $this->patchJson('/api/users', $data);


        $response->assertBadRequest();
        $response->assertJson(['code' => 1]);
    }

    private function createUser(string $name = '', string $email = ''): UserModel
    {
        return new UserModel(1, $name, $email, Carbon::now(), Carbon::now(),  UserStatus::Registered);
    }
}
