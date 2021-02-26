<?php

namespace App\GraphQL\Type;

use GraphQL\Type\Definition\ObjectType;
use GraphQL\Type\Definition\Type;

class PaginationMetadata extends ObjectType {
    public function __construct()
    {
        parent::__construct([
            'name' => 'PaginationMetadata',
            'fields' => [
                'totalCount' => Type::int(),
                'pagesCount' => Type::int()
            ]
        ]);
    }
}
