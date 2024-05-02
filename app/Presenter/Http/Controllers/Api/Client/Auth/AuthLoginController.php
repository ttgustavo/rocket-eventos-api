<?php

namespace App\Presenter\Http\Controllers\Api\Client\Auth;

use App\Domain\Repository\UserRepository;
use App\Infrastructure\Eloquent\Models\User;
use App\Presenter\ArrayUtil;
use App\Presenter\Http\Controllers\Api\ApiController;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use OpenApi\Attributes\JsonContent;
use OpenApi\Attributes\Post;
use OpenApi\Attributes\Property;
use OpenApi\Attributes\RequestBody;
use OpenApi\Attributes\Response;
use Symfony\Component\HttpFoundation\Response as HttpResponse;

class AuthLoginController extends ApiController
{
    private UserRepository $repository;

    public function __construct(UserRepository $repository)
    {
        $this->repository = $repository;
    }

    #[Post(
        path: '/login',
        description: 'Authenticates the user with e-mail and password specified in the body with JSON format.',
        summary: 'Login user',
        requestBody: new RequestBody(
            content: new JsonContent(
                properties: [
                    new Property(property: 'email', type: 'string', default: ''),
                    new Property(property: 'password', type: 'string', default: '')
                ]
            )
        ),
        tags: ['Authentication'],
        responses: [
            new Response(
                response: HttpResponse::HTTP_OK,
                description: 'Authenticated successfully.',
                content: new JsonContent(properties: [new Property(property: 'token', type: 'string')])
            ),
            new Response(
                response: HttpResponse::HTTP_BAD_REQUEST,
                description: 'Failed to authenticate. There are three reasons to that, specified by the code in body:<br>' .
                        '- 0: validation<br>' .
                        '- 1: wrong email/password<br>' .
                        '- 2: account is banned',
                content: [
                    new JsonContent(
                        properties: [
                            new Property(property: 'code', description: "Abc", type: 'int', default: 0),
                        ]
                    ),
                ]
            ),
            new Response(
                response: HttpResponse::HTTP_FORBIDDEN,
                description: 'Account banned.'
            )
        ]
    )]
    public function __invoke(Request $request): JsonResponse
    {
        $jsonString = $request->getContent();
        $json = json_decode($jsonString, true);

        ArrayUtil::trimValues($json, [AuthControllerInputs::FIELD_PASSWORD]);

        $validator = AuthValidation::validateLogin($json);
        if ($validator->fails()) return parent::responseBadRequest(['code' => 0]);

        $data = $validator->valid();

        $authenticated = Auth::once($data);
        if ($authenticated === false) return parent::responseBadRequest(['code' => 1]);

        $isAccountBanned = $this->repository->isBanned($data['email']);
        if ($isAccountBanned) return parent::responseForbidden();

        /** @var Authenticatable|User $user */
        $user = Auth::user();
        $token = $user->createToken('user')->plainTextToken;

        return parent::responseOk(['token' => $token]);
    }
}
