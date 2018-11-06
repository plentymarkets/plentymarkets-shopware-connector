<?php

namespace PlentyConnector\tests\Unit\Connector;

use ArrayIterator;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Ramsey\Uuid\Uuid;
use SystemConnector\Connector;
use SystemConnector\Console\OutputHandler\OutputHandlerInterface;
use SystemConnector\DefinitionProvider\DefinitionProvider;
use SystemConnector\ServiceBus\Command\CommandInterface;
use SystemConnector\ServiceBus\CommandFactory\CommandFactoryInterface;
use SystemConnector\ServiceBus\CommandType;
use SystemConnector\ServiceBus\Query\QueryInterface;
use SystemConnector\ServiceBus\QueryFactory\QueryFactoryInterface;
use SystemConnector\ServiceBus\QueryType;
use SystemConnector\ServiceBus\ServiceBusInterface;
use SystemConnector\TransferObject\TransferObjectInterface;
use SystemConnector\ValueObject\Definition\Definition;

class ConnectorTest extends TestCase
{
    public function test_handle_single_definition_fetch_all()
    {
        $testElement = $this->createMock(TransferObjectInterface::class);
        $testElement->expects($this->once())->method('getType')->willReturn('TestType');

        $command = $this->createMock(CommandInterface::class);
        $query = $this->createMock(QueryInterface::class);

        $serviceBus = $this->createMock(ServiceBusInterface::class);
        $serviceBus->method('handle')
            ->withConsecutive($query, $command)
            ->willReturnOnConsecutiveCalls([$testElement], true);

        $queryFactory = $this->createMock(QueryFactoryInterface::class);
        $queryFactory->expects($this->once())->method('create')->with(
            'TestOriginAdapter',
            'TestType',
            QueryType::ALL
        )->willReturn($query);

        $commandFactory = $this->createMock(CommandFactoryInterface::class);
        $commandFactory->expects($this->once())->method('create')->with(
            'TestDestinationAdapter',
            'TestType',
            CommandType::HANDLE,
            0,
            $testElement
        )->willReturn($command);

        $definition = $this->createMock(Definition::class);
        $definition->method('getOriginAdapterName')->willReturn('TestOriginAdapter');
        $definition->method('getDestinationAdapterName')->willReturn('TestDestinationAdapter');
        $definition->method('getObjectType')->willReturn('TestType');
        $definition->method('getPriority')->willReturn(0);
        $definition->method('isActive')->willReturn(true);

        $definitionProvider = new DefinitionProvider(
            new ArrayIterator([$definition]),
            new ArrayIterator(),
            new ArrayIterator()
        );

        $outputHandler = $this->createMock(OutputHandlerInterface::class);
        $logger = $this->createMock(LoggerInterface::class);

        $connector = new Connector(
            $serviceBus,
            $queryFactory,
            $commandFactory,
            $outputHandler,
            $definitionProvider,
            $logger
        );

        $connector->handle(QueryType::ALL, 'TestType');
    }

    public function test_handle_single_definition_fetch_one()
    {
        $uuid = Uuid::uuid4()->toString();

        $testElement = $this->createMock(TransferObjectInterface::class);
        $testElement->expects($this->once())->method('getType')->willReturn('TestType');

        $command = $this->createMock(CommandInterface::class);
        $query = $this->createMock(QueryInterface::class);

        $serviceBus = $this->createMock(ServiceBusInterface::class);
        $serviceBus->method('handle')
            ->withConsecutive($query, $command)
            ->willReturnOnConsecutiveCalls([$testElement], true);

        $queryFactory = $this->createMock(QueryFactoryInterface::class);
        $queryFactory->expects($this->once())->method('create')->with(
            'TestOriginAdapter',
            'TestType',
            QueryType::ONE,
            $uuid
        )->willReturn($query);

        $commandFactory = $this->createMock(CommandFactoryInterface::class);
        $commandFactory->expects($this->once())->method('create')->with(
            'TestDestinationAdapter',
            'TestType',
            CommandType::HANDLE,
            0,
            $testElement
        )->willReturn($command);

        $definition = $this->createMock(Definition::class);
        $definition->method('getOriginAdapterName')->willReturn('TestOriginAdapter');
        $definition->method('getDestinationAdapterName')->willReturn('TestDestinationAdapter');
        $definition->method('getObjectType')->willReturn('TestType');
        $definition->method('getPriority')->willReturn(0);
        $definition->method('isActive')->willReturn(true);

        $definitionProvider = new DefinitionProvider(
            new ArrayIterator([$definition]),
            new ArrayIterator(),
            new ArrayIterator()
        );

        $outputHandler = $this->createMock(OutputHandlerInterface::class);
        $logger = $this->createMock(LoggerInterface::class);

        $connector = new Connector(
            $serviceBus,
            $queryFactory,
            $commandFactory,
            $outputHandler,
            $definitionProvider,
            $logger
        );

        $connector->handle(QueryType::ONE, 'TestType', $uuid);
    }

    public function test_handle_single_definition_fetch_changed()
    {
        $testElement = $this->createMock(TransferObjectInterface::class);
        $testElement->expects($this->once())->method('getType')->willReturn('TestType');

        $command = $this->createMock(CommandInterface::class);
        $query = $this->createMock(QueryInterface::class);

        $serviceBus = $this->createMock(ServiceBusInterface::class);
        $serviceBus->method('handle')
            ->withConsecutive($query, $command)
            ->willReturnOnConsecutiveCalls([$testElement], true);

        $queryFactory = $this->createMock(QueryFactoryInterface::class);
        $queryFactory->expects($this->once())->method('create')->with(
            'TestOriginAdapter',
            'TestType',
            QueryType::CHANGED
        )->willReturn($query);

        $commandFactory = $this->createMock(CommandFactoryInterface::class);
        $commandFactory->expects($this->once())->method('create')->with(
            'TestDestinationAdapter',
            'TestType',
            CommandType::HANDLE,
            0,
            $testElement
        )->willReturn($command);

        $definition = $this->createMock(Definition::class);
        $definition->method('getOriginAdapterName')->willReturn('TestOriginAdapter');
        $definition->method('getDestinationAdapterName')->willReturn('TestDestinationAdapter');
        $definition->method('getObjectType')->willReturn('TestType');
        $definition->method('getPriority')->willReturn(0);
        $definition->method('isActive')->willReturn(true);

        $definitionProvider = new DefinitionProvider(
            new ArrayIterator([$definition]),
            new ArrayIterator(),
            new ArrayIterator()
        );

        $outputHandler = $this->createMock(OutputHandlerInterface::class);
        $logger = $this->createMock(LoggerInterface::class);

        $connector = new Connector(
            $serviceBus,
            $queryFactory,
            $commandFactory,
            $outputHandler,
            $definitionProvider,
            $logger
        );

        $connector->handle(QueryType::CHANGED, 'TestType');
    }
}
