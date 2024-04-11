<?php

namespace App\Domain\Usecase;

use App\Domain\Error\RegisterError;
use App\Domain\Model\UserModel;
use App\Domain\Repository\UserRepository;
use Respect\Validation\Validator;

class RegisterUsecase
{
    private $userRepository;

    public function __construct(UserRepository $userRepository)
    {
        $this->userRepository = $userRepository;
    }

    public function __invoke(string $name, string $email, string $password): UserModel|RegisterError
    {
        $hasEmailRegistered = $this->userRepository->hasEmailRegistered($email);
        if ($hasEmailRegistered) return RegisterError::EmailAlreadyExists;

        $user = $this->userRepository->insert($name, $email, $password);
        return $user;
    }

    private function validate(string $name, string $email, string $password): bool
    {
        $isNameValid = Validator::stringVal()
            ->notBlank()
            ->length(3, 150, true)
            ->validate($name);
        $isEmailValid = Validator::stringType()
            ->notBlank()
            ->email()
            ->validate($email);
        $isPasswordValid = Validator::stringType()
            ->notBlank()
            ->length(8, 100)
            ->validate($password);
        
        return $isNameValid && $isEmailValid && $isPasswordValid;
    }
}