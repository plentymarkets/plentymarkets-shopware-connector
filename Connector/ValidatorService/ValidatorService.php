<?php

namespace PlentyConnector\Connector\ValidatorService;

use Assert\InvalidArgumentException;
use PlentyConnector\Connector\TransferObject\TransferObjectInterface;
use PlentyConnector\Connector\Validator\ValidatorInterface;
use PlentyConnector\Connector\ValidatorService\Exception\InvalidDataException;
use PlentyConnector\Connector\ValueObject\ValueObjectInterface;

/**
 * Class ValidatorService
 */
class ValidatorService implements ValidatorServiceInterface
{
    /**
     * @var ValidatorInterface[]
     */
    public $validators = [];

    /**
     * @param ValidatorInterface $validator
     */
    public function addValidator(ValidatorInterface $validator)
    {
        $this->validators[] = $validator;
    }

    /**
     * {@inheritdoc}
     */
    public function validate($object, array $parents = [])
    {
        if (!$this->canBeValidated($object)) {
            return;
        }

        $validators = array_filter($this->validators, function (ValidatorInterface $validator) use ($object) {
            return $validator->supports($object);
        });

        try {
            array_walk($validators, function (ValidatorInterface $validator) use ($object) {
                $validator->validate($object);
            });

            $parents[] = $object;
            $methods = get_class_methods($object);

            $methods = array_filter($methods, function ($method) {
                return 0 === stripos($method, 'get');
            });

            foreach ($methods as $method) {
                $result = $object->$method();

                if (is_array($result)) {
                    foreach ($result as $item) {
                        if (!$this->canBeValidated($item)) {
                            continue;
                        }

                        $this->validate($item, $parents);
                    }
                }

                if (!$this->canBeValidated($result)) {
                    continue;
                }

                $this->validate($result, $parents);
            }
        } catch (InvalidArgumentException $exception) {
            throw InvalidDataException::fromObject($object, $exception->getMessage(), $exception->getPropertyPath(), $parents);
        }
    }

    /**
     * @param $value
     *
     * @return bool
     */
    private function canBeValidated($value)
    {
        if ($value instanceof TransferObjectInterface) {
            return true;
        }

        if ($value instanceof ValueObjectInterface) {
            return true;
        }

        return false;
    }
}
