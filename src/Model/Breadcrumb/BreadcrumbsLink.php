<?php

namespace App\Model\Breadcrumb;

class BreadcrumbsLink
{
    /**
     * @var string
     */
    private $id;

    /**
     * @var string
     */
    private $title;

    /**
     * @var string
     */
    private $url;

    /**
     * @var string
     */
    private $slug;

    /**
     * @var boolean
     */
    private $isActual = false;

    /**
     * @return string
     */
    public function getId(): string
    {
        return $this->id;
    }

    /**
     * @param string $id
     * @return BreadcrumbsLink
     */
    public function setId(string $id): BreadcrumbsLink
    {
        $this->id = $id;
        return $this;
    }

    /**
     * @return string
     */
    public function getTitle(): string
    {
        return $this->title;
    }

    /**
     * @param string $title
     * @return BreadcrumbsLink
     */
    public function setTitle(string $title): BreadcrumbsLink
    {
        $this->title = $title;
        return $this;
    }

    /**
     * @return string
     */
    public function getUrl(): string
    {
        return $this->url;
    }

    /**
     * @param string $url
     * @return BreadcrumbsLink
     */
    public function setUrl(string $url): BreadcrumbsLink
    {
        $this->url = $url;
        return $this;
    }

    /**
     * @return bool
     */
    public function isActual(): bool
    {
        return $this->isActual;
    }

    /**
     * @param bool $isActual
     * @return BreadcrumbsLink
     */
    public function setIsActual(bool $isActual): BreadcrumbsLink
    {
        $this->isActual = $isActual;
        return $this;
    }

    /**
     * @param string $slug
     * @return $this
     */
    public function setSlug(string $slug): self
    {
        $this->slug = $slug;
        return $this;
    }

    /**
     * @return string
     */
    public function getSlug(): string
    {
        return $this->slug;
    }
}