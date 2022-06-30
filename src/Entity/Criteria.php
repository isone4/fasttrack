<?php

namespace App\Entity;

class Criteria
{
    private int $page;
    private int $perPage;
    private string $sortingMethod;
    private ?string $searchValue;
    private ?string $columnName;

    public function __construct(?int $page, ?int $perPage, ?string $sortingMethod, ?string $columnName)
    {
        $this->page = $page === 0 ? 1 : $page;
        $this->perPage = $perPage === 0 ? 5 : $perPage;
        $this->sortingMethod = $sortingMethod ?? 'desc';
        $this->columnName = $columnName ?? 'id';
    }

    public function getPage(): int
    {
        return $this->page;
    }

    public function getPerPage(): int
    {
        return $this->perPage;
    }

    public function getSortingMethod(): string
    {
        return $this->sortingMethod;
    }

    public function getOffset(): int
    {
        return ($this->page - 1) * $this->perPage;
    }

    public function getSearchValue(): ?string
    {
        return $this->searchValue;
    }

    public function setSearchValue(?string $searchValue): void
    {
        $this->searchValue = $searchValue;
    }

    public function getColumnName(): ?string
    {
        return $this->columnName;
    }
//
//    public function setColumnName(?string $columnName): void
//    {
//        $this->columnName = $columnName;
//    }

}