<?php

namespace App\GraphQL\Handlers;

use App\Utils\ContainerAdapter;
use App\Utils\DocumentHelper as DB;
use App\GraphQL\Type\Types;
use Exception;
use GraphQL\Type\Definition\InputObjectType;
use GraphQL\Type\Definition\ObjectType;
use GraphQL\Type\Definition\Type;
use Psr\Container\ContainerInterface;

class ItemHandler
{
    protected array $config;

    protected function getAllArgs(): array
    {
        return [
            [
                'name' => 'perPage',
                'type' => Type::int()
            ],
            [
                'name' => 'page',
                'type' => Type::int()
            ],
            [
                'name' => 'sortField',
                'type' => Type::string(),
                'defaultValue' => $this->config['defaultSortField']
            ],
            [
                'name' => 'sortOrder',
                'type' => Types::get('ChoiceString', ['asc', 'desc']),
                'defaultValue' => 'asc'
            ]
        ];
    }

    public function all(): array
    {
        return [
            'type' => new ObjectType([
                'name' => $this->config['namePlural'] . 'Output',
                'fields' => [
                    ['name' => 'data', 'type' => Type::listOf(Types::get($this->config['name']))],
                    ['name' => 'meta', 'type' => Types::get('PaginationMetadata')]
                ]
            ]),
            'args' => $this->getAllArgs(),
            'description' => 'Get many ' . $this->config['naturalNamePlural'],
            'resolve' => function (ContainerInterface $container, $args) {
                $qb = DB::getQueryBuilder($container, $this->config['documentClass']);
                $qb = $qb->sort($args['sortField'], $args['sortOrder']);
                return DB::paginate(
                    $qb,
                    $args['perPage'] ?? null,
                    $args['page'] ?? null
                );
            }
        ];
    }

    public function one(): array
    {
        return [
            'type' => Types::get($this->config['name']),
            'description' => 'Get a ' . $this->config['naturalName'],
            'args' => [[
                           'name' => 'id',
                           'description' => 'The Id of the ' . $this->config['naturalName'],
                           'type' => Type::id()
                       ]],
            'resolve' => function (ContainerAdapter $container, $args) {
                $item = DB::getOne($container, $this->config['documentClass'], $args['id']);
                if ($item == null)
                    return new Exception("Unknown item, probably a invalid id", 404);
                return $item;
            }
        ];
    }

    private function setAttributes(array $fields, array $args, object $item): object
    {
        foreach ($fields as $field) {
            $field = $field->name;
            if (
                isset($args[$field]) &&
                (!empty($args[$field]) || is_bool($args[$field]))
            )
                $item->$field = $args[$field];
        }
        return $item;
    }

    public function create(): array
    {
        $createType = Types::getCreate($this->config['name']);
        /** @var $createType InputObjectType */
        return [
            'type' => Types::get($this->config['name']),
            'args' => [
                'item' => [
                    'description' => 'Properties to create the item',
                    'type' => Type::nonNull($createType)
                ]
            ],
            'resolve' => function (ContainerInterface $container, $args) use ($createType) {
                if (!$container->get(Session::class)->isAdmin())
                    return new Exception("Forbidden", 403);

                $item = new $this->config['documentClass']();
                $item = $this->setAttributes($createType->getFields(), $args['item'], $item);

                return DB::create($container, $item);
            }
        ];
    }

    public function update(): array
    {
        $updateType = Types::getCreate($this->config['name']);
        /** @var $createType InputObjectType */
        return [
            'type' => Types::get($this->config['name']),
            'description' => 'Update a ' . $this->config['naturalName'],
            'args' => [
                'id' => [
                    'description' => 'Identifier to found the item to edit',
                    'type' => Type::nonNull(Type::id())
                ],
                'item' => [
                    'description' => 'Properties to edit on this item',
                    'type' => Type::nonNull($updateType)
                ]
            ],
            'resolve' => function (ContainerInterface $container, $args) use ($updateType) {
                if (!$container->get(Session::class)->isAdmin())
                    return new Exception("Forbidden", 403);

                $item = DB::getOne($container, $this->config['documentClass'], $args['id']);
                if ($item == null)
                    return new Exception("Unknown item", 404);

                $item = $this->setAttributes($updateType->getFields(), $args['item'], $item);
                DB::persistAndFlush($container, $item);

                return $item;
            }
        ];
    }

    public function delete(): array
    {
        return [
            'type' => Type::boolean(),
            'description' => 'Delete a ' . $this->config['naturalName'],
            'args' => [
                'id' => [
                    'description' => '',
                    'type' => Type::nonNull(Type::id())
                ]
            ],
            'resolve' => function (ContainerInterface $container, $args) {
                if (!$container->get(Session::class)->isAdmin())
                    return new Exception("Forbidden", 403);

                $res = DB::delete($container, $this->config['documentClass'], [$args['id']]);
                if ($res == 0)
                    return new Exception("Unknown item", 404);

                return true;
            }
        ];
    }

}
