<?php

namespace App\Domain\Repository;

use App\Domain\Model\EventModel;

interface EventRepository
{
    public function hasEventWithSlug(string $slug): bool;
    public function create(
        string $name,
        string $slug,
        string $details,
        string $subscriptionDateStart,
        string $subscriptionDateEnd,
        string $presentationAt,
    ): EventModel;
}