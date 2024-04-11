<?php

namespace Tests\Feature\Controller;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Symfony\Component\HttpFoundation\Response;
use Tests\TestCase;

class AuthRegisterControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_returns_status_code_201_with_user_data_when_registered(): void
    {
        $data = [
            'name' => 'Gustavo',
            'email' => 'myemail@email.com',
            'password' => 'mypassword'
        ];

        $response = $this->postJson( '/register', $data);
        $response->assertStatus(Response::HTTP_CREATED);
    }

    public function test_returns_status_code_400_when_email_is_already_registered_with_code_1(): void
    {
        $data = [
            'name' => 'Gustavo',
            'email' => 'myemail@email.com',
            'password' => 'mypassword'
        ];

        $responseCreated = $this->postJson('/register', $data);
        $responseCreated->assertStatus(Response::HTTP_CREATED);

        $response = $this->postJson('/register', $data);
        $response->assertStatus(Response::HTTP_BAD_REQUEST);
        $response->assertJson([
            'code' => 1,
            'message' => 'E-mail already in use.'
        ]);
    }

    public function test_returns_bad_request_when_validation_name_fails_with_code_0(): void
    {
        $data = ['email' => 'myemail@email.com', 'password' => 'password'];

        $dataNameMissing1 = array_merge($data, []);
        $dataNameMissing2 = array_merge($data, ['name' => '']);
        $dataNameWhitespace1 = array_merge($data, ['name' => '   ']);
        $dataNameWhitespace2 = array_merge($data, ['name' => '  a']);
        $dataNameWhitespace3 = array_merge($data, ['name' => 'a  ']);
        $dataNameLessThanMinimum = array_merge($data, ['name' => 'Ab']);
        $dataNameMoreThanMaximum = array_merge($data, ['name' => 'aaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaa']);

        $responseNameMissing1 = $this->postJson('/register', $dataNameMissing1);
        $responseNameMissing2 = $this->postJson('/register', $dataNameMissing2);
        $responseNameWhitespace1 = $this->postJson('/register', $dataNameWhitespace1);
        $responseNameWhitespace2 = $this->postJson('/register', $dataNameWhitespace2);
        $responseNameWhitespace3 = $this->postJson('/register', $dataNameWhitespace3);
        $responseNameLessThanMinimum = $this->postJson('/register', $dataNameLessThanMinimum);
        $responseNameMoreThanMaximum = $this->postJson('/register', $dataNameMoreThanMaximum);

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

    public function test_returns_bad_request_when_validation_email_fails_with_code_0(): void
    {
        $data = ['name' => 'Gustavo', 'password' => 'password'];

        $dataEmailMissing1 = array_merge($data, []);
        $dataEmailMissing2 = array_merge($data, ['email' => '']);
        $dataEmailWrongFormat1 = array_merge($data, ['email' => 'myemail']);
        $dataEmailWrongFormat2 = array_merge($data, ['email' => '@email.com']);
        $dataEmailWrongFormat3 = array_merge($data, ['email' => 'myemail@email']);
        $dataEmailWrongFormat4 = array_merge($data, ['email' => 'myemail@email.']);

        $responseEmailMissing1 = $this->postJson('/register', $dataEmailMissing1);
        $responseEmailMissing2 = $this->postJson('/register', $dataEmailMissing2);
        $responseWrongFormat1 = $this->postJson('/register', $dataEmailWrongFormat1);
        $responseWrongFormat2 = $this->postJson('/register', $dataEmailWrongFormat2);
        $responseWrongFormat3 = $this->postJson('/register', $dataEmailWrongFormat3);
        $responseWrongFormat4 = $this->postJson('/register', $dataEmailWrongFormat4);

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

    public function test_returns_bad_request_when_validation_password_fails_with_code_0(): void
    {
        $data = ['name' => 'Gustavo', 'email' => 'email@email.com'];

        $dataPasswordMissing1 = array_merge($data, []);
        $dataPasswordMissing2 = array_merge($data, ['password' => '']);
        $dataPasswordWhitespace = array_merge($data, ['password' => '        ']);
        $dataPasswordLessThanMinimum = array_merge($data, ['password' => 'aaaaaaa']);
        $dataPasswordMoreThanMaximum = array_merge($data, ['password' => 'aaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaa']);

        $responsePasswordMissing1 = $this->postJson('/register', $dataPasswordMissing1);
        $responsePasswordMissing2 = $this->postJson('/register', $dataPasswordMissing2);
        $responsePasswordWhitespace = $this->postJson('/register', $dataPasswordWhitespace);
        $responsePasswordLessThanMinimum = $this->postJson('/register', $dataPasswordLessThanMinimum);
        $responsePasswordMoreThanMaximum = $this->postJson('/register', $dataPasswordMoreThanMaximum);

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
}
