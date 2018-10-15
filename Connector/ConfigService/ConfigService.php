<?php

namespace SystemConnector\ConfigService;

use DateTime;
use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;
use SystemConnector\ConfigService\Model\Config;
use SystemConnector\ConfigService\Model\ConfigRepository;

class ConfigService implements ConfigServiceInterface
{
    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    /**
     * @var ConfigRepository
     */
    private $repository;

    /**
     * @param EntityManagerInterface $entityManager
     */
    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
        $this->repository = $entityManager->getRepository(Config::class);
    }

    /**
     * {@inheritdoc}
     */
    public function getAll()
    {
        /**
         * @var Config[] $configElements
         */
        $configElements = $this->repository->findAll();

        $result = [];

        foreach ($configElements as $element) {
            $result[$element->getName()] = $element->getValue();
        }

        return array_merge($result);
    }

    /**
     * {@inheritdoc}
     */
    public function get($key, $default = null)
    {
        /**
         * @var null|Config $element
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
         * @var null|Config $element
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
