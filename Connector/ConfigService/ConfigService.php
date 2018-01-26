<?php

namespace PlentyConnector\Connector\ConfigService;

use DateTime;
use DateTimeImmutable;
use Doctrine\Common\Persistence\ObjectRepository;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use PlentyConnector\Connector\ConfigService\Model\Config;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class ConfigService.
 */
class ConfigService implements ConfigServiceInterface
{
    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    /**
     * @var ObjectRepository
     */
    private $repository;

    /**
     * @var ContainerInterface
     */
    private $container;

    /**
     * @param EntityManagerInterface $entityManager
     * @param ContainerInterface     $container
     */
    public function __construct(EntityManagerInterface $entityManager, ContainerInterface $container)
    {
        $this->entityManager = $entityManager;
        $this->repository    = $entityManager->getRepository(Config::class);
        $this->container     = $container;
    }

    /**
     * {@inheritdoc}
     */
    public function getAll()
    {
        $containerParameters = [];

        if ($this->container->hasParameter('shopware.plenty_connector')) {
            $containerParameters = $this->container->getParameter('shopware.plenty_connector');
        }

        /**
         * @var Config[] $configElements
         */
        $configElements = $this->repository->findAll();

        $result = [];
        foreach ($configElements as $element) {
            $result[$element->getName()] = $element->getValue();
        }

        return array_merge($result, $containerParameters);
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

        /**
         * @var Config|null
         */
        $element = $this->repository->findOneBy([
            'name' => $key,
        ]);

        if (null === $element) {
            return $default;
        }

        return $element->getValue();
    }

    /**
     * {@inheritdoc}
     */
    public function set($key, $value)
    {
        /**
         * @var Config|null
         */
        $element = $this->repository->findOneBy([
            'name' => $key,
        ]);

        if (null === $element) {
            $element = new Config();
            $element->setName($key);
        }

        if ($value instanceof DateTime || $value instanceof DateTimeImmutable) {
            $value = $value->format(DATE_W3C);
        }

        if ($element->getValue() === $value) {
            return;
        }

        $element->setValue($value);

        $this->entityManager->persist($element);
        $this->entityManager->flush();
        $this->entityManager->clear();
    }
}
