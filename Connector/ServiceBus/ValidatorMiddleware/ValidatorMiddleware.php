<?php

namespace SystemConnector\ServiceBus\ValidatorMiddleware;

use League\Tactician\Middleware;
use SystemConnector\ServiceBus\Command\TransferObjectCommand;
use SystemConnector\ValidatorService\ValidatorServiceInterface;

class ValidatorMiddleware implements Middleware
{
    /**
     * @var ValidatorServiceInterface
     */
    private $validator;

    public function __construct(ValidatorServiceInterface $validator)
    {
        $this->validator = $validator;
    }

    /**
     * @param mixed $command
     *
     * @return mixed
     */
    public function execute($command, callable $next)
    {
        if (!($command instanceof TransferObjectCommand)) {
            return $next($command);
        }

        $object = $command->getPayload();

        $this->validator->validate($object);

        return $next($command);
    }
}
