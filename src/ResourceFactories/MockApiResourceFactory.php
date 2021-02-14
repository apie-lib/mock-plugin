<?php

namespace Apie\MockPlugin\ResourceFactories;

use Apie\Core\Apie;
use Apie\Core\Interfaces\ApiResourceFactoryInterface;
use Apie\Core\Interfaces\ApiResourcePersisterInterface;
use Apie\Core\Interfaces\ApiResourceRetrieverInterface;
use Apie\Core\ResourceFactories\ChainableFactory;
use Apie\MockPlugin\DataLayers\MockApiResourceDataLayer;
use ReflectionClass;

/**
 * ApiResourceFactoryInterface implementation of a mock REST server. We require the original ApiResourceFactory to
 * find out which methods are allowed (GET, POST, PUT etc.).
 *
 * @TODO: add some hook to have some control over the mocking...
 */
final class MockApiResourceFactory implements ApiResourceFactoryInterface
{
    /**
     * @var MockApiResourceDataLayer
     */
    private $retriever;

    /**
     * @var Apie|ApiResourceFactoryInterface
     */
    private $apie;

    /**
     * @var string[]
     */
    private $skippedResources;

    /**
     * @param MockApiResourceDataLayer $retriever
     * @param Apie|ApiResourceFactoryInterface Apie
     * @param string[] $skippedResources resources that are not allowed to be mocked.
     */
    public function __construct(
        MockApiResourceDataLayer $retriever,
        $apie,
        array $skippedResources
    ) {
        $this->retriever = $retriever;
        $this->apie = $apie;
        $this->skippedResources = $skippedResources;
    }

    /**
     * {@inheritDoc}
     */
    public function getApiResourceRetrieverInstance(string $identifier): ApiResourceRetrieverInterface
    {
        $retriever = $this->getFactory()->getApiResourceRetrieverInstance($identifier);
        if (!in_array(get_class($retriever), $this->skippedResources)) {
            return $this->retriever;
        }

        return $retriever;
    }

    /**
     * {@inheritDoc}
     */
    public function getApiResourcePersisterInstance(string $identifier): ApiResourcePersisterInterface
    {
        $persister = $this->getFactory()->getApiResourcePersisterInstance($identifier);
        if (!in_array(get_class($persister), $this->skippedResources)) {
            return $this->retriever;
        }

        return $persister;
    }

    /**
     * {@inheritDoc}
     */
    public function hasApiResourceRetrieverInstance(string $identifier): bool
    {
        return $this->getFactory()->hasApiResourceRetrieverInstance($identifier);
    }

    /**
     * {@inheritDoc}
     */
    public function hasApiResourcePersisterInstance(string $identifier): bool
    {
        return $this->getFactory()->hasApiResourcePersisterInstance($identifier);
    }

    private function getFactory(): ApiResourceFactoryInterface
    {
        if ($this->apie instanceof ApiResourceFactoryInterface) {
            return $this->apie;
        }
        $factory = $this->apie->getApiResourceFactory();
        $prop = (new ReflectionClass($factory))->getProperty('factories');
        $prop->setAccessible(true);
        $factories = $prop->getValue($factory);
        return $this->apie = new ChainableFactory(
            array_filter($factories, function (ApiResourceFactoryInterface $factory) {
                return !$factory instanceof MockApiResourceFactory;
            })
        );
    }
}
