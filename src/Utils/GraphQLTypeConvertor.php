<?php

namespace App\Utils;

use App\GraphQL\Annotations\IgnoredField;
use App\GraphQL\Annotations\IncludeTimestamps;
use App\GraphQL\Annotations\ReadOnlyField;
use App\GraphQL\Annotations\RequiredField;
use App\GraphQL\Type\Types;
use Doctrine\Common\Annotations\AnnotationReader;
use Doctrine\ODM\MongoDB\Mapping\Annotations\EmbedMany;
use Doctrine\ODM\MongoDB\Mapping\Annotations\EmbedOne;
use Doctrine\ODM\MongoDB\Mapping\Annotations\Field;
use Doctrine\ODM\MongoDB\Mapping\Annotations\ReferenceMany;
use Doctrine\ODM\MongoDB\Mapping\Annotations\ReferenceOne;
use Exception;
use GraphQL\Type\Definition\InputObjectType;
use GraphQL\Type\Definition\ScalarType;
use GraphQL\Type\Definition\Type;
use ReflectionClass;
use ReflectionException;
use ReflectionProperty;

class GraphQLTypeConvertor
{
    /**
     * @param string $raw
     * @return ScalarType
     * @throws Exception
     */
    public static function standardTypeConversion(string $raw)
    {
        switch ($raw) {
            case 'string':
                return Type::string();
            case 'integer':
            case 'int':
                return Type::int();
            case 'float':
                return Type::float();
            case 'boolean':
            case 'bool':
                return Type::boolean();
            case 'date':
                return Types::get('DateTime');
            default:
                throw new Exception('Unexpected value: ' . $raw);
        }
    }

    /**
     * @param ReflectionProperty $property
     * @return string|null
     */
    public static function getVarAnnotation(ReflectionProperty $property): ?string
    {
        preg_match_all('#@(.*?)\n#s', $property->getDocComment(), $annotations);
        $var = array_filter($annotations[0], fn($a) => strpos($a, '@var') !== false);
        if (empty($var)) return null;
        $var = trim(reset($var));
        $var = explode(' ', $var);
        if (empty($var)) return null;
        $var = $var[1];
        $var = explode('|', $var);
        if (empty($var)) return null;
        return $var[0];
    }

    public static function parseEmbedOrReference($ref)
    {
        $type = $ref->targetDocument;
        $type = explode('\\', $type);
        $type = $type[count($type) - 1];
        return [$type, Types::get($type)];
    }

    public static function parseDescription(string $docComment): ?string
    {
        $firstLine = explode("\n", $docComment);
        return trim(substr($firstLine[1], strpos($firstLine[1], '*') + 1));
    }

    /**
     * @param string $documentClass
     * @return array
     * @throws ReflectionException
     * @throws Exception
     */
    public static function convert(string $documentClass): array
    {
        $fields = [];
        $createFields = $updateFields = [];
        $reflexion = new ReflectionClass($documentClass);
        $reader = new AnnotationReader();

        $includeTimestamps = $reader->getClassAnnotation(
            $reflexion,
            IncludeTimestamps::class
        );

        foreach ($reflexion->getProperties() as $prop) {
            $ignored = $reader->getPropertyAnnotation($prop, IgnoredField::class);
            if (
                ($includeTimestamps === null && ($prop->name === 'createdAt' || $prop->name === 'updatedAt')) ||
                $ignored !== null
            ) continue;

            $desc = self::parseDescription($prop->getDocComment());
            $fieldAnnotation = $reader->getPropertyAnnotation($prop, Field::class);
            $type = null;
            if ($fieldAnnotation !== null) {
                /** @var $fieldAnnotation Field */
                // case of a collection
                if ($fieldAnnotation->type == 'collection') {
                    $var = self::getVarAnnotation($prop);
                    $var = str_replace('[]', '', $var);
                    $type = Type::listOf(self::standardTypeConversion($var));
                } else {
                    $type = self::standardTypeConversion($fieldAnnotation->type);
                }
            }

            $relationType = null;
            $relationName = null;
            $a = $reader->getPropertyAnnotation($prop, EmbedOne::class);
            if ($a !== null) {
                $relation = self::parseEmbedOrReference($a);
                $relationName = $relation[0];
                $type = $relation[1];
                $relationType = 'embedOne';
            }

            $a = $reader->getPropertyAnnotation($prop, EmbedMany::class);
            if ($a !== null) {
                $relation = self::parseEmbedOrReference($a);
                $relationName = $relation[0];
                $type = Type::listOf($relation[1]);
                $relationType = 'embedMany';
            }

            $a = $reader->getPropertyAnnotation($prop, ReferenceOne::class);
            if ($a !== null) {
                $relation = self::parseEmbedOrReference($a);
                $relationName = $relation[0];
                $type = $relation[1];
                $relationType = 'referenceOne';
            }

            $a = $reader->getPropertyAnnotation($prop, ReferenceMany::class);
            if ($a !== null) {
                $relation = self::parseEmbedOrReference($a);
                $relationName = $relation[0];
                $type = Type::listOf($relation[1]);
                $relationType = 'referenceMany';
            }

            if ($type !== null) {
                $fields[$prop->name] = [
                    'description' => $desc,
                    'type' => $type
                ];

                $readOnly = $reader->getPropertyAnnotation($prop, ReadOnlyField::class);
                $required = $reader->getPropertyAnnotation($prop, RequiredField::class);
                // only include these fields if it's not a readonly
                if ($readOnly === null) { // if the readOnly annotation is not present
                    $name = $prop->name;
                    $updateType = $createType = $type;
                    if ($relationType !== null) {
                        // case of a relation
                        if ($relationType === 'referenceOne') {
                            // provide a id field
                            $name .= 'Id'; // append Id to the name
                            $createType = $updateType = Type::string();
                        }
                        if ($relationType === 'referenceMany') {
                            // provide a list of id field
                            $name .= 'Ids'; // append Id to the name
                            $createType = $updateType = Type::listOf(Type::string());
                        }
                        if ($relationType === 'embedOne') {
                            $createType = Types::getCreate($relationName);
                            $updateType = Types::getUpdate($relationName);
                        }
                        if ($relationType === 'embedMany') {
                            $createType = Type::listOf(Types::getCreate($relationName));
                            $updateType = Type::listOf(Types::getUpdate($relationName));
                        }
                    }
                    if ($required !== null) { // if the required annotation is present
                        $createType = Type::nonNull($type);
                    }

                    $createFields[$name] = [
                        'description' => $desc,
                        'type' => $createType
                    ];
                    $updateFields[$name] = [
                        'description' => $desc,
                        'type' => $updateType
                    ];
                }
            }
        }
//
//    if ($includeTimestamps === null) {
//      unset($fields['createdAt']);
//      unset($fields['updatedAt']);
//    }

        return [
            'name' => $reflexion->getShortName(),
            'type' => [
                'name' => $reflexion->getShortName(),
                'description' => self::parseDescription($reflexion->getDocComment()),
                'fields' => array_merge($fields, [
                    'id' => ['type' => Type::id()]
                ])
            ],
            'createType' => new InputObjectType([
                'name' => $reflexion->getShortName() . 'CreateInput',
                'fields' => $createFields
            ]),
            'updateType' => new InputObjectType([
                'name' => $reflexion->getShortName() . 'UpdateInput',
                'fields' => $updateFields
            ])
        ];
    }
}
