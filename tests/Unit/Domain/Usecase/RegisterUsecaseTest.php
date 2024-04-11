<?php

namespace Tests\Unit\Domain\Usecase;

use App\Domain\Error\RegisterError;
use App\Domain\Model\UserModel;
use App\Domain\Model\UserStatus;
use App\Domain\Repository\UserRepository;
use App\Domain\Usecase\RegisterUsecase;
use Carbon\Carbon;
use PHPUnit\Framework\TestCase;

class RegisterUsecaseTest extends TestCase
{
    private $userRepository;

    public function setUp(): void
    {
        $this->userRepository = $this->createMock(UserRepository::class);
    }


    public function test_register_success_return_user(): void
    {
        $data = ['name' => 'Gustavo', 'email' => 'email@email.com', 'password' => 'password'];
        $userToReturn = new UserModel(
            0,
            $data['name'],
            $data['email'],
            Carbon::now(),
            Carbon::now(),
            UserStatus::Registered,
        );

        $this->userRepository->method('hasEmailRegistered')->willReturn(false);
        $this->userRepository->method('insert')->willReturn($userToReturn);

        $usecase = new RegisterUsecase($this->userRepository);
        $user = $usecase->__invoke($data['name'], $data['email'], $data['password']);

        $this->assertNotNull($user);
    }

    public function test_register_email_already_exists_return_error(): void
    {
        $data = ['name' => 'Gustavo', 'email' => 'email@email.com', 'password' => 'password'];
        $this->userRepository->method('hasEmailRegistered')->willReturn(true);

        $usecase = new RegisterUsecase($this->userRepository);
        $result = $usecase->__invoke($data['name'], $data['email'], $data['password']);

        $this->assertInstanceOf(RegisterError::class, $result);
        $this->assertEquals(RegisterError::EmailAlreadyExists, $result);
    }

}