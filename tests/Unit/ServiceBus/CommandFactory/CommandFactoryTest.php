<?php

namespace PlentyConnector\tests\Unit\ServiceBus\CommandFactory;

use PHPUnit\Framework\TestCase;
use PlentyConnector\Connector\ServiceBus\Command\CommandInterface;
use PlentyConnector\Connector\ServiceBus\CommandFactory\CommandFactory;
use PlentyConnector\Connector\ServiceBus\CommandGenerator\CommandGeneratorInterface;
use PlentyConnector\Connector\ServiceBus\CommandType;
use PlentyConnector\Connector\TransferObject\TransferObjectInterface;
use Ramsey\Uuid\Uuid;

/**
 * Class CommandFactoryTest.
 */
class CommandFactoryTest extends TestCase
{
    public function test_generate_handle_command()
    {
        $transferObject = $this->createMock(TransferObjectInterface::class);
        $transferObject->expects($this->any())->method('getType')->willReturn('TestType');

        $commandMock = $this->createMock(CommandInterface::class);

        $generator = $this->createMock(CommandGeneratorInterface::class);
        $generator->expects($this->once())->method('supports')->with('TestType')->willReturn(true);
        $generator->expects($this->once())->method('generateHandleCommand')->with('TestAdapter', $transferObject)->willReturn($commandMock);

        $commandFactory = new CommandFactory();
        $commandFactory->addGenerator($generator);

        $command = $commandFactory->create('TestAdapter', 'TestType', CommandType::HANDLE, $transferObject);

        $this->assertEquals($command, $commandMock);
    }

    public function test_generate_remove_command()
    {
        $uuid = Uuid::uuid4()->toString();

        $transferObject = $this->createMock(TransferObjectInterface::class);
        $transferObject->expects($this->any())->method('getType')->willReturn('TestType');

        $commandMock = $this->createMock(CommandInterface::class);

        $generator = $this->createMock(CommandGeneratorInterface::class);
        $generator->expects($this->once())->method('supports')->with('TestType')->willReturn(true);
        $generator->expects($this->once())->method('generateRemoveCommand')->with('TestAdapter', $uuid)->willReturn($commandMock);

        $commandFactory = new CommandFactory();
        $commandFactory->addGenerator($generator);

        $command = $commandFactory->create('TestAdapter', 'TestType', CommandType::REMOVE, $uuid);

        $this->assertEquals($command, $commandMock);
    }
}
