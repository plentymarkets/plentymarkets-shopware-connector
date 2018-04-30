<?php

namespace PlentyConnector\Connector\TransferObject\Order\Customer;

use DateTimeImmutable;
use PlentyConnector\Connector\ValueObject\AbstractValueObject;
use ReflectionClass;

/**
 * Class Customer
 */
class Customer extends AbstractValueObject
{
    const TYPE_NORMAL = 1;
    const TYPE_GUEST = 2;

    const GENDER_MALE = 'male';
    const GENDER_FEMALE = 'female';

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
     * @var null|DateTimeImmutable
     */
    private $newsletterAgreementDate;

    /**
     * @var string
     */
    private $languageIdentifier = '';

    /**
     * @var string
     */
    private $customerGroupIdentifier = '';

    /**
     * @var string
     */
    private $gender = self::GENDER_MALE;

    /**
     * @var null|string
     */
    private $title;

    /**
     * @var string
     */
    private $firstname = '';

    /**
     * @var string
     */
    private $lastname = '';

    /**
     * @var null|DateTimeImmutable
     */
    private $birthday;

    /**
     * @var null|string
     */
    private $phoneNumber;

    /**
     * @var null|string
     */
    private $mobilePhoneNumber;

    /**
     * @var string
     */
    private $shopIdentifier = '';

    /**
     * Customer constructor.
     */
    public function __construct()
    {
        $this->birthday = new DateTimeImmutable('now');
    }

    /**
     * @return array
     */
    public function getCustomerTypes()
    {
        return $this->getConstantsByName('TYPE');
    }

    /**
     * @return int
     */
    public function getType()
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
        $this->newsletter = $newsletter;
    }

    /**
     * @return null|DateTimeImmutable
     */
    public function getNewsletterAgreementDate()
    {
        return $this->newsletterAgreementDate;
    }

    /**
     * @param null|DateTimeImmutable $newsletterAgreementDate
     */
    public function setNewsletterAgreementDate(DateTimeImmutable $newsletterAgreementDate = null)
    {
        $this->newsletterAgreementDate = $newsletterAgreementDate;
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
        $this->customerGroupIdentifier = $customerGroupIdentifier;
    }

    /**
     * @return string
     */
    public function getGender()
    {
        return $this->gender;
    }

    /**
     * @param string $gender
     */
    public function setGender($gender)
    {
        $this->gender = $gender;
    }

    /**
     * @return array
     */
    public function getGenders()
    {
        return $this->getConstantsByName('GENDER');
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
    public function setTitle($title = null)
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
    public function setFirstname($firstname = null)
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
     * @return null|DateTimeImmutable
     */
    public function getBirthday()
    {
        return $this->birthday;
    }

    /**
     * @param null|DateTimeImmutable $birthday
     */
    public function setBirthday(DateTimeImmutable $birthday = null)
    {
        $this->birthday = $birthday;
    }

    /**
     * @return null|string
     */
    public function getPhoneNumber()
    {
        return $this->phoneNumber;
    }

    /**
     * @param null|string $phoneNumber
     */
    public function setPhoneNumber($phoneNumber = null)
    {
        $this->phoneNumber = $phoneNumber;
    }

    /**
     * @return null|string
     */
    public function getMobilePhoneNumber()
    {
        return $this->mobilePhoneNumber;
    }

    /**
     * @param null|string $mobilePhoneNumber
     */
    public function setMobilePhoneNumber($mobilePhoneNumber = null)
    {
        $this->mobilePhoneNumber = $mobilePhoneNumber;
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

    /**
     * @param string $name
     *
     * @return array
     */
    private function getConstantsByName($name)
    {
        $reflection = new ReflectionClass(__CLASS__);

        $constants = $reflection->getConstants();

        $result = [];

        foreach ($constants as $key => $constant) {
            if (false !== stripos($key, $name)) {
                $result[$key] = $constant;
            }
        }

        return $result;
    }
}
