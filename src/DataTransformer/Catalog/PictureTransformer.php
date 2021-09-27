<?php

namespace App\DataTransformer\Catalog;

use App\Document\Catalog\Picture;
use Symfony\Component\Serializer\Exception\ExceptionInterface;

class PictureTransformer extends BaseCatalogTransformer
{
    /**
     * @param Picture $object
     * @return mixed
     */
    public function toArray($object, bool $fullInfo = true)
    {
        return [
            'id'               => $object->getId(),
            'slug'             => $object->getSlug(),
            'originalFilename' => $object->getOriginalFilename(),
            'createdAt'        => $object->getCreatedAt(),
            'updatedAt'        => $object->getUpdatedAt(),
            'catalog'          => $fullInfo ? [
                'id'   => $object->getCatalog()->getId(),
                'name' => $object->getCatalog()->getName(),
            ] : null,
            'validatedVersion' => $this->versionToArray($object->getValidatedVersion()),
            'versions'         => array_map(function (Picture\Version $version) {
                return $this->versionToArray($version);
            }, $object->getVersions()->toArray()),
            'file'             => $object->getFile() ? [
                'path'             => $object->getFile()->getPath(),
                'mimeType'         => $object->getFile()->getMimeType(),
                'hash'             => $object->getFile()->getHash(),
                'originalFileName' => $object->getFile()->getOriginalFileName(),
                'size'             => $object->getFile()->getSize(),
            ] : null,
            'detail'           => $this->router->generate('PICTURE_DETAIL', [
                'id' => $object->getId(),
            ]),
            'breadcrumbs'      => $fullInfo ? $this->getBreadcrumb($object) : null,
        ];
    }

    private function versionToArray(?Picture\Version $version)
    {
        if (!$version) {
            return;
        }
        return [
            'id'          => $version->getId(),
            'name'        => $version->getName(),
            'description' => $version->getDescription(),
            'source'      => $version->getSource(),
            'takenAt'     => $version->getTakenAt(),
            'createdAt'   => $version->getCreatedAt(),
        ];
    }

    /**
     * @param $array
     * @return Picture
     * @throws ExceptionInterface
     */
    public function toObject($array): Picture
    {
        return $this->denormalize($array, Picture::class);
    }

    /**
     * @param Picture $picture
     * @return array|null
     */
    private function getBreadcrumb(Picture $picture)
    {
        if (!$catalog = $picture->getCatalog()) {
            return null;
        }
        return $this->catalogHelpers->getBreadCrumbs($catalog)->toArray();
    }
}