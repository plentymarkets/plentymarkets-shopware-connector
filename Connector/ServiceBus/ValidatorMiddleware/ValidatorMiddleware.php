<?php

namespace PlentyConnector\Connector\ServiceBus\ValidatorMiddleware;

use League\Tactician\Middleware;
use PlentyConnector\Connector\ServiceBus\Command\HandleCommandInterface;
use PlentyConnector\Connector\ValidatorService\ValidatorServiceInterface;

/**
 * Class ValidatorMiddleware.
 */
class ValidatorMiddleware implements Middleware
{
    /**
     * @var ValidatorServiceInterface
     */
    private $validator;

    /**
     * ValidatorMiddleware constructor.
     *
     * @param ValidatorServiceInterface $validator
     */
    public function __construct(ValidatorServiceInterface $validator)
    {
        $this->validator = $validator;
    }

    /**
     * @param $command
     * @param callable $next
     *
     * @return mixed
     */
    public function execute($command, callable $next)
    {
        if (!($command instanceof HandleCommandInterface)) {
            return $next($command);
        }

        $object = $command->getTransferObject();

        $this->validator->validate($object);

        return $next($command);
    }
}
