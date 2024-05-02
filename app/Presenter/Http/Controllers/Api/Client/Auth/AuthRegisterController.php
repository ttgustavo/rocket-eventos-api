<?php

namespace App\Presenter\Http\Controllers\Api\Client\Auth;

use App\Domain\Model\UserModel;
use App\Domain\Repository\UserRepository;
use App\Presenter\ArrayUtil;
use App\Presenter\Http\Controllers\Api\ApiController;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use OpenApi\Attributes\JsonContent;
use OpenApi\Attributes\Post;
use OpenApi\Attributes\Property;
use OpenApi\Attributes\RequestBody;
use OpenApi\Attributes\Response;
use Symfony\Component\HttpFoundation\Response as HttpResponse;

class AuthRegisterController extends ApiController
{
    private UserRepository $repository;

    public function __construct(UserRepository $repository)
    {
        $this->repository = $repository;
    }

    #[Post(
        path: '/register',
        description: 'Register an user specifying name, e-mail and password and they are required. This route has two return types.',
        summary: 'Register user',
        requestBody: new RequestBody(
            content: new JsonContent(
                properties: [
                    new Property(property: 'name', type: 'string', default: ''),
                    new Property(property: 'email', type: 'string', default: ''),
                    new Property(property: 'password', type: 'string', default: '')
                ]
            )
        ),
        tags: ['Authentication'],
        responses: [
            new Response(
                response: HttpResponse::HTTP_CREATED,
                description: 'The user was registered successfully.',
                content: new JsonContent(ref: UserModel::class)
            ),
            new Response(
                response: HttpResponse::HTTP_BAD_REQUEST,
                description: 'Failed to register. There are two reasons to that, specified by the code in body:<br>- 0: validation<br>- 1: email already exists<br>',
                content: [
                    new JsonContent(
                        properties: [
                            new Property(property: 'code', type: 'int', default: 0),
                        ],
                    ),
                ]
            )
        ]
    )]
    public function __invoke(Request $request): JsonResponse
    {
        $content = $request->getContent();
        $json = json_decode($content, true);

        ArrayUtil::trimValues($json, [AuthControllerInputs::FIELD_PASSWORD]);

        $validator = AuthValidation::validateRegister($json);
        if ($validator->fails()) return parent::responseBadRequest(['code' => 0]);

        $json = $validator->valid();
        $name = $json[AuthControllerInputs::FIELD_NAME];
        $email = $json[AuthControllerInputs::FIELD_EMAIL];
        $password = $json[AuthControllerInputs::FIELD_PASSWORD];

        $hasEmailRegistered = $this->repository->hasEmailRegistered($email);
        if ($hasEmailRegistered) return parent::responseBadRequest(['code' => 1]);

        $user = $this->repository->insert($name, $email, $password);
        return parent::responseCreated($user);
    }
}
