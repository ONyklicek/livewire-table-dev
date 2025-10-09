<?php

namespace NyonCode\LivewireTable\Builders;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class RelationshipResolver
{
    /**
     * Extract relationship names from columns
     */
    public static function extractRelationships(Collection $columns): array
    {
        return $columns
            ->map(fn ($column) => $column->getField())
            ->filter(fn ($field) => str_contains($field, '.'))
            ->map(fn ($field) => self::parseRelationship($field))
            ->flatten()
            ->unique()
            ->values()
            ->toArray();
    }

    /**
     * Parse relationship from dot notation
     * Example: "user.posts.comments" -> ["user", "user.posts", "user.posts.comments"]
     */
    public static function parseRelationship(string $field): array
    {
        $parts = explode('.', $field);
        array_pop($parts); // Remove last part (field name)

        $relationships = [];
        $current = '';

        foreach ($parts as $part) {
            $current = $current ? "$current.$part" : $part;
            $relationships[] = $current;
        }

        return $relationships;
    }

    /**
     * Eager load relationships on query
     */
    public static function eagerLoad(Builder $query, array $relationships): Builder
    {
        if (empty($relationships)) {
            return $query;
        }

        return $query->with($relationships);
    }

    /**
     * Apply relationship constraints
     */
    public static function applyConstraints(
        Builder $query,
        string $relation,
        callable $callback
    ): Builder {
        return $query->whereHas($relation, $callback);
    }

    /**
     * Optimize relationship loading with counts
     */
    public static function withCounts(Builder $query, array $relations): Builder
    {
        return $query->withCount($relations);
    }
}
