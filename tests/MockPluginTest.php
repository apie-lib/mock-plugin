<?php

namespace Apie\Tests\MockPlugin;

use Apie\ApplicationInfoPlugin\ApplicationInfoPlugin;
use Apie\ApplicationInfoPlugin\DataLayers\ApplicationInfoRetriever;
use Apie\Core\Apie;
use Apie\MockPlugin\DataLayers\MockApiResourceDataLayer;
use Apie\MockPlugin\MockPlugin;
use PHPUnit\Framework\TestCase;

class MockPluginTest extends TestCase
{
    /**
     * @var Apie
     */
    private $apie;

    protected function setUp(): void
    {
        $this->apie = new Apie([new MockPlugin(), new ApplicationInfoPlugin()], true, null);
    }

    public function test_resource_retriever_is_overwritten()
    {
        $factory = $this->apie->getApiResourceFactory();
        $this->assertInstanceOf(
            MockApiResourceDataLayer::class,
            $factory->getApiResourceRetrieverInstance(ApplicationInfoRetriever::class)
        );
        $this->assertFalse($factory->hasApiResourcePersisterInstance(ApplicationInfoRetriever::class));
    }
}
