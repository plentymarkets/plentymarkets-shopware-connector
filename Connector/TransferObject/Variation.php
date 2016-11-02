<?php
/**
 * Created by PhpStorm.
 * User: jochenmanz
 * Date: 23/09/2016
 * Time: 09:50
 */

namespace PlentyConnector\Connector\TransferObject;


use Assert\Assertion;

class Variation implements TransferObjectInterface
{
    /**
     * Identifier of the object
     *
     * @var string
     */
    private $identifer;

    /**
     * @var string
     */
    private $name;

    /**
     * Variation constructor.
     *
     * @param string $identifer
     * @param string $name
     */
    public function __construct($identifer, $name)
    {
        Assertion::uuid($identifer);
        Assertion::string($name);

        $this->identifer = $identifer;
        $this->name = $name;
    }

    /**
     * @return string
     */
    public static function getType()
    {
        return 'Variation';
    }


    /**
     * @param array $params
     *
     * @return self
     */
    public static function fromArray(array $params = [])
    {
        return new self(
            $params['identifer'],
            $params['name']
        );
    }
}
