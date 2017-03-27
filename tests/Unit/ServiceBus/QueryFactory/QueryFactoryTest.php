<?php

namespace PlentyConnector\tests\Unit\ServiceBus\QueryFactory;

use PHPUnit\Framework\TestCase;
use PlentyConnector\Connector\ServiceBus\Query\QueryInterface;
use PlentyConnector\Connector\ServiceBus\QueryFactory\QueryFactory;
use PlentyConnector\Connector\ServiceBus\QueryGenerator\QueryGeneratorInterface;
use PlentyConnector\Connector\ServiceBus\QueryType;
use PlentyConnector\Connector\TransferObject\TransferObjectInterface;
use Ramsey\Uuid\Uuid;

/**
 * Class QueryFactoryTest.
 */
class QueryFactoryTest extends TestCase
{
    public function test_generate_fetch_all_query()
    {
        $transferObject = $this->createMock(TransferObjectInterface::class);
        $transferObject->expects($this->any())->method('getType')->willReturn('TestType');

        $queryMock = $this->createMock(QueryInterface::class);

        $generator = $this->createMock(QueryGeneratorInterface::class);
        $generator->expects($this->once())->method('supports')->with('TestType')->willReturn(true);
        $generator->expects($this->once())->method('generateFetchAllQuery')->with('TestAdapter')->willReturn($queryMock);

        $queryFactory = new QueryFactory();
        $queryFactory->addGenerator($generator);

        $query = $queryFactory->create('TestAdapter', 'TestType', QueryType::ALL);

        $this->assertEquals($query, $queryMock);
    }

    public function test_generate_fetch_changed_query()
    {
        $transferObject = $this->createMock(TransferObjectInterface::class);
        $transferObject->expects($this->any())->method('getType')->willReturn('TestType');

        $queryMock = $this->createMock(QueryInterface::class);

        $generator = $this->createMock(QueryGeneratorInterface::class);
        $generator->expects($this->once())->method('supports')->with('TestType')->willReturn(true);
        $generator->expects($this->once())->method('generateFetchChangedQuery')->with('TestAdapter')->willReturn($queryMock);

        $queryFactory = new QueryFactory();
        $queryFactory->addGenerator($generator);

        $query = $queryFactory->create('TestAdapter', 'TestType', QueryType::CHANGED);

        $this->assertEquals($query, $queryMock);
    }

    public function test_generate_fetch_one_query()
    {
        $uuid = Uuid::uuid4()->toString();

        $transferObject = $this->createMock(TransferObjectInterface::class);
        $transferObject->expects($this->any())->method('getType')->willReturn('TestType');

        $queryMock = $this->createMock(QueryInterface::class);

        $generator = $this->createMock(QueryGeneratorInterface::class);
        $generator->expects($this->once())->method('supports')->with('TestType')->willReturn(true);
        $generator->expects($this->once())->method('generateFetchQuery')->with('TestAdapter', $uuid)->willReturn($queryMock);

        $queryFactory = new QueryFactory();
        $queryFactory->addGenerator($generator);

        $query = $queryFactory->create('TestAdapter', 'TestType', QueryType::ONE, $uuid);

        $this->assertEquals($query, $queryMock);
    }
}
