<?php

namespace PlentyConnector\Console\Command;

use Exception;
use PlentyConnector\Connector\Logger\ConsoleHandler;
use PlentyConnector\Connector\MappingService\MappingServiceInterface;
use PlentyConnector\Connector\TransferObject\TransferObjectInterface;
use PlentyConnector\Console\OutputHandler\OutputHandlerInterface;
use Shopware\Commands\ShopwareCommand;
use Shopware\Components\Logger;
use Symfony\Component\Console\Exception\InvalidArgumentException;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Command to manually process definitions.
 */
class MappingCommand extends ShopwareCommand
{
    /**
     * @var MappingServiceInterface
     */
    private $mappingService;

    /**
     * @var Logger
     */
    private $logger;

    /**
     * @var OutputHandlerInterface
     */
    private $outputHandler;

    /**
     * MappingCommand constructor.
     *
     * @param MappingServiceInterface $mappingService
     * @param Logger $logger
     * @param OutputHandlerInterface $outputHandler
     */
    public function __construct(
        MappingServiceInterface $mappingService,
        Logger $logger,
        OutputHandlerInterface $outputHandler
    ) {
        $this->mappingService = $mappingService;
        $this->logger = $logger;
        $this->outputHandler = $outputHandler;

        parent::__construct();
    }

    /**
     * @throws InvalidArgumentException
     */
    protected function configure()
    {
        $this->setName('plentyconnector:mapping');
        $this->setDescription('cleanup task');
        $this->addArgument(
            'objectType',
            InputArgument::OPTIONAL,
            'Object type to process. Leave empty for every object type',
            null
        );
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     *
     * @throws Exception
     *
     * @return int|void|null
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->logger->pushHandler(new ConsoleHandler($output));
        $this->outputHandler->initialize($input, $output);

        $objectType = $input->getArgument('objectType');

        try {
            $mapping = $this->mappingService->getMappingInformation($objectType);

            foreach ($mapping as $entry) {
                $this->outputHandler->writeLine($entry->getObjectType());

                $headers = [
                    $entry->getOriginAdapterName(),
                    $entry->getDestinationAdapterName(),
                ];

                $rows = [];
                foreach ($entry->getOriginTransferObjects() as $object) {
                    $targetIdentifier = array_filter($entry->getDestinationTransferObjects(), function(TransferObjectInterface $targetObject) use ($object) {
                        return $object->getIdentifier() === $targetObject->getIdentifier();
                    });

                    if (!empty($targetIdentifier)) {
                        $targetIdentifier = array_shift($targetIdentifier);

                        if (method_exists($targetIdentifier, 'getName')) {
                            $targetIdentifier = $targetIdentifier->getName();
                        } else {
                            $targetIdentifier = $targetIdentifier->getIdentifier();
                        }
                    } else {
                        $targetIdentifier = '';
                    }

                    if (method_exists($object, 'getName')) {
                        $objectIdentifier = $object->getName();
                    } else {
                        $objectIdentifier = $object->getIdentifier();
                    }

                    $rows[] = [
                        $objectIdentifier,
                        $targetIdentifier,
                    ];
                }

                $this->outputHandler->createTable($headers, $rows);
            }
        } catch (Exception $exception) {
            $this->logger->error($exception->getMessage());
            $this->logger->error($exception->getTraceAsString());
        }
    }
}
