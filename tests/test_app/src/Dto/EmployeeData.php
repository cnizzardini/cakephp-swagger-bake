<?php

namespace SwaggerBakeTest\App\Dto;

class EmployeeData
{
    /**
     * Last name required
     * @var string
     * @required
     */
    private $lastName;

    /** @var string */
    private $firstName;

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
}
