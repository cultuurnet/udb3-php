<?php
/**
 * @file
 */

namespace CultuurNet\UDB3\Hydra\Symfony;

use CultuurNet\UDB3\Hydra\PageUrlGenerator as PageUrlGeneratorInterface;
use Symfony\Component\HttpFoundation\ParameterBag;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class PageUrlGenerator implements PageUrlGeneratorInterface
{
    /**
     * @var ParameterBag
     */
    private $query;

    /**
     * @var UrlGeneratorInterface
     */
    private $urlGenerator;

    /**
     * @var string
     */
    private $routeName;

    /**
     * @var string
     */
    private $pageParameterName;

    /**
     * @param ParameterBag $query
     * @param UrlGeneratorInterface $urlGenerator
     * @param string $routeName
     * @param string $pageParameterName
     */
    public function __construct(
        ParameterBag $query,
        UrlGeneratorInterface $urlGenerator,
        $routeName,
        $pageParameterName = 'page'
    ) {
        $this->query = $query;
        $this->pageParameterName = $pageParameterName;
        $this->urlGenerator = $urlGenerator;
        $this->routeName = $routeName;
    }

    /**
     * @inheritdoc
     */
    public function urlForPage($pageNumber)
    {
        $query = clone $this->query;

        if ($pageNumber === 0) {
            $query->remove($this->pageParameterName);
        } else {
            $query->set($this->pageParameterName, $pageNumber);
        }

        return $this->urlGenerator->generate(
            $this->routeName,
            $query->all(),
            UrlGeneratorInterface::ABSOLUTE_URL
        );
    }
}
