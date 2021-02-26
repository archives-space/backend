<?php

namespace App\GraphQL\Type\Scalar;

use GraphQL\Error\Error;
use GraphQL\Language\AST\Node;
use GraphQL\Type\Definition\CustomScalarType;
use GraphQL\Utils\Utils;

class ChoiceString extends CustomScalarType
{
    public $name = 'ChoiceString';
    private array $choices;
    private bool $strictCasing;

    /**
     * @param array $choices Array of string choice
     * @param bool $strictCasing If you want to also check for the lower/upper case
     */
    public function __construct(array $choices, bool $strictCasing = false) {
        $this->choices = $strictCasing ? array_map(fn ($c) => strtoupper($c), $choices) : $choices;
        $this->strictCasing = $strictCasing;
        parent::__construct();
    }

    /**
     * Output parsing
     * @param string $value
     * @return string
     */
    public function serialize($value)
    {
        return $value;
    }

    public function parseLiteral(Node $valueNode, ?array $variables = null)
    {
        return $this->parseValue($valueNode->toArray()['value']);
    }

    /**
     * Input validation and parsing
     * @param mixed $value
     * @return mixed
     * @throws Error
     */
    public function parseValue($value)
    {
        if (!in_array($this->strictCasing ? $value : strtolower($value), $this->choices)) {
            $choiceStr = implode('|', $this->choices);
            throw new Error("Invalid choice string expected one of " . $choiceStr . " but got : " . Utils::printSafeJson($value));
        }
        return $value;
    }
}
