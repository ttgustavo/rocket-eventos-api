<?php

namespace App\Presenter\Http\Controllers\Api\Client\User;

use App\Domain\Repository\AttendeeRepository;
use App\Presenter\Http\Controllers\Api\ApiController;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class UserEventsController extends ApiController
{
    private AttendeeRepository $repository;

    public function __construct(AttendeeRepository $repository)
    {
        $this->repository = $repository;
    }

    public function __invoke(Request $request): JsonResponse
    {
        $userId = Auth::id();

        $events = $this->repository->getEventsFromUser($userId);
        return parent::responseOk($events);
    }
}
