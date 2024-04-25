<?php

namespace App\Domain\Pagination;

readonly class ModelPagination
{
    const KEY_ITEMS = 'data';
    const KEY_TOTAL_ITEMS = 'totalItems';
    const KEY_PREVIOUS_PAGE = 'previousPage';
    const KEY_NEXT_PAGE = 'nextPage';

    public function __construct(
        public array   $items,
        public int     $totalItems,
        public ?string $nextPage,
        public ?string $previousPage,
    )
    {
    }

    public function toJson(): string
    {
        $jsonArray = [
            self::KEY_ITEMS => $this->items,
            self::KEY_TOTAL_ITEMS => $this->totalItems
        ];

        if (is_null($this->previousPage) === false) {
            $jsonArray[self::KEY_PREVIOUS_PAGE] = $this->previousPage;
        }
        if (is_null($this->nextPage) === false) {
            $jsonArray[self::KEY_NEXT_PAGE] = $this->nextPage;
        }

        return json_encode($jsonArray);
    }
}
