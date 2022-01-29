<?php

namespace SwaggerBakeTest\App\Dto;

use SwaggerBake\Lib\Attribute\OpenApiDtoQuery;
use SwaggerBake\Lib\Attribute\OpenApiDtoRequestBody;

class EmployeeDataRequest
{
    #[OpenApiDtoRequestBody(name: 'first_name', description: 'testing')]
    #[OpenApiDtoQuery(name: 'first_name', description: 'testing')]
    private string $firstName;

    #[OpenApiDtoRequestBody(name: 'last_name', description: 'testing')]
    #[OpenApiDtoQuery(name: 'last_name', description: 'testing')]
    private string $lastName;

    #[OpenApiDtoRequestBody(name: 'title', description: 'testing')]
    #[OpenApiDtoQuery(name: 'title', description: 'testing')]
    private string $title;

    #[OpenApiDtoRequestBody(name: 'age', type: 'integer', format: 'int32', description: 'testing')]
    #[OpenApiDtoQuery(name: 'age', type: 'integer', format: 'int32', description: 'testing')]
    private string $age;

    #[OpenApiDtoRequestBody(name: 'date', type: 'string', format: 'date', description: 'testing')]
    #[OpenApiDtoQuery(name: 'date', type: 'string', format: 'date', description: 'testing')]
    private string $date;

    /**
     * @return string
     */
    public function getFirstName(): string
    {
        return $this->firstName;
    }

    /**
     * @param string $firstName
     * @return EmployeeDataRequest
     */
    public function setFirstName(string $firstName): EmployeeDataRequest
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
     * @return EmployeeDataRequest
     */
    public function setLastName(string $lastName): EmployeeDataRequest
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
     * @return EmployeeDataRequest
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
     * @return EmployeeDataRequest
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
     * @return EmployeeDataRequest
     */
    public function setDate($date)
    {
        $this->date = $date;
        return $this;
    }
}
