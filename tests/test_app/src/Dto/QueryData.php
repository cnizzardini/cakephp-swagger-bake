<?php

namespace SwaggerBakeTest\App\Dto;

class QueryData
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
     * @return QueryData
     */
    public function setFirstName(string $firstName): QueryData
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
     * @return QueryData
     */
    public function setLastName(string $lastName): QueryData
    {
        $this->lastName = $lastName;
        return $this;
    }
}
