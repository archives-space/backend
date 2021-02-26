<?php

namespace App\GraphQL\Handlers;

use App\Document\User;

class UserHandler extends ItemHandler
{
    protected array $config = [
        'naturalName' => 'user',
        'naturalNamePlural' => 'users',
        'name' => 'User',
        'namePlural' => 'Users',
        'documentClass' => User\User::class,
        'defaultSortField' => 'createdAt'
    ];
}
