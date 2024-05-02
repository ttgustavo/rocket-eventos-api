<?php

namespace App\Domain\Model;

use Carbon\Carbon;
use OpenApi\Attributes\Property;
use OpenApi\Attributes\Schema;

#[Schema]
readonly class EventModel
{
    public function __construct(
        #[Property(type: 'integer', example: '1000')]
        public int         $id,
        #[Property(type: 'string', example: 'My Event')]
        public string      $name,
        #[Property(type: 'string', example: 'my-event')]
        public string      $slug,
        #[Property(type: 'string', example: 'A long text.')]
        public string      $details,
        #[Property(type: 'string', format: 'date-time', example: new Carbon)]
        public Carbon      $subscriptionStart,
        #[Property(type: 'string', format: 'date-time', example: new Carbon)]
        public Carbon      $subscriptionEnd,
        #[Property(type: 'string', format: 'date-time', example: new Carbon)]
        public Carbon      $presentationAt,
        #[Property(type: 'string', format: 'date-time', example: new Carbon)]
        public Carbon      $createdAt,
        #[Property(type: 'string', format: 'date-time', example: new Carbon)]
        public Carbon      $updatedAt,
        #[Property(type: 'integer', default: EventStatus::Created)]
        public EventStatus $status,
    ) {
    }
}
