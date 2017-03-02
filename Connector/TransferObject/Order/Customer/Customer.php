<?php

namespace PlentyConnector\Connector\TransferObject\Order\Customer;

use Assert\Assertion;
use DateTimeImmutable;
use PlentyConnector\Connector\TransferObject\AbstractTransferObject;

/**
 * Class Customer
 */
class Customer extends AbstractTransferObject
{
    const TYPE_NORMAL = 1;
    const TYPE_GUEST = 2;

    const SALUTATION_MR = 1;
    const SALUTATION_MS = 2;
    const SALUTATION_FIRM = 3;

    const TYPE = 'order_customer';

    private $identifier;

    /**
     * @var int
     */
    private $type = self::TYPE_NORMAL;

    /**
     * @var string
     */
    private $number = '';

    /**
     * @var string
     */
    private $email = '';

    /**
     * @var bool
     */
    private $newsletter = false;

    /**
     * @var string
     */
    private $languageIdentifier = '';

    /**
     * @var string
     */
    private $customerGroupIdentifier = '';

    /**
     * @var int
     */
    private $salutation = 0;

    /**
     * @var string
     */
    private $title = '';

    /**
     * @var string
     */
    private $firstname = '';

    /**
     * @var string
     */
    private $lastname = '';

    /**
     * @var DateTimeImmutable
     */
    private $birthday;

    /**
     * @var string
     */
    private $phoneNumber = '';

    /**
     * @var string
     */
    private $mobilePhoneNumber = '';

    /**
     * @var string
     */
    private $shopIdentifier;
    
    /**
     * Customer constructor.
     */
    public function __construct()
    {
        $timezone = new \DateTimeZone('UTC');
        $this->birthday = new DateTimeImmutable('now', $timezone);
    }

    /**
     * @return int
     */
    public function getCustomerType()
    {
        return $this->type;
    }

    /**
     * @param int $type
     */
    public function setType($type)
    {
        $this->type = $type;
    }

    /**
     * @return string
     */
    public function getNumber()
    {
        return $this->number;
    }

    /**
     * @param string $number
     */
    public function setNumber($number)
    {
        $this->number = $number;
    }

    /**
     * @return string
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * @param string $email
     */
    public function setEmail($email)
    {
        $this->email = $email;
    }

    /**
     * @return bool
     */
    public function getNewsletter()
    {
        return $this->newsletter;
    }

    /**
     * @param bool $newsletter
     */
    public function setNewsletter($newsletter)
    {
        Assertion::boolean($newsletter);

        $this->newsletter = $newsletter;
    }

    /**
     * @return string
     */
    public function getLanguageIdentifier()
    {
        return $this->languageIdentifier;
    }

    /**
     * @param string $languageIdentifier
     */
    public function setLanguageIdentifier($languageIdentifier)
    {
        Assertion::uuid($languageIdentifier);

        $this->languageIdentifier = $languageIdentifier;
    }

    /**
     * @return string
     */
    public function getCustomerGroupIdentifier()
    {
        return $this->customerGroupIdentifier;
    }

    /**
     * @param string $customerGroupIdentifier
     */
    public function setCustomerGroupIdentifier($customerGroupIdentifier)
    {
        Assertion::uuid($customerGroupIdentifier);

        $this->customerGroupIdentifier = $customerGroupIdentifier;
    }

    /**
     * @return int
     */
    public function getSalutation()
    {
        return $this->salutation;
    }

    /**
     * @param int $salutation
     */
    public function setSalutation($salutation)
    {
        $this->salutation = $salutation;
    }

    /**
     * @return null|string
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * @param null|string $title
     */
    public function setTitle($title)
    {
        $this->title = $title;
    }

    /**
     * @return null|string
     */
    public function getFirstname()
    {
        return $this->firstname;
    }

    /**
     * @param null|string $firstname
     */
    public function setFirstname($firstname)
    {
        $this->firstname = $firstname;
    }

    /**
     * @return null|string
     */
    public function getLastname()
    {
        return $this->lastname;
    }

    /**
     * @param null|string $lastname
     */
    public function setLastname($lastname)
    {
        $this->lastname = $lastname;
    }

    /**
     * @return DateTimeImmutable|null
     */
    public function getBirthday()
    {
        return $this->birthday;
    }

    /**
     * @param DateTimeImmutable $birthday
     */
    public function setBirthday($birthday)
    {
        $this->birthday = $birthday;
    }

    /**
     * @return string
     */
    public function getPhoneNumber()
    {
        return $this->phoneNumber;
    }

    /**
     * @param string $phoneNumber
     */
    public function setPhoneNumber($phoneNumber)
    {
        $this->phoneNumber = $phoneNumber;
    }

    /**
     * @return string
     */
    public function getMobilePhoneNumber()
    {
        return $this->mobilePhoneNumber;
    }

    /**
     * @param string $mobilePhoneNumber
     */
    public function setMobilePhoneNumber($mobilePhoneNumber)
    {
        $this->mobilePhoneNumber = $mobilePhoneNumber;
    }

    /**
     * {@inheritdoc}
     */
    public function getIdentifier()
    {
        Assertion::notBlank($this->identifier);

        return $this->identifier;
    }

    /**
     * {@inheritdoc}
     */
    public function getType()
    {
        return self::TYPE;
    }

    /**
     * @return string
     */
    public function getShopIdentifier()
    {
        return $this->shopIdentifier;
    }

    /**
     * @param string $shopIdentifier
     */
    public function setShopIdentifier($shopIdentifier)
    {
        $this->shopIdentifier = $shopIdentifier;
    }
}
