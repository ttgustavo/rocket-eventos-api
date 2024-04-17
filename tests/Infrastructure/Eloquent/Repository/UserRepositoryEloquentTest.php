<?php

namespace Tests\Infrastructure\Eloquent\Repository;

use App\Domain\Repository\UserRepository;
use App\Infrastructure\Eloquent\Models\User;
use App\Infrastructure\Eloquent\Repository\UserRepositoryEloquent;
use Database\Factories\UserFactory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserRepositoryEloquentTest extends TestCase
{
    use RefreshDatabase;

    private UserRepository $repository;

    protected function setUp(): void
    {
        parent::setUp();

        $this->repository = new UserRepositoryEloquent;
    }

    public function test_when_user_exists_then_has_email_registered_returns_true(): void
    {
        $email = 'email@email.com';
        $this->createUser(email: $email);

        $hasEmailRegistered = $this->repository->hasEmailRegistered($email);

        $this->assertSame(true, $hasEmailRegistered);
    }

    public function test_when_user_not_exists_then_has_email_registered_returns_false(): void
    {
        $email = 'email@email.com';

        $hasEmailRegistered = $this->repository->hasEmailRegistered($email);

        $this->assertSame(false, $hasEmailRegistered);
    }

    public function test_when_created_user_returns_user(): void
    {
        $this->repository->insert('Gustavo', 'email@email.com', 'mypassword');

        $this->assertDatabaseCount(User::class, 1);
        $this->assertDatabaseHas(User::class, ['name' => 'Gustavo', 'email' => 'email@email.com']);
    }

    private function createUser(string $name = null, string $email = null): void
    {
        $attributes = [];

        if (is_null($name) === false) $attributes['name'] = $name;
        if (is_null($email) === false) $attributes['email'] = $email;

        UserFactory::new($attributes)->create();
    }
}
