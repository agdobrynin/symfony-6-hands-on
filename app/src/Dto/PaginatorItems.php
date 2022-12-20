<?php
declare(strict_types=1);

namespace App\Dto;

use App\Dto\Exception\PaginatorItemsPageException;
use App\Dto\Exception\PaginatorItemsPageSizeException;

class PaginatorItems
{
    public readonly int $totalPages;

    public function __construct(
        public readonly int            $page,
        public readonly int            $pageSize,
        public readonly int            $totalItems,
        public readonly \ArrayIterator $iterator,
    )
    {
        if ($page < 1) {
            throw new PaginatorItemsPageException(sprintf('Parameter "page" must be positive value. Got "%s"', $page));
        }

        if ($pageSize < 1) {
            throw new PaginatorItemsPageSizeException(sprintf('Parameter "pageSize" must be positive value. Got "%s"', $pageSize));
        }

        $this->totalPages = (int)ceil($totalItems / $pageSize);

        if ($this->totalPages && $page > $this->totalPages) {
            throw new PaginatorItemsPageException(
                sprintf('Parameter "page" must be less or equal "%s".', $this->totalPages));
        }
    }
}
