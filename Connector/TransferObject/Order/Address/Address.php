<?php

namespace SystemConnector\TransferObject\Order\Address;

use ReflectionClass;
use SystemConnector\TransferObject\AttributableInterface;
use SystemConnector\ValueObject\AbstractValueObject;
use SystemConnector\ValueObject\Attribute\Attribute;

class Address extends AbstractValueObject implements AttributableInterface
{
    const GENDER_MALE = 'male';
    const GENDER_FEMALE = 'female';
    const GENDER_DIVERSE = 'diverse';

    /**
     * @var null|string
     */
    private $company;

    /**
     * @var null|string
     */
    private $department;

    /**
     * @var string
     */
    private $gender = self::GENDER_MALE;

    /**
     * @var null|string
     */
    private $title;

    /**
     * @var null|string
     */
    private $firstname;

    /**
     * @var null|string
     */
    private $lastname;

    /**
     * @var string
     */
    private $street = '';

    /**
     * @var null|string
     */
    private $additional;

    /**
     * @var string
     */
    private $postalCode = '';

    /**
     * @var string
     */
    private $city = '';

    /**
     * @var string
     */
    private $countryIdentifier = '';

    /**
     * @var null|string
     */
    private $vatId;

    /**
     * @var null|string
     */
    private $phoneNumber;

    /**
     * @var null|string
     */
    private $mobilePhoneNumber;

    /**
     * @var Attribute[]
     */
    private $attributes = [];

    /**
     * @return null|string
     */
    public function getCompany()
    {
        return $this->company;
    }

    /**
     * @param null|string $company
     */
    public function setCompany($company)
    {
        $this->company = $company;
    }

    /**
     * @return null|string
     */
    public function getDepartment()
    {
        return $this->department;
    }

    /**
     * @param null|string $department
     */
    public function setDepartment($department)
    {
        $this->department = $department;
    }

    /**
     * @return array
     */
    public function getGenders(): array
    {
        $reflection = new ReflectionClass(__CLASS__);

        return $reflection->getConstants();
    }

    /**
     * @return string
     */
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
     * @return null|string
     */
    public function getAdditional()
    {
        return $this->additional;
    }

    /**
     * @param null|string $additional
     */
    public function setAdditional($additional = null)
    {
        $this->additional = $additional;
    }

    /**
     * @return null|string
     */
    public function getStreet()
    {
        return $this->street;
    }

    /**
     * @param null|string $street
     */
    public function setStreet($street)
    {
        $this->street = $street;
    }

    /**
     * @return string
     */
    public function getPostalCode(): string
    {
        return $this->postalCode;
    }

    /**
     * @param string $postalCode
     */
    public function setPostalCode($postalCode)
    {
        $this->postalCode = $postalCode;
    }

    /**
     * @return string
     */
    public function getCity(): string
    {
        return $this->city;
    }

    /**
     * @param string $city
     */
    public function setCity($city)
    {
        $this->city = $city;
    }

    /**
     * @return string
     */
    public function getCountryIdentifier(): string
    {
        return $this->countryIdentifier;
    }

    /**
     * @param string $countryIdentifier
     */
    public function setCountryIdentifier($countryIdentifier)
    {
        $this->countryIdentifier = $countryIdentifier;
    }

    /**
     * @return null|string
     */
    public function getVatId()
    {
        return $this->vatId;
    }

    /**
     * @param null|string $vatId
     */
    public function setVatId($vatId)
    {
        $this->vatId = $vatId;
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
     * @return Attribute[]
     */
    public function getAttributes(): array
    {
        return $this->attributes;
    }

    /**
     * @param Attribute[] $attributes
     */
    public function setAttributes(array $attributes)
    {
        $this->attributes = $attributes;
    }

    /**
     * {@inheritdoc}
     */
    public function getClassProperties()
    {
        return [
            'company' => $this->getCompany(),
            'department' => $this->getDepartment(),
            'gender' => $this->getGender(),
            'title' => $this->getTitle(),
            'firstname' => $this->getFirstname(),
            'lastname' => $this->getLastname(),
            'street' => $this->getStreet(),
            'additional' => $this->getAdditional(),
            'postalCode' => $this->getPostalCode(),
            'city' => $this->getCity(),
            'countryIdentifier' => $this->getCountryIdentifier(),
            'vatId' => $this->getVatId(),
            'phoneNumber' => $this->getPhoneNumber(),
            'mobilePhoneNumber' => $this->getMobilePhoneNumber(),
            'attributes' => $this->getAttributes(),
        ];
    }
}
