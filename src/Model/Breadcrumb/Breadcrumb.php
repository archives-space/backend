<?php

namespace App\Model\Breadcrumb;

/**
 * Class Breadcrumb
 * @package App\Model\Catalog
 */
class Breadcrumb
{
    /**
     * @var BreadcrumbsLink[]
     */
    private $links = [];

    /**
     * @return BreadcrumbsLink[]
     */
    public function getLinks(): array
    {
        return $this->links;
    }

    /**
     * @return BreadcrumbsLink[]
     */
    public function getReversedLinks(): array
    {
        return array_reverse($this->links);
    }

    /**
     * @param BreadcrumbsLink $link
     * @return Breadcrumb
     */
    public function addLink(BreadcrumbsLink $link): Breadcrumb
    {
        $this->links[] = $link;
        return $this;
    }

    /**
     * @return array[]
     */
    public function toArray()
    {
        return array_map(function (BreadcrumbsLink $breadcrumbsLink) {
            return [
                'id'       => $breadcrumbsLink->getId(),
                'title'    => $breadcrumbsLink->getTitle(),
                'url'      => $breadcrumbsLink->getUrl(),
                'isActual' => $breadcrumbsLink->isActual(),
            ];
        }, $this->getReversedLinks());
    }
}