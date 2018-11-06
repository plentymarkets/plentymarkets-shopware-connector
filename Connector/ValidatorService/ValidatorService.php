<?php

namespace SystemConnector\ValidatorService;

use Assert\InvalidArgumentException;
use phpDocumentor\Reflection\Types\Iterable_;
use SystemConnector\TransferObject\TransferObjectInterface;
use SystemConnector\Validator\ValidatorInterface;
use SystemConnector\ValidatorService\Exception\InvalidDataException;
use SystemConnector\ValueObject\ValueObjectInterface;
use Traversable;
use function is_array;

class ValidatorService implements ValidatorServiceInterface
{
    /**
     * @var Traversable|ValidatorInterface[]
     */
    public $validators = [];

    /**
     * @param Traversable|ValidatorInterface[] $validators
     */
    public function __construct(Traversable $validators)
    {
        $this->validators = iterator_to_array($validators);
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
                } else {
                    if (!$this->canBeValidated($result)) {
                        continue;
                    }

                    $this->validate($result, $parents);
                }
            }
        } catch (InvalidArgumentException $exception) {
            throw InvalidDataException::fromObject($object, $exception->getMessage(), $exception->getPropertyPath(), $parents);
        }
    }

    /**
     * @param mixed $value
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
