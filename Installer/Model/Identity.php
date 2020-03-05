<?php

namespace PlentyConnector\Installer\Model;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="plenty_identity", indexes={
 *     @ORM\Index(name="objectIdentifier_idx", columns={"objectIdentifier"}),
 *     @ORM\Index(name="objectType_idx", columns={"objectType"}),
 *     @ORM\Index(name="adapterIdentifier_idx", columns={"adapterIdentifier"}),
 *     @ORM\Index(name="adapterName_idx", columns={"adapterName"})
 * })
 */
class Identity
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
     * Identifier of the object.
     *
     * @var string
     *
     * @ORM\Column(name="objectIdentifier", type="string", nullable=false)
     */
    private $objectIdentifier;

    /**
     * TransferObject type.
     *
     * @var string
     *
     * @ORM\Column(name="objectType", type="string", nullable=false)
     */
    private $objectType;

    /**
     * Identifier inside the adapter domain.
     *
     * @var string
     *
     * @ORM\Column(name="adapterIdentifier", type="string", nullable=false)
     */
    private $adapterIdentifier;

    /**
     * Adapter name.
     *
     * @var string
     *
     * @ORM\Column(name="adapterName", type="string", nullable=false)
     */
    private $adapterName;

    public function __construct($objectIdentifier, $objectType, $adapterIdentifier, $adapterName)
    {
        $this->objectIdentifier = $objectIdentifier;
        $this->objectType = $objectType;
        $this->adapterIdentifier = $adapterIdentifier;
        $this->adapterName = $adapterName;
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getObjectIdentifier(): string
    {
        return $this->objectIdentifier;
    }

    /**
     * @param string $objectIdentifier
     */
    public function setObjectIdentifier($objectIdentifier)
    {
        $this->objectIdentifier = $objectIdentifier;
    }

    public function getObjectType(): string
    {
        return $this->objectType;
    }

    /**
     * @param string $objectType
     */
    public function setObjectType($objectType)
    {
        $this->objectType = $objectType;
    }

    public function getAdapterIdentifier(): string
    {
        return $this->adapterIdentifier;
    }

    /**
     * @param string $adapterIdentifier
     */
    public function setAdapterIdentifier($adapterIdentifier)
    {
        $this->adapterIdentifier = $adapterIdentifier;
    }

    public function getAdapterName(): string
    {
        return $this->adapterName;
    }

    /**
     * @param string $adapterName
     */
    public function setAdapterName($adapterName)
    {
        $this->adapterName = $adapterName;
    }
}
