<?php

namespace PlentyConnector\Container;

use Interop\Container\ContainerInterface;
use PlentyConnector\Container\Exception\NotFoundException;
use Symfony\Component\DependencyInjection\ContainerInterface as BaseContainerInterface;

/**
 * Class ShopwareContainer.
 */
class Container implements ContainerInterface
{
    /**
     * @var BaseContainerInterface
     */
    private $container;

    /**
     * Container constructor.
     *
     * @param BaseContainerInterface $container
     */
    public function __construct(BaseContainerInterface $container)
    {
        $this->container = $container;
    }

    /**
     * Finds an entry of the container by its identifier and returns it.
     *
     * @param string $id Identifier of the entry to look for.
     *
     * @throws NotFoundException No entry was found for this identifier.
     *
     * @return mixed Entry.
     */
    public function get($id)
    {
        try {
            $this->container->get($id);
        } catch (\Exception $e) {
            throw new NotFoundException();
        }
    }

    /**
     * Returns true if the container can return an entry for the given identifier.
     * Returns false otherwise.
     *
     * @param string $id Identifier of the entry to look for.
     *
     * @return bool
     */
    public function has($id)
    {
        $this->container->has($id);
    }
}
