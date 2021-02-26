<?php

use App\GraphQL\Handlers\UserHandler;
use App\GraphQL\Type\PaginationMetadata;
use App\GraphQL\Type\Types;
use GraphQL\Type\Definition\ObjectType;

$userHandler = new UserHandler();

Types::load();

return [
    'query' => new ObjectType([
        'name' => 'Query',
        'fields' => [
            'allUsers' => $userHandler->all(),
            'getUser' => $userHandler->one(),
        ]
    ]),

    'mutation' => new ObjectType([
        'name' => 'Mutation',
        'fields' => [
            'createUser' => $userHandler->create(),
            'updateUser' => $userHandler->update(),
            'deleteUser' => $userHandler->delete(),
        ]
    ]),

    'PaginationMetadata' => (new PaginationMetadata())
];
