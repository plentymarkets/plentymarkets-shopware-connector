<?php

namespace PlentyConnector\tests\Unit\Connector;

use PHPUnit\Framework\TestCase;
use PlentyConnector\Connector\Connector;
use PlentyConnector\Connector\ServiceBus\Command\CommandInterface;
use PlentyConnector\Connector\ServiceBus\CommandFactory\CommandFactoryInterface;
use PlentyConnector\Connector\ServiceBus\CommandType;
use PlentyConnector\Connector\ServiceBus\Query\QueryInterface;
use PlentyConnector\Connector\ServiceBus\QueryFactory\QueryFactoryInterface;
use PlentyConnector\Connector\ServiceBus\QueryType;
use PlentyConnector\Connector\ServiceBus\ServiceBusInterface;
use PlentyConnector\Connector\TransferObject\TransferObjectInterface;
use PlentyConnector\Connector\ValueObject\Definition\Definition;
use PlentyConnector\Console\OutputHandler\OutputHandlerInterface;
use Psr\Log\LoggerInterface;
use Ramsey\Uuid\Uuid;

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
        $definition->expects($this->any())->method('getOriginAdapterName')->willReturn('TestOriginAdapter');
        $definition->expects($this->any())->method('getDestinationAdapterName')->willReturn('TestDestinationAdapter');
        $definition->expects($this->any())->method('getObjectType')->willReturn('TestType');
        $definition->method('getPriority')->willReturn(0);
        $definition->method('isActive')->willReturn(true);

        $outputHandler = $this->createMock(OutputHandlerInterface::class);
        $logger = $this->createMock(LoggerInterface::class);

        $connector = new Connector($serviceBus, $queryFactory, $commandFactory, $outputHandler, $logger);
        $connector->addDefinition($definition);
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
        $definition->expects($this->any())->method('getOriginAdapterName')->willReturn('TestOriginAdapter');
        $definition->expects($this->any())->method('getDestinationAdapterName')->willReturn('TestDestinationAdapter');
        $definition->expects($this->any())->method('getObjectType')->willReturn('TestType');
        $definition->method('getPriority')->willReturn(0);
        $definition->method('isActive')->willReturn(true);

        $outputHandler = $this->createMock(OutputHandlerInterface::class);
        $logger = $this->createMock(LoggerInterface::class);

        $connector = new Connector($serviceBus, $queryFactory, $commandFactory, $outputHandler, $logger);
        $connector->addDefinition($definition);
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
        $definition->expects($this->any())->method('getOriginAdapterName')->willReturn('TestOriginAdapter');
        $definition->expects($this->any())->method('getDestinationAdapterName')->willReturn('TestDestinationAdapter');
        $definition->expects($this->any())->method('getObjectType')->willReturn('TestType');
        $definition->method('getPriority')->willReturn(0);
        $definition->method('isActive')->willReturn(true);

        $outputHandler = $this->createMock(OutputHandlerInterface::class);
        $logger = $this->createMock(LoggerInterface::class);

        $connector = new Connector($serviceBus, $queryFactory, $commandFactory, $outputHandler, $logger);
        $connector->addDefinition($definition);
        $connector->handle(QueryType::CHANGED, 'TestType');
    }
}
