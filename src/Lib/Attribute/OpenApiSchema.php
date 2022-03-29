<?php
declare(strict_types=1);

namespace SwaggerBake\Lib\Attribute;

use Attribute;
use InvalidArgumentException;
use SwaggerBake\Lib\OpenApi\Schema;

#[Attribute(Attribute::TARGET_CLASS)]
class OpenApiSchema
{
    /**
     *  Default behavior. Adds the schema if it matches a controller with a restful route.
     */
    public const VISIBLE_DEFAULT = 1;

    /**
     * Always add the schema.
     */
    public const VISIBLE_ALWAYS = 2;

    /**
     * Never add the schema to the default location, but adds it to vendor location. This hides the schema from the
     * Swagger UIs Schemas section, but still allows the schema to be used for request and response bodies.
     */
    public const VISIBLE_HIDDEN = 3;

    /**
     * Never add the Schema. Warning this can break request body definitions and response samples.
     */
    public const VISIBLE_NEVER = 4;

    /**
     * @deprecated deprecated because of misspelling
     */
    public const VISIBILE_DEFAULT = 1;

    /**
     * @deprecated deprecated because of misspelling
     */
    public const VISIBILE_ALWAYS = 2;

    /**
     * @deprecated deprecated because of misspelling
     */
    public const VISIBILE_HIDDEN = 3;

    /**
     * @deprecated deprecated because of misspelling
     */
    public const VISIBILE_NEVER = 4;

    /**
     * @param int $visibility See class constants for options.
     * @param string|null $title The title of the schema
     * @param string|null $description The description of the schema
     * @todo convert to readonly properties in PHP 8.1
     */
    public function __construct(
        public int $visibility = 1,
        public ?string $title = null,
        public ?string $description = null
    ) {
        if ($this->visibility < 1 || $this->visibility > 4) {
            throw new InvalidArgumentException(
                'OpenApiSchema visibility must be 1 through 4. See class constants'
            );
        }
    }

    /**
     * Create a Schema from this attribute instance.
     *
     * @return \SwaggerBake\Lib\OpenApi\Schema
     */
    public function createSchema(): Schema
    {
        return (new Schema($this->title))
            ->setVisibility($this->visibility)
            ->setDescription($this->description);
    }
}
