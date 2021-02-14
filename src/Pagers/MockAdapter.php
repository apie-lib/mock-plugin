<?php

namespace Apie\MockPlugin\Pagers;

use Apie\Core\SearchFilters\SearchFilterHelper;
use Apie\Core\SearchFilters\SearchFilterRequest;
use Apie\MockPlugin\DataLayers\MockApiResourceDataLayer;
use Apie\ObjectAccessNormalizer\ObjectAccess\ObjectAccessInterface;
use Pagerfanta\Adapter\AdapterInterface;

class MockAdapter implements AdapterInterface
{
    /**
     * @var MockApiResourceDataLayer
     */
    private $dataLayer;

    /**
     * @var (int|string)[]
     */
    private $idList;

    /**
     * @var array
     */
    private $searches;

    /**
     * @var string
     */
    private $resourceClass;

    /**
     * @var array
     */
    private $context;

    /**
     * @var ObjectAccessInterface
     */
    private $propertyAccessor;

    public function __construct(
        MockApiResourceDataLayer $dataLayer,
        array $idList,
        array $searches,
        string $resourceClass,
        array $context,
        ObjectAccessInterface $propertyAccessor
    ) {
        $this->dataLayer = $dataLayer;
        $this->idList = $idList;
        $this->searches = $searches;
        $this->resourceClass = $resourceClass;
        $this->context = $context;
        $this->propertyAccessor = $propertyAccessor;
    }

    public function getNbResults()
    {
        return count($this->idList);
    }

    public function getSlice($offset, $length)
    {
        $searchFilterRequest = new SearchFilterRequest(
            $offset,
            $length,
            $this->searches
        );

        return array_map(
            function ($id) {
                return $this->dataLayer->retrieve($this->resourceClass, $id, $this->context);
            },
            SearchFilterHelper::applySearchFilter($this->idList, $searchFilterRequest, $this->propertyAccessor)
        );
    }
}
