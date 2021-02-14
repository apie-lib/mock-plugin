<?php

namespace Apie\MockPlugin;

use Apie\Core\Interfaces\ApiResourceFactoryInterface;
use Apie\Core\PluginInterfaces\ApieAwareInterface;
use Apie\Core\PluginInterfaces\ApieAwareTrait;
use Apie\Core\PluginInterfaces\ApiResourceFactoryProviderInterface;

final class MockPlugin implements ApiResourceFactoryProviderInterface, ApieAwareInterface
{
    use ApieAwareTrait;

    private $ignoreList = [];

    public function __construct(array $ignoreList = [])
    {
        $this->ignoreList = $ignoreList;
    }

    public function getApiResourceFactory(array $next = []): ApiResourceFactoryInterface
    {
        $apie = $this->getApie();

        return new MockApiResourceFactory(
            new MockApiResourceDataLayer(
                $this->getApie()->getCacheItemPool(),
                $this->getApie()->getIdentifierExtractor(),
                $this->getApie()->getObjectAccess()
            ),
            $apie,
            $this->ignoreList
        );
    }
}
