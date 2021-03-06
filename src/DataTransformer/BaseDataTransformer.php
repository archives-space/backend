<?php

namespace App\DataTransformer;

use Doctrine\Common\Annotations\AnnotationReader;
use Symfony\Component\PropertyInfo\Extractor\ReflectionExtractor;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Exception\ExceptionInterface;
use Symfony\Component\Serializer\Mapping\ClassDiscriminatorFromClassMetadata;
use Symfony\Component\Serializer\Mapping\Factory\ClassMetadataFactory;
use Symfony\Component\Serializer\Mapping\Loader\AnnotationLoader;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Component\Serializer\Normalizer\AbstractObjectNormalizer;
use Symfony\Component\Serializer\Normalizer\DateTimeNormalizer;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;

abstract class BaseDataTransformer implements DataTransformerInterface
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
    public function normalize($object)
    {
        return $this->getSerializer()->normalize($object, 'json');
    }

    /**
     * @param        $array
     * @param string $type
     * @return mixed
     * @throws ExceptionInterface
     */
    public function denormalize($array, string $type)
    {
        return $this->getSerializer()->denormalize($array, $type);
    }

    /**
     * @return Serializer
     */
    private function getSerializer()
    {
        $encoders = [new JsonEncoder()];

        $defaultContext = [
            AbstractNormalizer::OBJECT_TO_POPULATE         => function ($object, $format, $context) {
                return $object->getId();
            },
            AbstractNormalizer::CIRCULAR_REFERENCE_HANDLER => function ($object, $format, $context) {
                return $object->getId();
            },
            AbstractObjectNormalizer::MAX_DEPTH_HANDLER    => function ($innerObject, $outerObject, string $attributeName, string $format = null, array $context = []) {
                return $outerObject->getId();
            },
            AbstractObjectNormalizer::ENABLE_MAX_DEPTH     => true,
        ];

        $normalizers = [
            new DateTimeNormalizer(),
            new ObjectNormalizer(
                null,
                null,
                null,
                new ReflectionExtractor(),
                null,
                null,
                $defaultContext
            ),
        ];

        return new Serializer($normalizers, $encoders);
    }
}