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

class UpdateUserValidationTest extends TestCase
{
    public function test_validation_name(): void
    {
        $dataWithNameValid = ['name' => 'Gustavo'];
        $dataWithNameLessThanMinimum = ['name' => 'A'];
        $dataWithNameMoreThanMaximum = ['name' => str_pad('', 151, 'a')];

        $validatorWithNameValid = UpdateUserValidation::validate($dataWithNameValid);
        $this->assertTrue($validatorWithNameValid->passes());

        $validator = UpdateUserValidation::validate($dataWithNameLessThanMinimum);
        $this->assertFalse($validator->passes());

        $validator = UpdateUserValidation::validate($dataWithNameMoreThanMaximum);
        $this->assertFalse($validator->passes());
    }

    public function test_validation_email(): void
    {
        $dataWithEmailValid = ['email' => 'email@email.com'];
        $dataWithEmailBadFormat1 = ['email' => '@email.com'];
        $dataWithEmailBadFormat2 = ['email' => 'email@email'];
        $dataWithEmailBadFormat3 = ['email' => 'email'];
        $dataWithEmailBadFormat4 = ['email' => '@email.com'];

        $validatorWithNameValid = UpdateUserValidation::validate($dataWithEmailValid);
        $this->assertTrue($validatorWithNameValid->passes());

        $validator = UpdateUserValidation::validate($dataWithEmailBadFormat1);
        $this->assertFalse($validator->passes());

        $validator = UpdateUserValidation::validate($dataWithEmailBadFormat2);
        $this->assertFalse($validator->passes());

        $validator = UpdateUserValidation::validate($dataWithEmailBadFormat3);
        $this->assertFalse($validator->passes());

        $validator = UpdateUserValidation::validate($dataWithEmailBadFormat4);
        $this->assertFalse($validator->passes());
    }

    public function test_validation_password(): void
    {
        $dataWithPasswordsValid = ['old_password' => '12345678', 'new_password' => '12345678'];
        $dataWithOldPasswordLessThanMinimum = ['old_password' => '1234567', 'new_password' => '12345678'];
        $dataWithOldPasswordMoreThanMaximum = ['old_password' => str_pad('', 101, 'a'), 'new_password' => '12345678'];
        $dataWithNewPasswordLessThanMinimum = ['old_password' => '12345678', 'new_password' => '1234567'];
        $dataWithNewPasswordMoreThanMaximum = ['old_password' => '12345678', 'new_password' => str_pad('', 101, 'a')];
        $dataWithNewPasswordMissing = ['old_password' => '12345678'];
        $dataWithOldPasswordMissing = ['new_password' => '12345678'];


        $validatorWithNameValid = UpdateUserValidation::validate($dataWithPasswordsValid);
        $this->assertTrue($validatorWithNameValid->passes());

        $validator = UpdateUserValidation::validate($dataWithOldPasswordLessThanMinimum);
        $this->assertFalse($validator->passes());

        $validator = UpdateUserValidation::validate($dataWithOldPasswordMoreThanMaximum);
        $this->assertFalse($validator->passes());

        $validator = UpdateUserValidation::validate($dataWithNewPasswordLessThanMinimum);
        $this->assertFalse($validator->passes());

        $validator = UpdateUserValidation::validate($dataWithNewPasswordMoreThanMaximum);
        $this->assertFalse($validator->passes());

        $validator = UpdateUserValidation::validate($dataWithNewPasswordMissing);
        $this->assertFalse($validator->passes());

        $validator = UpdateUserValidation::validate($dataWithOldPasswordMissing);
        $this->assertFalse($validator->passes());
    }
}
