<?php

namespace SystemConnector\Console\Command;

use Exception;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use SystemConnector\Console\OutputHandler\OutputHandlerInterface;
use SystemConnector\Logger\ConsoleHandler;
use SystemConnector\MappingService\MappingServiceInterface;
use SystemConnector\TransferObject\TransferObjectInterface;
use Throwable;

class MappingCommand extends Command
{
    /**
     * @var MappingServiceInterface
     */
    private $mappingService;

    /**
     * @var OutputHandlerInterface
     */
    private $outputHandler;

    /**
     * @var LoggerInterface
     */
    private $logger;

    public function __construct(
        MappingServiceInterface $mappingService,
        OutputHandlerInterface $outputHandler,
        LoggerInterface $logger
    ) {
        $this->mappingService = $mappingService;
        $this->outputHandler = $outputHandler;
        $this->logger = $logger;

        parent::__construct();
    }

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this->setName('plentyconnector:mapping');
        $this->setDescription('displays mapping informations');
        $this->addArgument(
            'objectType',
            InputArgument::OPTIONAL,
            'Object type to process. Leave empty for every object type'
        );
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        if (method_exists($this->logger, 'pushHandler')) {
            $this->logger->pushHandler(new ConsoleHandler($output));
        }

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
                    $targetIdentifier = array_filter($entry->getDestinationTransferObjects(), function (TransferObjectInterface $targetObject) use ($object) {
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
        } catch (Throwable $exception) {
            $this->logger->error($exception->getMessage());
        }
    }
}
