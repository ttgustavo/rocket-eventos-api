<?php

namespace Tests\Feature\Controller\Api\Auth;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Symfony\Component\HttpFoundation\Response;
use Tests\TestCase;

class AuthLoginControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_returns_status_code_200_with_token_when_authenticated(): void
    {
        $dataRegistration = [
            'name' => 'Gustavo',
            'email' => 'email@email.com',
            'password' => 'mypassword'
        ];
        $dataLogin = ['email' => 'email@email.com', 'password' => 'mypassword'];
        $this->postJson('/api/register', $dataRegistration);

        $response = $this->postJson( '/api/login', $dataLogin);
        
        $response->assertStatus(Response::HTTP_OK);
        $response->assertJsonIsObject();
        $response->assertJsonStructure(['token']);
    }

    public function test_returns_status_code_400_when_authentication_fails(): void
    {
        $data = [
            'email' => 'email@email.com',
            'password' => 'mypassword'
        ];

        $response = $this->postJson('/api/login', $data);

        $response->assertStatus(Response::HTTP_BAD_REQUEST);
        $response->assertJson(['code' => 1]);
    }

    public function test_returns_status_code_400_with_code_1_when_validation_email_fails(): void
    {
        $data = ['name' => 'Gustavo', 'password' => 'password'];

        $dataEmailMissing1 = array_merge($data, []);
        $dataEmailMissing2 = array_merge($data, ['email' => '']);
        $dataEmailWrongFormat1 = array_merge($data, ['email' => 'myemail']);
        $dataEmailWrongFormat2 = array_merge($data, ['email' => '@email.com']);
        $dataEmailWrongFormat3 = array_merge($data, ['email' => 'myemail@email']);
        $dataEmailWrongFormat4 = array_merge($data, ['email' => 'myemail@email.']);

        $responseEmailMissing1 = $this->postJson('/api/login', $dataEmailMissing1);
        $responseEmailMissing2 = $this->postJson('/api/login', $dataEmailMissing2);
        $responseWrongFormat1 = $this->postJson('/api/login', $dataEmailWrongFormat1);
        $responseWrongFormat2 = $this->postJson('/api/login', $dataEmailWrongFormat2);
        $responseWrongFormat3 = $this->postJson('/api/login', $dataEmailWrongFormat3);
        $responseWrongFormat4 = $this->postJson('/api/login', $dataEmailWrongFormat4);

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

    public function test_returns_status_code_400_with_code_1_when_validation_password_fails(): void
    {
        $data = ['name' => 'Gustavo', 'email' => 'email@email.com'];

        $dataPasswordMissing1 = array_merge($data, []);
        $dataPasswordMissing2 = array_merge($data, ['password' => '']);
        $dataPasswordWhitespace = array_merge($data, ['password' => '  ']);
        $dataPasswordMoreThanMaximum = array_merge($data, ['password' => 'aaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaa']);

        $responsePasswordMissing1 = $this->postJson('/api/login', $dataPasswordMissing1);
        $responsePasswordMissing2 = $this->postJson('/api/login', $dataPasswordMissing2);
        $responsePasswordWhitespace = $this->postJson('/api/login', $dataPasswordWhitespace);
        $responsePasswordMoreThanMaximum = $this->postJson('/api/login', $dataPasswordMoreThanMaximum);

        $responsePasswordMissing1->assertStatus(Response::HTTP_BAD_REQUEST);
        $responsePasswordMissing1->assertJson(['code' => 0]);
        $responsePasswordMissing2->assertStatus(Response::HTTP_BAD_REQUEST);
        $responsePasswordMissing2->assertJson(['code' => 0]);
        $responsePasswordWhitespace->assertStatus(Response::HTTP_BAD_REQUEST);
        $responsePasswordWhitespace->assertJson(['code' => 0]);
        $responsePasswordMoreThanMaximum->assertStatus(Response::HTTP_BAD_REQUEST);
        $responsePasswordMoreThanMaximum->assertJson(['code' => 0]);
    }
}
