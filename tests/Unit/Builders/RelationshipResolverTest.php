<?php

declare(strict_types=1);

use NyonCode\LivewireTable\Builders\RelationshipResolver;
use NyonCode\LivewireTable\Columns\TextColumn;

covers(RelationshipResolver::class);

describe('parseRelationship', function () {
    test('parses simple relationship correctly', function () {
        $result = RelationshipResolver::parseRelationship('user.name');

        expect($result)->toBe(['user']);
    });

    test('parses nested relationships correctly', function () {
        $result = RelationshipResolver::parseRelationship('user.company.name');

        expect($result)->toBe(['user', 'user.company']);
    });

    test('parses deeply nested relationships', function () {
        $result = RelationshipResolver::parseRelationship('user.posts.comments.author.name');

        expect($result)->toBe([
            'user',
            'user.posts',
            'user.posts.comments',
            'user.posts.comments.author',
        ]);
    });

    test('throws exception for empty field', function () {
        expect(fn () => RelationshipResolver::parseRelationship(''))
            ->toThrow(InvalidArgumentException::class, 'Field cannot be empty');
    });

    test('throws exception for non-relationship field', function () {
        expect(fn () => RelationshipResolver::parseRelationship('name'))
            ->toThrow(InvalidArgumentException::class, 'Invalid relationship field:');
    });

    test('throws exception for field with only dot', function () {
        expect(fn () => RelationshipResolver::parseRelationship('.'))
            ->toThrow(InvalidArgumentException::class);
    });
});

describe('validateField', function () {
    test('throws exception for consecutive dots', function () {
        expect(fn () => RelationshipResolver::validateField('user..name'))
            ->toThrow(InvalidArgumentException::class, 'consecutive dots');
    });

    test('throws exception for leading dot', function () {
        expect(fn () => RelationshipResolver::validateField('.user.name'))
            ->toThrow(InvalidArgumentException::class, 'cannot start or end with a dot');
    });

    test('throws exception for trailing dot', function () {
        expect(fn () => RelationshipResolver::validateField('user.name.'))
            ->toThrow(InvalidArgumentException::class, 'cannot start or end with a dot');
    });

    test('validates field format successfully', function () {
        expect(fn () => RelationshipResolver::validateField('user.name'))
            ->not->toThrow(InvalidArgumentException::class)
            ->and(fn () => RelationshipResolver::validateField('user.company.name'))
            ->not->toThrow(InvalidArgumentException::class);
    });
});

describe('isRelationship', function () {
    test('checks if field is a relationship', function () {
        expect(RelationshipResolver::isRelationship('user.name'))
            ->toBeTrue()
            ->and(RelationshipResolver::isRelationship('name'))
            ->toBeFalse()
            ->and(RelationshipResolver::isRelationship(''))
            ->toBeFalse();
    });
});

describe('getRelationshipPath', function () {
    test('extracts relationship path from field', function () {
        expect(RelationshipResolver::getRelationshipPath('user.company.name'))
            ->toBe('user.company')
            ->and(RelationshipResolver::getRelationshipPath('user.name'))
            ->toBe('user')
            ->and(RelationshipResolver::getRelationshipPath('name'))
            ->toBeNull();
    });
});

describe('getFieldName', function () {
    test('extracts field name from relationship', function () {
        expect(RelationshipResolver::getFieldName('user.company.name'))
            ->toBe('name')
            ->and(RelationshipResolver::getFieldName('name'))
            ->toBe('name');
    });
});

describe('extractRelationships', function () {
    test('extracts relationships from columns collection', function () {
        $columns = collect([
            TextColumn::make('name'),
            TextColumn::make('user.name'),
            TextColumn::make('user.company.name'),
            TextColumn::make('email'),
        ]);

        $result = RelationshipResolver::extractRelationships($columns);

        expect($result)
            ->toBeArray()
            ->toHaveCount(2)
            ->toBe(['user', 'user.company']);
    });

    test('handles duplicate relationships correctly', function () {
        $columns = collect([
            TextColumn::make('user.name'),
            TextColumn::make('user.email'),
            TextColumn::make('user.company.name'),
        ]);

        $result = RelationshipResolver::extractRelationships($columns);

        expect($result)
            ->toBe(['user', 'user.company'])
            ->toHaveCount(2);
    });
});

describe('parseRelationship with datasets', function () {
    test('parses various relationship formats', function (string $input, array $expected) {
        $result = RelationshipResolver::parseRelationship($input);

        expect($result)->toBe($expected);
    })->with([
        'simple relationship' => ['user.name', ['user']],
        'nested relationship' => ['user.company.name', ['user', 'user.company']],
        'deeply nested' => [
            'user.posts.comments.author.name',
            ['user', 'user.posts', 'user.posts.comments', 'user.posts.comments.author'],
        ],
    ]);
});

describe('validateField with datasets', function () {
    test('throws exception for invalid fields', function (string $field, string $expectedMessage) {
        expect(fn () => RelationshipResolver::validateField($field))
            ->toThrow(InvalidArgumentException::class, $expectedMessage);
    })->with([
        'consecutive dots' => ['user..name', 'consecutive dots'],
        'leading dot' => ['.user.name', 'cannot start or end with a dot'],
        'trailing dot' => ['user.name.', 'cannot start or end with a dot'],
        'empty field' => ['', 'Field cannot be empty'],
    ]);
});