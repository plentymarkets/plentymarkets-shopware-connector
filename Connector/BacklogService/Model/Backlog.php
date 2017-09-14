<?php

namespace PlentyConnector\Connector\BacklogService\Model;

use DateTimeImmutable;
use Doctrine\ORM\Mapping as ORM;
use PlentyConnector\Connector\ServiceBus\Command\CommandInterface;

/**
 * Class Backlog.
 *
 * @ORM\Entity()
 * @ORM\Table(name="plenty_backlog", indexes={
 *     @ORM\Index(name="hash_idx", columns={"hash"}),
 *     @ORM\Index(name="time_idx", columns={"time"})
 * })
 */
class Backlog
{
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     * logged command
     *
     * @var CommandInterface
     *
     * @ORM\Column(name="payload", type="object", nullable=false)
     */
    private $payload;

    /**
     * time of insertion
     *
     * @var DateTime
     *
     * @ORM\Column(name="time", type="datetime", nullable=false)
     */
    private $time;

    /**
     * hash of the stored payload
     *
     * @var string
     *
     * @ORM\Column(name="hash", type="string", nullable=false)
     */
    private $hash;

    /**
     * Backlog constructor.
     */
    public function __construct()
    {
        $this->time = new DateTimeImmutable('now');
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return CommandInterface
     */
    public function getPayload()
    {
        return $this->payload;
    }

    /**
     * @param CommandInterface $payload
     */
    public function setPayload(CommandInterface $payload)
    {
        $this->payload = $payload;
    }

    /**
     * @return DateTime
     */
    public function getTime()
    {
        return $this->time;
    }

    /**
     * @return string
     */
    public function getHash()
    {
        return $this->hash;
    }

    /**
     * @param string $hash
     */
    public function setHash($hash)
    {
        $this->hash = $hash;
    }
}
