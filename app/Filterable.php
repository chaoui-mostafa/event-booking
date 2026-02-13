<?php

namespace App\Traits;

use Illuminate\Database\Eloquent\Builder;

trait Filterable
{
    /**
     * Filter by date range
     */
    public function scopeFilterByDate(Builder $query, ?string $startDate = null, ?string $endDate = null): Builder
    {
        if ($startDate) {
            $query->where('created_at', '>=', $startDate);
        }

        if ($endDate) {
            $query->where('created_at', '<=', $endDate);
        }

        return $query;
    }

    /**
     * Search by title
     */
    public function scopeSearchByTitle(Builder $query, ?string $searchTerm = null): Builder
    {
        if ($searchTerm) {
            $query->where('title', 'LIKE', "%{$searchTerm}%");
        }

        return $query;
    }
}
