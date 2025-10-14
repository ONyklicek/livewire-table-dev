<?php

namespace NyonCode\LivewireTable\Builders;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use InvalidArgumentException;

class RelationshipResolver
{
    /**
     * Extract relationship names from columns
     */
    public static function extractRelationships(Collection $columns): array
    {
        return $columns
            ->map(fn ($column) => $column->getField())
            ->filter(fn ($field) => self::isRelationship($field))
            ->map(function ($field) {
                try {
                    return self::parseRelationship($field);
                } catch (InvalidArgumentException $e) {
                    Log::warning("Invalid relationship field: [$field]", [
                        'error' => $e->getMessage(),
                    ]);

                    return [];
                }
            })
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
        if (empty($field)) {
            throw new InvalidArgumentException('Field cannot be empty');
        }

        if (! Str::contains($field, '.')) {
            throw new InvalidArgumentException("Invalid relationship field: $field");
        }

        $parts = explode('.', $field);
        array_pop($parts); // Remove last part (field name)

        if (empty($parts)) {
            throw new InvalidArgumentException("Field '$field' has invalid format");
        }

        $relationships = [];
        $current = '';

        foreach ($parts as $part) {

            if (empty(trim($part))) {
                throw new InvalidArgumentException("Field '$field' contains empty relationship segment");
            }

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

    /**
     * Check if field is a relationship (contains dot notation)
     */
    public static function isRelationship(string $field): bool
    {
        return ! empty($field) && Str::contains($field, '.');
    }

    /**
     * Get relationship name from field
     * Example: "user.company.name" -> "user.company"
     */
    public static function getRelationshipPath(string $field): ?string
    {
        if (! self::isRelationship($field)) {
            return null;
        }

        $parts = explode('.', $field);
        array_pop($parts);

        return implode('.', $parts);
    }

    /**
     * Get field name from relationship field
     * Example: "user.company.name" -> "name"
     */
    public static function getFieldName(string $field): string
    {
        $parts = explode('.', $field);

        return end($parts);
    }

    /**
     * Validate relationship field format
     *
     * @throws InvalidArgumentException
     */
    public static function validateField(string $field): void
    {
        if (empty($field)) {
            throw new InvalidArgumentException('Field cannot be empty');
        }

        if (Str::contains($field, '..')) {
            throw new InvalidArgumentException("Field '$field' contains consecutive dots");
        }

        if (Str::startsWith($field, '.') || Str::endsWith($field, '.')) {
            throw new InvalidArgumentException("Field '$field' cannot start or end with a dot");
        }
    }
}
