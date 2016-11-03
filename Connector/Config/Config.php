<?php

namespace PlentyConnector\Connector\Config;

use PlentyConnector\Connector\Config\Model\Config as ConfigModel;
use Shopware\Components\Model\ModelManager;

/**
 * Class Config.
 */
class Config implements ConfigInterface
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
     * Config data array.
     *
     * @var ConfigModel[]
     */
    private $config = [];

    /**
     * @param ModelManager $entityManager
     */
    public function __construct(ModelManager $entityManager)
    {
        $this->entityManager = $entityManager;
        $this->repository = $entityManager->getRepository(ConfigModel::class);
    }

    /**
     * {@inheritdoc}
     */
    public function get($key, $default = null)
    {
        if (0 === count($this->config)) {
            $this->initialize();
        }

        /**
         * @var ConfigModel[]
         */
        $elements = $this->repository->findAll();

        foreach ($elements as $element) {
            $this->config[$element->getName()] = $element;
        }

        if (!array_key_exists($key, $this->config)) {
            return $default;
        } else {
            return $this->config[$key]->getValue();
        }
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
            $value = $value->format(\DateTime::ISO8601);
        }

        $this->config[$key]->setValue($value);

        $this->entityManager->persist($this->config[$key]);
        $this->entityManager->flush($this->config[$key]);
    }
}
