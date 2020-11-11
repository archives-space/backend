<?php

namespace App\Document;

use App\Repository\ProductRepository;
use Doctrine\ODM\MongoDB\Mapping\Annotations as Odm;
use ApiPlatform\Core\Annotation\ApiResource;

/**
 * @ApiResource(
 *     collectionOperations={
 *          "get",
 *          "post" = { "security_post_denormalize" = "is_granted('ROLE_ADMIN', object)" }
 *     },
 * )
 * @Odm\Document(repositoryClass=ProductRepository::class)
 */
class Product
{
    /**
     * @Odm\Id
     */
    protected $id;

    /**
     * @Odm\Field(type="string")
     */
    protected $name;

    /**
     * @Odm\Field(type="float")
     */
    protected $price;

    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return mixed
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param mixed $name
     * @return Product
     */
    public function setName($name)
    {
        $this->name = $name;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getPrice()
    {
        return $this->price;
    }

    /**
     * @param mixed $price
     * @return Product
     */
    public function setPrice($price)
    {
        $this->price = $price;
        return $this;
    }
}