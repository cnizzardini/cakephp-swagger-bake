<?php

namespace SwaggerBake\Lib\Decorator;

use Cake\ORM\Entity;
use ReflectionClass;
use ReflectionException;
use SwaggerBake\Lib\Exception\SwaggerBakeRunTimeException;

class EntityDecorator
{
    /** @var Entity  */
    private $entity;

    /** @var string  */
    private $name = '';

    /** @var string */
    private $fqns;

    /** @var PropertyDecorator[]  */
    private $properties = [];

    public function __construct(Entity $entity)
    {
        $this->entity = $entity;
        $this->fqns = get_class($entity);

        try {
            $this->name = (new ReflectionClass($entity))->getShortName();
        } catch(ReflectionException $e) {
            throw new SwaggerBakeRunTimeException('ReflectionException: ' . $e->getMessage());
        }
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @param string $name
     * @return EntityDecorator
     */
    public function setName(string $name): EntityDecorator
    {
        $this->name = $name;
        return $this;
    }

    /**
     * @return PropertyDecorator[]
     */
    public function getProperties(): array
    {
        return $this->properties;
    }

    /**
     * @param PropertyDecorator[] $properties
     * @return EntityDecorator
     */
    public function setProperties(array $properties): EntityDecorator
    {
        $this->properties = $properties;
        return $this;
    }

    /**
     * @return Entity
     */
    public function getEntity(): Entity
    {
        return $this->entity;
    }

    /**
     * @param Entity $entity
     * @return EntityDecorator
     */
    public function setEntity(Entity $entity): EntityDecorator
    {
        $this->entity = $entity;
        return $this;
    }

    /**
     * @return string
     */
    public function getFqns(): string
    {
        return $this->fqns;
    }

    /**
     * @param string $fqns
     * @return EntityDecorator
     */
    public function setFqns(string $fqns): EntityDecorator
    {
        $this->fqns = $fqns;
        return $this;
    }
}