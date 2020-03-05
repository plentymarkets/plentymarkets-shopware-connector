<?php

namespace PlentyConnector\Installer\Model;

use DateTime;
use Doctrine\ORM\Mapping as ORM;
use SystemConnector\ServiceBus\Command\CommandInterface;

/**
 * @ORM\Entity
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
     * status of entry (open, processed)
     *
     * @var string
     *
     * @ORM\Column(name="status", type="string", nullable=false)
     */
    private $status;

    /**
     * priority of the entry
     *
     * @var int
     *
     * @ORM\Column(name="priority", type="integer", nullable=false, options={"default": 0})
     */
    private $priority = 0;

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

    public function __construct()
    {
        $this->time = new DateTime('now');
        $this->status = self::STATUS_OPEN;
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getPayload(): CommandInterface
    {
        return $this->payload;
    }

    public function setPayload(CommandInterface $payload)
    {
        $this->payload = $payload;
    }

    public function getStatus(): string
    {
        return $this->status;
    }

    /**
     * @param string $status
     */
    public function setStatus($status)
    {
        $this->status = $status;
    }

    public function getPriority(): int
    {
        return $this->priority;
    }

    /**
     * @param int $priority
     */
    public function setPriority($priority)
    {
        $this->priority = $priority;
    }

    public function getTime(): DateTime
    {
        return $this->time;
    }

    public function setTime(DateTime $time)
    {
        $this->time = $time;
    }

    public function getHash(): string
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
