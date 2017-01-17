<?php

namespace PlentyConnector\tests\Unit\CleanupService;

use PHPUnit\Framework\TestCase;
use PlentyConnector\Connector\CleanupService\CleanupService;
use PlentyConnector\Connector\CommandBus\Command\CommandInterface;
use PlentyConnector\Connector\CommandBus\CommandFactory\CommandFactory;
use PlentyConnector\Connector\CommandBus\CommandType;
use PlentyConnector\Connector\IdentityService\IdentityServiceInterface;
use PlentyConnector\Connector\QueryBus\Query\QueryInterface;
use PlentyConnector\Connector\QueryBus\QueryFactory\QueryFactoryInterface;
use PlentyConnector\Connector\QueryBus\QueryType;
use PlentyConnector\Connector\ServiceBus\ServiceBusInterface;
use PlentyConnector\Connector\ValueObject\Definition\DefinitionInterface;
use PlentyConnector\Connector\ValueObject\Identity\IdentityInterface;
use PlentyConnector\Connector\TransferObject\SynchronizedTransferObjectInterface;
use Psr\Log\LoggerInterface;
use Ramsey\Uuid\Uuid;

/**
 * Class CleanupServiceTest
 */
class CleanupServiceTest extends TestCase
{
    public function test_cleanup_specific_type_and_remove_one_orphaned()
    {
        $orphanedUuid = Uuid::uuid4()->toString();

        $testElement = $this->createMock(SynchronizedTransferObjectInterface::class);
        $testElement->expects($this->once())->method('getType')->willReturn('TestType');
        $testElement->expects($this->once())->method('getIdentifier')->willReturn(Uuid::uuid4()->toString());

        $command = $this->createMock(CommandInterface::class);
        $query = $this->createMock(QueryInterface::class);

        $queryBus = $this->createMock(ServiceBusInterface::class);
        $queryBus->expects($this->once())->method('handle')->with($query)->willReturn([
            $testElement
        ]);

        $commandBus = $this->createMock(ServiceBusInterface::class);
        $commandBus->expects($this->once())->method('handle')->with($command);

        $queryFactory = $this->createMock(QueryFactoryInterface::class);
        $queryFactory->expects($this->once())->method('create')->with(
            'TestOriginAdapter',
            'TestType',
            QueryType::ALL
        )->willReturn($query);

        $commandFactory = $this->createMock(CommandFactory::class);
        $commandFactory->expects($this->once())->method('create')->with(
            'TestDestinationAdapter',
            'TestType',
            CommandType::REMOVE,
            $orphanedUuid
        )->willReturn($command);

        $identity = $this->createMock(IdentityInterface::class);
        $identity->expects($this->any())->method('getObjectIdentifier')->willReturn($orphanedUuid);
        $identity->expects($this->any())->method('getObjectType')->willReturn('TestType');
        $identity->expects($this->any())->method('getAdapterIdentifier')->willReturn('2');
        $identity->expects($this->any())->method('getAdapterName')->willReturn('TestOriginAdapter');

        $identityService = $this->createMock(IdentityServiceInterface::class);
        $identityService->expects($this->once())->method('findby')->willReturn([$identity]);

        $definition = $this->createMock(DefinitionInterface::class);
        $definition->expects($this->any())->method('getOriginAdapterName')->willReturn('TestOriginAdapter');
        $definition->expects($this->any())->method('getDestinationAdapterName')->willReturn('TestDestinationAdapter');
        $definition->expects($this->any())->method('getObjectType')->willReturn('TestType');

        $logger = $this->createMock(LoggerInterface::class);

        $cleanupService = new CleanupService(
            $queryBus,
            $commandBus,
            $queryFactory,
            $commandFactory,
            $identityService,
            $logger
        );

        $cleanupService->addDefinition($definition);

        $cleanupService->cleanup('TestType');
    }

    public function test_cleanup_specific_type_and_remove_all()
    {
        $orphanedUuid = Uuid::uuid4()->toString();

        $command = $this->createMock(CommandInterface::class);
        $query = $this->createMock(QueryInterface::class);

        $queryBus = $this->createMock(ServiceBusInterface::class);
        $queryBus->expects($this->once())->method('handle')->with($query)->willReturn([]);

        $commandBus = $this->createMock(ServiceBusInterface::class);
        $commandBus->expects($this->once())->method('handle')->with($command);

        $queryFactory = $this->createMock(QueryFactoryInterface::class);
        $queryFactory->expects($this->once())->method('create')->with(
            'TestOriginAdapter',
            'TestType',
            QueryType::ALL
        )->willReturn($query);

        $commandFactory = $this->createMock(CommandFactory::class);
        $commandFactory->expects($this->once())->method('create')->with(
            'TestDestinationAdapter',
            'TestType',
            CommandType::REMOVE,
            $orphanedUuid
        )->willReturn($command);

        $identity = $this->createMock(IdentityInterface::class);
        $identity->expects($this->any())->method('getObjectIdentifier')->willReturn($orphanedUuid);
        $identity->expects($this->any())->method('getObjectType')->willReturn('TestType');
        $identity->expects($this->any())->method('getAdapterIdentifier')->willReturn('2');
        $identity->expects($this->any())->method('getAdapterName')->willReturn('TestOriginAdapter');

        $identityService = $this->createMock(IdentityServiceInterface::class);
        $identityService->expects($this->once())->method('findby')->willReturn([$identity]);

        $definition = $this->createMock(DefinitionInterface::class);
        $definition->expects($this->any())->method('getOriginAdapterName')->willReturn('TestOriginAdapter');
        $definition->expects($this->any())->method('getDestinationAdapterName')->willReturn('TestDestinationAdapter');
        $definition->expects($this->any())->method('getObjectType')->willReturn('TestType');

        $logger = $this->createMock(LoggerInterface::class);

        $cleanupService = new CleanupService(
            $queryBus,
            $commandBus,
            $queryFactory,
            $commandFactory,
            $identityService,
            $logger
        );

        $cleanupService->addDefinition($definition);

        $cleanupService->cleanup('TestType');
    }
}
