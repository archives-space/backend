<?php

namespace App\Utils\Catalog;

use App\Document\Catalog\Picture;
use App\Document\Catalog\Resolution;
use App\Document\User\User;
use Symfony\Component\Routing\RouterInterface;

class PictureArrayGenerator
{
    /**
     * @var RouterInterface
     */
    private $router;

    /**
     * @var CatalogArrayGenerator
     */
    private $catalogArrayGenerator;

    /**
     * @var CatalogHelpers
     */
    private $catalogHelpers;

    /**
     * UserArrayGenerator constructor.
     * @param RouterInterface       $router
     * @param CatalogArrayGenerator $catalogArrayGenerator
     * @param CatalogHelpers        $catalogHelpers
     */
    public function __construct(
        RouterInterface $router,
        CatalogArrayGenerator $catalogArrayGenerator,
        CatalogHelpers $catalogHelpers
    )
    {
        $this->router                = $router;
        $this->catalogArrayGenerator = $catalogArrayGenerator;
        $this->catalogHelpers        = $catalogHelpers;
    }

    /**
     * @param Picture $picture
     * @param bool    $fullInfo
     * @return array
     */
    public function toArray(Picture $picture, bool $fullInfo = true): array
    {
        return [
            'id'               => $picture->getId(),
            //            'placeId'          => $user->getPlaceId(),
            'name'             => $picture->getName(),
            'description'      => $picture->getDescription(),
            'source'           => $picture->getSource(),
            'edited'           => $picture->isEdited(),
            'originalFileName' => $picture->getOriginalFileName(),
            'typeMime'         => $picture->getTypeMime(),
            'hash'             => $picture->getHash(),
            'takenAt'          => $picture->getTakenAt(),
            'createdAt'        => $picture->getCreatedAt(),
            'updatedAt'        => $picture->getUpdatedAt(),
            'exif'             => [
                'id'           => $picture->getExif() ? $picture->getExif()->getId() : null,
                'model'        => $picture->getExif() ? $picture->getExif()->getModel() : null,
                'manufacturer' => $picture->getExif() ? $picture->getExif()->getManufacturer() : null,
                'aperture'     => $picture->getExif() ? $picture->getExif()->getAperture() : null,
                'iso'          => $picture->getExif() ? $picture->getExif()->getIso() : null,
                'exposure'     => $picture->getExif() ? $picture->getExif()->getExposure() : null,
                'focalLength'  => $picture->getExif() ? $picture->getExif()->getFocalLength() : null,
                'flash'        => $picture->getExif() ? $picture->getExif()->getFlash() : null,
            ],
            'resolutions'      => $this->getResolutions($picture),
            'position'         => [
                'lat' => $picture->getPosition() ? $picture->getPosition()->getLat() : null,
                'lng' => $picture->getPosition() ? $picture->getPosition()->getLng() : null,
            ],
            'license'          => [
                'name'     => $picture->getLicense() ? $picture->getLicense()->getName() : null,
                'isEdited' => $picture->getLicense() ? $picture->getLicense()->isEdited() : null,
            ],
            'catalog'          => $this->getCatalog($picture),
            'breadcrumb'       => $this->getBreadcrumb($picture, $fullInfo),
            'pictureDetail'    => $this->router->generate('PICTURE_DETAIL', [
                'id' => $picture->getId(),
            ]),
        ];
    }

    /**
     * @param Picture $picture
     * @return array[]
     */
    private function getResolutions(Picture $picture)
    {
        return array_map(function (Resolution $resolution) {
            return [
                'id'        => $resolution->getId(),
                //                'file'      => $resolution->getFile(),
                'width'     => $resolution->getWidth(),
                'height'    => $resolution->getHeight(),
                'size'      => $resolution->getSize(),
                'sizeLabel' => $resolution->getSizeLabel(),
                //                'key'       => $resolution->getKey(),
            ];
        }, $picture->getResolutions()->toArray());
    }

    /**
     * @param Picture $picture
     * @return array|null
     */
    private function getCatalog(Picture $picture)
    {
        if (!$catalog = $picture->getCatalog()) {
            return null;
        }
        return [
            'id'            => $catalog->getId(),
            'name'          => $catalog->getName(),
            'catalogDetail' => $this->router->generate('CATALOG_DETAIL', [
                'id' => $catalog->getId(),
            ]),
        ];
    }

    /**
     * @param Picture $picture
     * @return array|null
     */
    private function getBreadcrumb(Picture $picture, bool $fullInfo)
    {
        if (!$fullInfo) {
            return null;
        }
        if (!$catalog = $picture->getCatalog()) {
            return null;
        }
        return $this->catalogHelpers->getBreadCrumbs($catalog)->toArray();
    }
}