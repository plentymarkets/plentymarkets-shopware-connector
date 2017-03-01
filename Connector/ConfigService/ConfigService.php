<?php

namespace PlentyConnector\Connector\ConfigService;

use Exception;
use PlentyConnector\Connector\ConfigService\Model\Config as ConfigModel;
use Shopware\Components\Model\ModelManager;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * TODO: Refactor
 * TODO: add readonly flag for container based config values (+ backend)
 *
 * Class ConfigService.
 */
class ConfigService implements ConfigServiceInterface
{
    /**
     * @var ModelManager
     */
    private $entityManager;

    /**
     * @var ModelManager
     */
    private $repository;

    /**
     * @var ContainerInterface
     */
    private $container;

    /**
     * Config data array.
     *
     * @var ConfigModel[]
     */
    private $config = [];

    /**
     * @param ModelManager $entityManager
     * @param ContainerInterface $container
     */
    public function __construct(ModelManager $entityManager, ContainerInterface $container)
    {
        $this->entityManager = $entityManager;
        $this->repository = $entityManager->getRepository(ConfigModel::class);
        $this->container = $container;

        $this->initialize();
    }

    /**
     * {@inheritdoc}
     */
    public function get($key, $default = null)
    {
        if ($this->container->hasParameter('shopware.plenty_connector.' . $key)) {
            try {
                return $this->container->getParameter('shopware.plenty_connector.' . $key);
            } catch (Exception $exception) {
                // fail silently
            }
        }

        if (!array_key_exists($key, $this->config)) {
            return $default;
        }

        return $this->config[$key]->getValue();
    }

    /**
     * {@inheritdoc}
     *
     * @throws \Doctrine\ORM\ORMInvalidArgumentException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function set($key, $value)
    {
        if (!array_key_exists($key, $this->config)) {
            $this->config[$key] = new ConfigModel();

            $this->config[$key]->setName($key);
        }

        if (null !== $this->config[$key] && $this->config[$key] === $value) {
            return;
        }

        if ($value instanceof \DateTime || $value instanceof \DateTimeImmutable) {
            $value = $value->format(DATE_W3C);
        }

        $this->config[$key]->setValue($value);

        $this->entityManager->persist($this->config[$key]);
        $this->entityManager->flush($this->config[$key]);
    }

    /**
     * pre fill the whole existing config.
     */
    private function initialize()
    {
        /**
         * @var ConfigModel[]
         */
        $elements = $this->repository->findAll();

        foreach ($elements as $element) {
            $this->config[$element->getName()] = $element;
        }
    }
}
