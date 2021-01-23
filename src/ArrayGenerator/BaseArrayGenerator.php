<?php

namespace App\ArrayGenerator;

use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Exception\ExceptionInterface;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Component\Serializer\Normalizer\AbstractObjectNormalizer;
use Symfony\Component\Serializer\Normalizer\DateTimeNormalizer;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;
use Traversable;

abstract class BaseArrayGenerator implements ArrayGeneratorInterface
{
    /**
     * @var RouterInterface
     */
    protected $router;

    /**
     * BaseArrayGenerator constructor.
     * @param RouterInterface $router
     */
    public function __construct(RouterInterface $router)
    {
        $this->router = $router;
    }

    /**
     * @param $object
     * @return array|\ArrayObject|bool|\Countable|float|int|string|Traversable|null
     * @throws ExceptionInterface
     */
    public function serialize($object)
    {
        $encoders    = [new JsonEncoder()];
        $defaultContext = [
            AbstractNormalizer::CIRCULAR_REFERENCE_HANDLER => function ($object, $format, $context) {
                return $object->getName();
            },
            AbstractObjectNormalizer::MAX_DEPTH_HANDLER => function ($innerObject, $outerObject, string $attributeName, string $format = null, array $context = []) {
                return $outerObject->getName();
            },
            AbstractObjectNormalizer::ENABLE_MAX_DEPTH => true,
        ];
        $normalizers = [new DateTimeNormalizer(), new ObjectNormalizer(null, null, null, null, null, null, $defaultContext)];

        $serializer = new Serializer($normalizers, $encoders);

        return $serializer->normalize($object, 'json');
    }
}