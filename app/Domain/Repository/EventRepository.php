<?php

namespace App\Domain\Repository;

use App\Domain\Model\EventModel;
use App\Domain\Pagination\ModelPagination;

interface EventRepository
{
    public function hasEventWithId(int $id): bool;
    public function hasEventWithSlug(string $slug): bool;

    public function create(
        string $name,
        string $slug,
        string $details,
        string $subscriptionDateStart,
        string $subscriptionDateEnd,
        string $presentationAt,
    ): EventModel;

    public function getAll(int $page = 1): ModelPagination;

    public function getById(int $id): ?EventModel;
    public function getBySlug(string $slug): ?EventModel;

    public function update(int $id, array $data): ?EventModel;

    public function delete(int $id): void;
}
