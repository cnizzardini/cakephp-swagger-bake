<?php

namespace SwaggerBakeTest\App\Dto;

use SwaggerBake\Lib\Annotation as Swag;

class EmployeeData
{
    /** @var string */
    private $firstName;

    /**
     * Last name required
     * @var string
     * @required
     */
    private $lastName;

    /**
     * @Swag\SwagRequestBody(name="title", type="string", description="testing")
     * @var string
     */
    private $title;

    /**
     * @Swag\SwagRequestBody(name="age", type="integer", format="int32" description="testing")
     * @var integer
     */
    private $age;

    /**
     * @Swag\SwagRequestBody(name="date", type="string", format="date", description="testing")
     * @var string
     */
    private $date;

    /**
     * @return string
     */
    public function getFirstName(): string
    {
        return $this->firstName;
    }

    /**
     * @param string $firstName
     * @return EmployeeData
     */
    public function setFirstName(string $firstName): EmployeeData
    {
        $this->firstName = $firstName;
        return $this;
    }

    /**
     * @return string
     */
    public function getLastName(): string
    {
        return $this->lastName;
    }

    /**
     * @param string $lastName
     * @return EmployeeData
     */
    public function setLastName(string $lastName): EmployeeData
    {
        $this->lastName = $lastName;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * @param mixed $title
     * @return EmployeeData
     */
    public function setTitle($title)
    {
        $this->title = $title;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getAge()
    {
        return $this->age;
    }

    /**
     * @param mixed $age
     * @return EmployeeData
     */
    public function setAge($age)
    {
        $this->age = $age;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getDate()
    {
        return $this->date;
    }

    /**
     * @param mixed $date
     * @return EmployeeData
     */
    public function setDate($date)
    {
        $this->date = $date;
        return $this;
    }
}
