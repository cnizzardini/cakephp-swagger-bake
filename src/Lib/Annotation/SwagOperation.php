<?php
declare(strict_types=1);

namespace SwaggerBake\Lib\Annotation;

/**
 * @deprecated Use OpenApiOperation
 * @codeCoverageIgnore
 * @Annotation
 * @Target({"METHOD"})
 * @Attributes({
 * @Attribute("isVisible", type="bool"),
 * @Attribute("tagNames", type="array"),
 * @Attribute("showPut", type="bool")
 * })
 * @see https://swagger.io/docs/specification/paths-and-operations/
 */
class SwagOperation
{
    /**
     * Setting to false will not add controller:action as an OpenApi operation
     *
     * default: true
     *
     * @var bool
     */
    public $isVisible;

    /**
     * OpenApi operation tag names
     *
     * default: Controller name without Controller, (e.g. Actors not ActorsController)
     *
     * @var string[]
     * @see https://swagger.io/docs/specification/grouping-operations-with-tags/
     */
    public $tagNames;

    /**
     * Allows HTTP PUT on controller::edit crud action, default is false
     *
     * @var bool
     */
    public $showPut = false;

    /**
     * @param array $values Annotation attributes as key-value pair
     */
    public function __construct(array $values)
    {
        $values = array_merge(
            ['isVisible' => true, 'tagNames' => [], 'httpMethod' => null, 'showPut' => false],
            $values
        );
        $this->isVisible = (bool)$values['isVisible'];
        $this->tagNames = $values['tagNames'];
        $this->showPut = (bool)$values['showPut'];
    }
}
