<?php

namespace SystemConnector\TransferObject\Order\Customer;

use DateTimeImmutable;
use ReflectionClass;
use SystemConnector\ValueObject\AbstractValueObject;

class Customer extends AbstractValueObject
{
    const TYPE_NORMAL = 'normal';
    const TYPE_GUEST = 'guest';

    const GENDER_MALE = 'male';
    const GENDER_FEMALE = 'female';
    const GENDER_DIVERSE = 'diverse';

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

    public function __construct()
    {
        $this->birthday = new DateTimeImmutable('now');
    }

    public function getCustomerTypes(): array
    {
        return $this->getConstantsByName('TYPE');
    }

    public function getType(): string
    {
        return $this->type;
    }

    /**
     * @param string $type
     */
    public function setType($type)
    {
        $this->type = $type;
    }

    public function getNumber(): string
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

    public function getEmail(): string
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

    public function getNewsletter(): bool
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

    public function setNewsletterAgreementDate(DateTimeImmutable $newsletterAgreementDate = null)
    {
        $this->newsletterAgreementDate = $newsletterAgreementDate;
    }

    public function getLanguageIdentifier(): string
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

    public function getCustomerGroupIdentifier(): string
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

    public function getGender(): string
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

    public function getGenders(): array
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

    public function getShopIdentifier(): string
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
     * {@inheritdoc}
     */
    public function getClassProperties()
    {
        return [
            'type' => $this->getType(),
            'number' => $this->getNumber(),
            'email' => $this->getEmail(),
            'newsletter' => $this->getNewsletter(),
            'newsletterAgreementDate' => $this->getNewsletterAgreementDate(),
            'languageIdentifier' => $this->getLanguageIdentifier(),
            'customerGroupIdentifier' => $this->getCustomerGroupIdentifier(),
            'gender' => $this->getGender(),
            'title' => $this->getTitle(),
            'firstname' => $this->getFirstname(),
            'lastname' => $this->getLastname(),
            'birthday' => $this->getBirthday(),
            'phoneNumber' => $this->getPhoneNumber(),
            'mobilePhoneNumber' => $this->getMobilePhoneNumber(),
            'shopIdentifier' => $this->getShopIdentifier(),
        ];
    }

    /**
     * @param string $name
     */
    private function getConstantsByName($name): array
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
