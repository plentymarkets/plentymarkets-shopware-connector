<?php

namespace SystemConnector\Console\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use SystemConnector\Console\OutputHandler\OutputHandlerInterface;
use SystemConnector\DefinitionProvider\DefinitionProviderInterface;
use SystemConnector\IdentityService\IdentityServiceInterface;
use SystemConnector\ServiceBus\Query\FetchTransferObjectQuery;
use SystemConnector\ServiceBus\QueryType;
use SystemConnector\ServiceBus\ServiceBus;

class DumpCommand extends Command
{
    /**
     * @var ServiceBus
     */
    private $serviceBus;

    /**
     * @var IdentityServiceInterface
     */
    private $identityService;

    /**
     * @var DefinitionProviderInterface
     */
    private $definitionProvider;

    /**
     * @var OutputHandlerInterface
     */
    private $outputHandler;

    public function __construct(
        ServiceBus $serviceBus,
        IdentityServiceInterface $identityService,
        DefinitionProviderInterface $definitionProvider,
        OutputHandlerInterface $outputHandler
    ) {
        $this->serviceBus = $serviceBus;
        $this->identityService = $identityService;
        $this->definitionProvider = $definitionProvider;
        $this->outputHandler = $outputHandler;

        parent::__construct();
    }

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this->setName('plentyconnector:dump');
        $this->setDescription('retrieves and dumps transfer objects');
        $this->addArgument(
            'identifier',
            InputArgument::REQUIRED,
            'Object Identifier'
        );
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->outputHandler->initialize($input, $output);

        $identity = $this->identityService->findOneBy([
            'objectIdentifier' => $input->getArgument('identifier'),
        ]);

        if (null === $identity) {
            $this->outputHandler->writeLine('could not find identity for identifier');

            return;
        }

        $i = 1;
        $definitions = $this->definitionProvider->getConnectorDefinitions($identity->getObjectType());

        foreach ($definitions as $definition) {
            $result = $this->serviceBus->handle(new FetchTransferObjectQuery(
                $definition->getOriginAdapterName(),
                $definition->getObjectType(),
                QueryType::ONE,
                $input->getArgument('identifier')
            ));

            foreach ($result as $item) {
                $this->outputHandler->writeLine();
                $this->outputHandler->writeLine(sprintf('TransferObject %s:',  $i++));
                $this->outputHandler->writeLine(json_encode($item, JSON_PRETTY_PRINT));
            }
        }
    }
}
