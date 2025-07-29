<?php

namespace Asciisd\Cybersource\Responses;

use Illuminate\Support\Collection;

class TransactionSearchResponse
{
    public function __construct(
        public readonly string $searchId,
        public readonly string $name,
        public readonly bool $save,
        public readonly string $sort,
        public readonly int $count,
        public readonly int $totalCount,
        public readonly int $limit,
        public readonly int $offset,
        public readonly string $query,
        public readonly string $timezone,
        public readonly string $submitTimeUtc,
        public readonly array $links,
        private readonly array $rawTransactionSummaries = []
    ) {}

    /**
     * Create from API response array
     */
    public static function fromArray(array $data): self
    {
        return new self(
            searchId: $data['searchId'],
            name: $data['name'],
            save: $data['save'],
            sort: $data['sort'],
            count: $data['count'],
            totalCount: $data['totalCount'],
            limit: $data['limit'],
            offset: $data['offset'],
            query: $data['query'],
            timezone: $data['timezone'],
            submitTimeUtc: $data['submitTimeUtc'],
            links: $data['_links'],
            rawTransactionSummaries: $data['_embedded']['transactionSummaries'] ?? []
        );
    }

    /**
     * Get transaction summaries as collection
     */
    public function getTransactions(): Collection
    {
        return collect($this->rawTransactionSummaries)
            ->map(fn (array $transaction) => TransactionSummary::fromArray($transaction));
    }

    /**
     * Check if there are more results available
     */
    public function hasMoreResults(): bool
    {
        return ($this->offset + $this->count) < $this->totalCount;
    }

    /**
     * Get the next offset for pagination
     */
    public function getNextOffset(): ?int
    {
        return $this->hasMoreResults() ? $this->offset + $this->limit : null;
    }

    /**
     * Check if this is the first page
     */
    public function isFirstPage(): bool
    {
        return $this->offset === 0;
    }

    /**
     * Check if this is the last page
     */
    public function isLastPage(): bool
    {
        return ! $this->hasMoreResults();
    }

    /**
     * Get pagination info
     */
    public function getPaginationInfo(): array
    {
        return [
            'current_page' => intval($this->offset / $this->limit) + 1,
            'total_pages' => intval(ceil($this->totalCount / $this->limit)),
            'per_page' => $this->limit,
            'total' => $this->totalCount,
            'from' => $this->offset + 1,
            'to' => min($this->offset + $this->count, $this->totalCount),
            'has_more' => $this->hasMoreResults(),
        ];
    }

    /**
     * Get the self link for this search
     */
    public function getSelfLink(): ?string
    {
        return $this->links['self']['href'] ?? null;
    }

    /**
     * Convert to array (for JSON serialization)
     */
    public function toArray(): array
    {
        return [
            'searchId' => $this->searchId,
            'name' => $this->name,
            'save' => $this->save,
            'sort' => $this->sort,
            'count' => $this->count,
            'totalCount' => $this->totalCount,
            'limit' => $this->limit,
            'offset' => $this->offset,
            'query' => $this->query,
            'timezone' => $this->timezone,
            'submitTimeUtc' => $this->submitTimeUtc,
            'transactions' => $this->getTransactions()->toArray(),
            'pagination' => $this->getPaginationInfo(),
            '_links' => $this->links,
        ];
    }
}
