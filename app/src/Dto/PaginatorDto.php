<?php
declare(strict_types=1);

namespace App\Dto;

use App\Dto\Exception\PaginatorDtoPageException;
use App\Dto\Exception\PaginatorDtoPageSizeException;
use Doctrine\ORM\Tools\Pagination\Paginator;

class PaginatorDto
{
    public readonly int $totalItems;
    public readonly int $totalPages;

    private Paginator $paginator;

    public function __construct(
        public readonly int $page,
        public readonly int $pageSize,
        Paginator           $paginator
    )
    {
        if ($page < 1) {
            throw new PaginatorDtoPageException(sprintf('Parameter "page" must be positive value. Got "%s"', $page));
        }

        if ($pageSize < 1) {
            throw new PaginatorDtoPageSizeException(sprintf('Parameter "pageSize" must be positive value. Got "%s"', $pageSize));
        }

        $this->totalItems = $paginator->count();
        $this->totalPages = (int)ceil($this->totalItems / $pageSize);

        if ($this->totalPages && $page > $this->totalPages) {
            throw new PaginatorDtoPageException(
                sprintf('Parameter "page" must be less or equal "%s".', $this->totalPages));
        }

        $paginator->getQuery()
            ->setFirstResult(($page - 1) * $pageSize)
            ->setMaxResults($this->pageSize);

        $this->paginator = $paginator;
    }

    /**
     * @throws \Exception
     */
    public function getIterator(): \ArrayIterator
    {
        return $this->paginator->getIterator();
    }
}
