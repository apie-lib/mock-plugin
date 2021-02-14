<?php
namespace Apie\Tests\MockPlugin\ResourceFactories;

use Apie\Core\Interfaces\ApiResourceFactoryInterface;
use Apie\Core\Interfaces\ApiResourcePersisterInterface;
use Apie\Core\Interfaces\ApiResourceRetrieverInterface;
use Apie\Core\SearchFilters\SearchFilterRequest;
use Apie\MockPlugin\DataLayers\MockApiResourceDataLayer;
use Apie\MockPlugin\ResourceFactories\MockApiResourceFactory;
use PHPUnit\Framework\TestCase;
use RuntimeException;

class MockApiResourceFactoryTest extends TestCase
{
    private $skippedRetriever;

    private $skippedPersister;

    private $testItem;

    private $retriever;

    private $factory;

    protected function setUp(): void
    {
        $this->skippedRetriever = new class implements ApiResourceRetrieverInterface
        {
            public function retrieve(string $resourceClass, $id, array $context)
            {
                throw new RuntimeException('This should not be called');
            }

            public function retrieveAll(string $resourceClass, array $context, SearchFilterRequest $searchFilterRequest
            ): iterable {
                throw new RuntimeException('This should not be called');
            }
        };
        $this->skippedPersister = new class implements ApiResourcePersisterInterface
        {
            public function persistNew($resource, array $context = [])
            {
                throw new RuntimeException('This should not be called');
            }

            public function persistExisting($resource, $int, array $context = [])
            {
                throw new RuntimeException('This should not be called');
            }

            public function remove(string $resourceClass, $id, array $context)
            {
                throw new RuntimeException('This should not be called');
            }
        };

        $this->retriever = $this->prophesize(MockApiResourceDataLayer::class);
        $this->factory = $this->prophesize(ApiResourceFactoryInterface::class);
        $this->testItem = new MockApiResourceFactory(
            $this->retriever->reveal(),
            $this->factory->reveal(),
            [get_class($this->skippedRetriever), get_class($this->skippedPersister)]
        );
    }

    public function testGetApiResourceRetrieverInstance()
    {
        $retriever = $this->prophesize(ApiResourceRetrieverInterface::class)->reveal();
        $this->factory->getApiResourceRetrieverInstance('donut-retriever')->shouldBeCalled()->willReturn($retriever);

        $this->assertInstanceOf(
            MockApiResourceDataLayer::class,
            $this->testItem->getApiResourceRetrieverInstance('donut-retriever')
        );
    }

    public function testGetApiResourceRetrieverInstance_skipped_resource()
    {
        $this->factory->getApiResourceRetrieverInstance('donut-retriever')->shouldBeCalled()->willReturn($this->skippedRetriever);

        $this->assertEquals(
            $this->skippedRetriever,
            $this->testItem->getApiResourceRetrieverInstance('donut-retriever')
        );
    }

    public function testGetApiResourcePersisterInstance()
    {
        $persister = $this->prophesize(ApiResourcePersisterInterface::class)->reveal();
        $this->factory->getApiResourcePersisterInstance('donut-retriever')->shouldBeCalled()->willReturn($persister);

        $this->assertInstanceOf(
            MockApiResourceDataLayer::class,
            $this->testItem->getApiResourcePersisterInstance('donut-retriever')
        );
    }

    public function testGetApiResourcePersisterInstance_skipped_resource()
    {
        $this->factory->getApiResourcePersisterInstance('donut-retriever')->shouldBeCalled()->willReturn($this->skippedPersister);

        $this->assertEquals(
            $this->skippedPersister,
            $this->testItem->getApiResourcePersisterInstance('donut-retriever')
        );
    }
}
