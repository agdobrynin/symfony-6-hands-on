<?php
declare(strict_types=1);

namespace App\Dto;

use App\Dto\Exception\PaginatorDtoPageException;
use App\Dto\Exception\PaginatorDtoPageSizeException;

class PaginatorDto
{
    public readonly int $totalPages;
    public readonly int $firstResultIndex;

    public function __construct(
        public readonly int $page,
        public readonly int $totalItems,
        public readonly int $pageSize)
    {
        if ($page < 1) {
            throw new PaginatorDtoPageException(sprintf('Parameter "page" must be positive value. Got "%s"', $page));
        }

        if ($pageSize < 1) {
            throw new PaginatorDtoPageSizeException(sprintf('Parameter "pageSize" must be positive value. Got "%s"', $pageSize));
        }

        $this->totalPages = (int)ceil($totalItems / $pageSize);

        if ($this->totalPages && $page > $this->totalPages) {
            throw new PaginatorDtoPageException(
                sprintf('Parameter "page" must be less or equal "%s".', $this->totalPages));
        }

        $this->firstResultIndex = ($page - 1) * $pageSize;
    }
}
