<?php

namespace Tests\Feature\Controller\Api\Client\Auth;

use App\Domain\Repository\UserRepository;
use App\Presenter\Http\Controllers\Api\Client\Auth\AuthControllerInputs;
use Database\Factories\UserFactory;
use Illuminate\Support\Facades\Auth;
use Laravel\Sanctum\NewAccessToken;
use Laravel\Sanctum\PersonalAccessToken;
use Tests\TestCase;

class AuthLoginControllerTest extends TestCase
{
    private UserRepository $repository;

    protected function setUp(): void
    {
        parent::setUp();

        $this->repository = $this->getMockBuilder(UserRepository::class)->getMock();
        $this->app->instance(UserRepository::class, $this->repository);
    }

    public function test_when_authenticated_returns_status_code_200_with_token(): void
    {
        $personalAccessToken = new PersonalAccessToken();
        $accessToken = new NewAccessToken($personalAccessToken, 'my_token');
        $user = UserFactory::new()->makeOne();
        $userMocked = \Mockery::mock($user)
            ->makePartial()
            ->shouldReceive('createToken')
            ->andReturn($accessToken)
            ->getMock();
        Auth::shouldReceive('once')->once()->andReturn(true);
        Auth::shouldReceive('user')->andReturn($userMocked);
        $this->repository->method('isBanned')->willReturn(false);

        $dataLogin = [
            AuthControllerInputs::FIELD_EMAIL => 'email@email.com',
            AuthControllerInputs::FIELD_PASSWORD => 'mypassword'
        ];
        $response = $this->postJson( '/api/login', $dataLogin);

        $response->assertOk();
        $response->assertJsonIsObject();
        $response->assertJson(['token' => 'my_token']);
    }

    public function test_when_authentication_fails_returns_status_code_400_with_code_1(): void
    {
        Auth::shouldReceive('once')->andReturn(false);

        $data = [
            AuthControllerInputs::FIELD_EMAIL => 'email@email.com',
            AuthControllerInputs::FIELD_PASSWORD => 'mypassword'
        ];
        $response = $this->postJson('/api/login', $data);

        $response->assertBadRequest();
        $response->assertJson(['code' => 1]);
    }

    public function test_when_authentication_fails_because_user_is_banned_returns_status_code_forbidden(): void
    {
        Auth::shouldReceive('once')->andReturn(true);
        $this->repository->method('isBanned')->willReturn(true);

        $data = [
            AuthControllerInputs::FIELD_EMAIL => 'email@email.com',
            AuthControllerInputs::FIELD_PASSWORD => 'mypassword'
        ];
        $response = $this->postJson('/api/login', $data);

        $response->assertForbidden();
    }

    // ---- Validation
    public function test_when_validation_email_fails_returns_status_code_400_with_code_0(): void
    {
        $data = [
            AuthControllerInputs::FIELD_NAME => 'Gustavo',
            AuthControllerInputs::FIELD_PASSWORD => 'password'
        ];

        $dataEmailMissing1 = array_merge($data, []);
        $dataEmailMissing2 = array_merge($data, [AuthControllerInputs::FIELD_EMAIL => '']);
        $dataEmailWrongFormat1 = array_merge($data, [AuthControllerInputs::FIELD_EMAIL => 'myemail']);
        $dataEmailWrongFormat2 = array_merge($data, [AuthControllerInputs::FIELD_EMAIL => '@email.com']);
        $dataEmailWrongFormat3 = array_merge($data, [AuthControllerInputs::FIELD_EMAIL => 'myemail@email']);
        $dataEmailWrongFormat4 = array_merge($data, [AuthControllerInputs::FIELD_EMAIL => 'myemail@email.']);

        $responseEmailMissing1 = $this->postJson('/api/login', $dataEmailMissing1);
        $responseEmailMissing2 = $this->postJson('/api/login', $dataEmailMissing2);
        $responseWrongFormat1 = $this->postJson('/api/login', $dataEmailWrongFormat1);
        $responseWrongFormat2 = $this->postJson('/api/login', $dataEmailWrongFormat2);
        $responseWrongFormat3 = $this->postJson('/api/login', $dataEmailWrongFormat3);
        $responseWrongFormat4 = $this->postJson('/api/login', $dataEmailWrongFormat4);

        $responseEmailMissing1->assertBadRequest();
        $responseEmailMissing1->assertJson(['code' => 0]);
        $responseEmailMissing2->assertBadRequest();
        $responseEmailMissing2->assertJson(['code' => 0]);
        $responseWrongFormat1->assertBadRequest();
        $responseWrongFormat1->assertJson(['code' => 0]);
        $responseWrongFormat2->assertBadRequest();
        $responseWrongFormat2->assertJson(['code' => 0]);
        $responseWrongFormat3->assertBadRequest();
        $responseWrongFormat3->assertJson(['code' => 0]);
        $responseWrongFormat4->assertBadRequest();
        $responseWrongFormat4->assertJson(['code' => 0]);
    }

    public function test_when_validation_password_fails_returns_status_code_400_with_code_0(): void
    {
        $data = [
            AuthControllerInputs::FIELD_NAME => 'Gustavo',
            AuthControllerInputs::FIELD_EMAIL => 'email@email.com'
        ];

        $dataPasswordMissing1 = array_merge($data, []);
        $dataPasswordMissing2 = array_merge($data, [AuthControllerInputs::FIELD_PASSWORD => '']);
        $dataPasswordWhitespace = array_merge($data, [AuthControllerInputs::FIELD_PASSWORD => '  ']);
        $dataPasswordMoreThanMaximum = array_merge($data, [AuthControllerInputs::FIELD_PASSWORD => 'aaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaa']);

        $responsePasswordMissing1 = $this->postJson('/api/login', $dataPasswordMissing1);
        $responsePasswordMissing2 = $this->postJson('/api/login', $dataPasswordMissing2);
        $responsePasswordWhitespace = $this->postJson('/api/login', $dataPasswordWhitespace);
        $responsePasswordMoreThanMaximum = $this->postJson('/api/login', $dataPasswordMoreThanMaximum);

        $responsePasswordMissing1->assertBadRequest();
        $responsePasswordMissing1->assertJson(['code' => 0]);
        $responsePasswordMissing2->assertBadRequest();
        $responsePasswordMissing2->assertJson(['code' => 0]);
        $responsePasswordWhitespace->assertBadRequest();
        $responsePasswordWhitespace->assertJson(['code' => 0]);
        $responsePasswordMoreThanMaximum->assertBadRequest();
        $responsePasswordMoreThanMaximum->assertJson(['code' => 0]);
    }
}
