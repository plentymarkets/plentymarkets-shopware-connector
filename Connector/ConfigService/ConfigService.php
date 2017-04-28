<?php

namespace PlentyConnector\Connector\ConfigService;

use DateTime;
use DateTimeImmutable;
use Exception;
use PlentyConnector\Connector\ConfigService\Model\Config;
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
     * @param ModelManager       $entityManager
     * @param ContainerInterface $container
     */
    public function __construct(ModelManager $entityManager, ContainerInterface $container)
    {
        $this->entityManager = $entityManager;
        $this->repository = $entityManager->getRepository(ConfigModel::class);
        $this->container = $container;
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
     *
     * @throws \Doctrine\ORM\ORMInvalidArgumentException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function set($key, $value)
    {
        $element = $this->repository->findOneBy([
            'name' => $key,
        ]);

        if (null === $element) {
            $element = new Config();
            $element->setName($key);
        }

        if ($element->getValue() === $value) {
            return;
        }

        if ($value instanceof DateTime || $value instanceof DateTimeImmutable) {
            $value = $value->format(DATE_W3C);
        }

        $element->setValue($value);

        $this->entityManager->persist($element);
        $this->entityManager->flush($element);
    }
}
