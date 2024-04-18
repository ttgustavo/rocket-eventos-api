<?php

namespace Tests\Feature\Controller\Api\Client\Auth;

use App\Domain\Model\UserModel;
use App\Domain\Model\UserStatus;
use App\Domain\Repository\UserRepository;
use App\Presenter\Http\Controllers\Api\Client\Auth\AuthControllerInputs;
use Carbon\Carbon;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Component\HttpFoundation\Response;
use Tests\TestCase;

class AuthRegisterControllerTest extends TestCase
{
    private UserRepository|MockObject $repository;

    protected function setUp(): void
    {
        parent::setUp();

        $this->repository = $this->getMockBuilder(UserRepository::class)->getMock();

        $this->app->bind(UserRepository::class, function() {
            return $this->repository;
        });
    }

    public function test_when_registered_returns_status_code_created_with_user_json(): void
    {
        $name = 'Gustavo';
        $email = 'myemail@email.com';
        $password = 'mypassword';
        $user = $this->createUser($name, $email);

        $data = [
            AuthControllerInputs::FIELD_NAME => $name,
            AuthControllerInputs::FIELD_EMAIL => $email,
            AuthControllerInputs::FIELD_PASSWORD => $password
        ];
        $this->repository->method('hasEmailRegistered')->willReturn(false);
        $this->repository->method('insert')->willReturn($user);

        $response = $this->postJson( '/api/register', $data);
        $response->assertCreated();
        $response->assertJsonIsObject();
        $response->assertJsonFragment(['name' => $name]);
    }

    public function test_when_email_is_already_registered_returns_bad_request_with_code_1(): void
    {
        $this->repository->method('hasEmailRegistered')->willReturn(true);

        $data = [
            AuthControllerInputs::FIELD_NAME => 'Gustavo',
            AuthControllerInputs::FIELD_EMAIL => 'email@email.com',
            AuthControllerInputs::FIELD_PASSWORD => '12345678'
        ];
        $response = $this->postJson('/api/register', $data);

        $response->assertBadRequest();
        $response->assertJson(['code' => 1]);
    }

    // ---- Validation
    public function test_when_validation_name_fails_returns_bad_request_with_code_0(): void
    {
        $data = ['email' => 'myemail@email.com', 'password' => 'password'];

        $dataNameMissing1 = array_merge($data, []);
        $dataNameMissing2 = array_merge($data, ['name' => '']);
        $dataNameWhitespace1 = array_merge($data, ['name' => '   ']);
        $dataNameWhitespace2 = array_merge($data, ['name' => '  a']);
        $dataNameWhitespace3 = array_merge($data, ['name' => 'a  ']);
        $dataNameLessThanMinimum = array_merge($data, ['name' => 'Ab']);
        $dataNameMoreThanMaximum = array_merge($data, ['name' => 'aaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaa']);

        $responseNameMissing1 = $this->postJson('/api/register', $dataNameMissing1);
        $responseNameMissing2 = $this->postJson('/api/register', $dataNameMissing2);
        $responseNameWhitespace1 = $this->postJson('/api/register', $dataNameWhitespace1);
        $responseNameWhitespace2 = $this->postJson('/api/register', $dataNameWhitespace2);
        $responseNameWhitespace3 = $this->postJson('/api/register', $dataNameWhitespace3);
        $responseNameLessThanMinimum = $this->postJson('/api/register', $dataNameLessThanMinimum);
        $responseNameMoreThanMaximum = $this->postJson('/api/register', $dataNameMoreThanMaximum);

        $responseNameMissing1->assertStatus(Response::HTTP_BAD_REQUEST);
        $responseNameMissing1->assertJson(['code' => 0]);
        $responseNameMissing2->assertStatus(Response::HTTP_BAD_REQUEST);
        $responseNameMissing2->assertJson(['code' => 0]);
        $responseNameWhitespace1->assertStatus(Response::HTTP_BAD_REQUEST);
        $responseNameWhitespace1->assertJson(['code' => 0]);
        $responseNameWhitespace2->assertStatus(Response::HTTP_BAD_REQUEST);
        $responseNameWhitespace2->assertJson(['code' => 0]);
        $responseNameWhitespace3->assertStatus(Response::HTTP_BAD_REQUEST);
        $responseNameWhitespace3->assertJson(['code' => 0]);
        $responseNameLessThanMinimum->assertStatus(Response::HTTP_BAD_REQUEST);
        $responseNameLessThanMinimum->assertJson(['code' => 0]);
        $responseNameMoreThanMaximum->assertStatus(Response::HTTP_BAD_REQUEST);
        $responseNameMoreThanMaximum->assertJson(['code' => 0]);
    }

    public function test_when_validation_email_fails_returns_bad_request_with_code_0(): void
    {
        $data = ['name' => 'Gustavo', 'password' => 'password'];

        $dataEmailMissing1 = array_merge($data, []);
        $dataEmailMissing2 = array_merge($data, ['email' => '']);
        $dataEmailWrongFormat1 = array_merge($data, ['email' => 'myemail']);
        $dataEmailWrongFormat2 = array_merge($data, ['email' => '@email.com']);
        $dataEmailWrongFormat3 = array_merge($data, ['email' => 'myemail@email']);
        $dataEmailWrongFormat4 = array_merge($data, ['email' => 'myemail@email.']);

        $responseEmailMissing1 = $this->postJson('/api/register', $dataEmailMissing1);
        $responseEmailMissing2 = $this->postJson('/api/register', $dataEmailMissing2);
        $responseWrongFormat1 = $this->postJson('/api/register', $dataEmailWrongFormat1);
        $responseWrongFormat2 = $this->postJson('/api/register', $dataEmailWrongFormat2);
        $responseWrongFormat3 = $this->postJson('/api/register', $dataEmailWrongFormat3);
        $responseWrongFormat4 = $this->postJson('/api/register', $dataEmailWrongFormat4);

        $responseEmailMissing1->assertStatus(Response::HTTP_BAD_REQUEST);
        $responseEmailMissing1->assertJson(['code' => 0]);
        $responseEmailMissing2->assertStatus(Response::HTTP_BAD_REQUEST);
        $responseEmailMissing2->assertJson(['code' => 0]);
        $responseWrongFormat1->assertStatus(Response::HTTP_BAD_REQUEST);
        $responseWrongFormat1->assertJson(['code' => 0]);
        $responseWrongFormat2->assertStatus(Response::HTTP_BAD_REQUEST);
        $responseWrongFormat2->assertJson(['code' => 0]);
        $responseWrongFormat3->assertStatus(Response::HTTP_BAD_REQUEST);
        $responseWrongFormat3->assertJson(['code' => 0]);
        $responseWrongFormat4->assertStatus(Response::HTTP_BAD_REQUEST);
        $responseWrongFormat4->assertJson(['code' => 0]);
    }

    public function test_when_validation_password_fails_returns_bad_request_with_code_0(): void
    {
        $data = ['name' => 'Gustavo', 'email' => 'email@email.com'];

        $dataPasswordMissing1 = array_merge($data, []);
        $dataPasswordMissing2 = array_merge($data, ['password' => '']);
        $dataPasswordWhitespace = array_merge($data, ['password' => '        ']);
        $dataPasswordLessThanMinimum = array_merge($data, ['password' => 'aaaaaaa']);
        $dataPasswordMoreThanMaximum = array_merge($data, ['password' => 'aaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaa']);

        $responsePasswordMissing1 = $this->postJson('/api/register', $dataPasswordMissing1);
        $responsePasswordMissing2 = $this->postJson('/api/register', $dataPasswordMissing2);
        $responsePasswordWhitespace = $this->postJson('/api/register', $dataPasswordWhitespace);
        $responsePasswordLessThanMinimum = $this->postJson('/api/register', $dataPasswordLessThanMinimum);
        $responsePasswordMoreThanMaximum = $this->postJson('/api/register', $dataPasswordMoreThanMaximum);

        $responsePasswordMissing1->assertStatus(Response::HTTP_BAD_REQUEST);
        $responsePasswordMissing1->assertJson(['code' => 0]);
        $responsePasswordMissing2->assertStatus(Response::HTTP_BAD_REQUEST);
        $responsePasswordMissing2->assertJson(['code' => 0]);
        $responsePasswordWhitespace->assertStatus(Response::HTTP_BAD_REQUEST);
        $responsePasswordWhitespace->assertJson(['code' => 0]);
        $responsePasswordLessThanMinimum->assertStatus(Response::HTTP_BAD_REQUEST);
        $responsePasswordLessThanMinimum->assertJson(['code' => 0]);
        $responsePasswordMoreThanMaximum->assertStatus(Response::HTTP_BAD_REQUEST);
        $responsePasswordMoreThanMaximum->assertJson(['code' => 0]);
    }

    private function createUser(string $name = '', string $email = ''): UserModel
    {
        $date = Carbon::now();
        return new UserModel(1, $name, $email, $date, $date, UserStatus::Registered);
    }
}
