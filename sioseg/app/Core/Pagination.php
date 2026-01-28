<?php

namespace App\Core;

class Pagination
{
    private int $currentPage;
    private int $totalRecords;
    private int $recordsPerPage;
    private int $totalPages;
    private string $baseUrl;

    public function __construct(int $currentPage, int $totalRecords, int $recordsPerPage = 50, string $baseUrl = '')
    {
        $this->currentPage = max(1, $currentPage);
        $this->totalRecords = $totalRecords;
        $this->recordsPerPage = $recordsPerPage;
        $this->totalPages = ceil($totalRecords / $recordsPerPage);
        $this->baseUrl = $baseUrl;
    }

    public function getCurrentPage(): int
    {
        return $this->currentPage;
    }

    public function getTotalPages(): int
    {
        return $this->totalPages;
    }

    public function getOffset(): int
    {
        return ($this->currentPage - 1) * $this->recordsPerPage;
    }

    public function getLimit(): int
    {
        return $this->recordsPerPage;
    }

    public function hasNextPage(): bool
    {
        return $this->currentPage < $this->totalPages;
    }

    public function hasPreviousPage(): bool
    {
        return $this->currentPage > 1;
    }

    public function getNextPageUrl(): string
    {
        if (!$this->hasNextPage()) {
            return '';
        }
        return $this->baseUrl . '?page=' . ($this->currentPage + 1);
    }

    public function getPreviousPageUrl(): string
    {
        if (!$this->hasPreviousPage()) {
            return '';
        }
        return $this->baseUrl . '?page=' . ($this->currentPage - 1);
    }

    public function renderPaginationControls(): string
    {
        if ($this->totalPages <= 1) {
            return '';
        }

        $html = '<div class="pagination-controls">';
        
        if ($this->hasPreviousPage()) {
            $html .= '<a href="' . $this->getPreviousPageUrl() . '" class="pagination-btn">← Anterior</a>';
        }
        
        $html .= '<span class="pagination-info">Página ' . $this->currentPage . ' de ' . $this->totalPages . '</span>';
        
        if ($this->hasNextPage()) {
            $html .= '<a href="' . $this->getNextPageUrl() . '" class="pagination-btn">Próxima →</a>';
        }
        
        $html .= '</div>';
        
        return $html;
    }
}