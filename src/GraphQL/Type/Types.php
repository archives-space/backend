<?php
namespace App\GraphQL\Type;

use App\Document;
use App\GraphQL\Type\Scalar;
use App\GraphQL\Type\Scalar\ChoiceString;
use App\Utils\GraphQLTypeConvertor;
use GraphQL\Type\Definition\ObjectType;

class Types
{
    private static array $types = [];
    private static array $createTypes = [];
    private static array $updateTypes = [];

    private static array $data = [];

    public function __construct()
    {
        // foreach (self::$types as $type) {
        //   $name = strtoupper(substr($type, 0, 1) . substr($type, 1));
        //   self::$$name = function () use ($type, $name) {
        //     return self::$data[$name] ?: (self::$data[$name] = new $type());
        //   };
        // }
    }

    public static function load() {
        self::$types = [
            'DateTime' => Scalar\DateTime::class,
            'PaginationMetadata' => PaginationMetadata::class,
            'Url' => Scalar\Url::class,
            'NonEmpty' => Scalar\NonEmpty::class,
            'ChoiceString' => ChoiceString::class,
            //'Range' => Range::class,
            //'StringOrNumber' => StringOrNumber::class

        ];
        // we will need to cache all this type conversion but later
        $additionalTypes = [
            Document\User\User::class
        ];
        foreach ($additionalTypes as $additional) {
            $res = GraphQLTypeConvertor::convert($additional);
            self::$types[$res['name']] = $res['type'];
            self::$createTypes[$res['name']] = $res['createType'];
            self::$updateTypes[$res['name']] = $res['updateType'];
        }
    }

    public static function getCreate($name) {
        return self::$createTypes[$name];
    }

    public static function getUpdate($name) {
        return self::$updateTypes[$name];
    }

    public static function getRaw($name) {
        return self::$types[$name];
    }

    /**
     * @param $name
     * @param mixed ...$params
     * @return mixed
     */
    public static function get($name, ...$params) {
        $formattedName = $name;
        if (!isset(self::$data[$formattedName])) {
            if (gettype(self::$types[$name]) === 'array') {
                $instance = new ObjectType(self::$types[$name]);
            } else {
                if (count($params) > 0) {
                    $instance = new self::$types[$name](...$params);
                } else {
                    $instance = new self::$types[$name]();
                }
            }
            self::$data[$formattedName] = $instance;
        }
        return self::$data[$formattedName];
    }

}
