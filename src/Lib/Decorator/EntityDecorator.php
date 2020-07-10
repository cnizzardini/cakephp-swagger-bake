<?php
declare(strict_types=1);

namespace SwaggerBake\Lib\Decorator;

use Cake\ORM\Entity;
use ReflectionClass;
use ReflectionException;
use SwaggerBake\Lib\Exception\SwaggerBakeRunTimeException;

/**
 * Class EntityDecorator
 *
 * @package SwaggerBake\Lib\Decorator
 *
 * Decorates Cake\ORM\Entity
 */
class EntityDecorator
{
    /**
     * @var \Cake\ORM\Entity
     */
    private $entity;

    /**
     * @var string
     */
    private $name = '';

    /**
     * @var string
     */
    private $fqns;

    /**
     * @var \SwaggerBake\Lib\Decorator\PropertyDecorator[]
     */
    private $properties = [];

    /**
     * @param \Cake\ORM\Entity $entity Entity
     * @throws \ReflectionException
     */
    public function __construct(Entity $entity)
    {
        $this->entity = $entity;
        $this->fqns = get_class($entity);
        $this->name = (new ReflectionClass($entity))->getShortName();
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @param string $name Name of the entity
     * @return $this
     */
    public function setName(string $name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * @return \SwaggerBake\Lib\Decorator\PropertyDecorator[]
     */
    public function getProperties(): array
    {
        return $this->properties;
    }

    /**
     * @param \SwaggerBake\Lib\Decorator\PropertyDecorator[] $properties Array of PropertyDecorator objects
     * @return $this
     */
    public function setProperties(array $properties)
    {
        $this->properties = $properties;

        return $this;
    }

    /**
     * @return \Cake\ORM\Entity
     */
    public function getEntity(): Entity
    {
        return $this->entity;
    }

    /**
     * @param \Cake\ORM\Entity $entity Entity
     * @return $this
     */
    public function setEntity(Entity $entity)
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
     * @param string $fqns Fully Qualified Namespace of the Entity
     * @return $this
     */
    public function setFqns(string $fqns)
    {
        $this->fqns = $fqns;

        return $this;
    }
}
