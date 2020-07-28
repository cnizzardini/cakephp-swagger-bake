<?php
declare(strict_types=1);

namespace SwaggerBake\Lib\Annotation;

use SwaggerBake\Lib\Exception\SwaggerBakeRunTimeException;

/**
 * Annotation for describing Path Parameter Objects.
 *
 * The name specified for Path Parameter must exist in your existing routes for the path parameter to be applied.
 *
 * @Annotation
 * @Target({"METHOD"})
 * @Attributes({
 * @Attribute("name", type = "string"),
 * @Attribute("type",  type = "string"),
 * @Attribute("description",  type = "string"),
 * @Attribute("allowReserved",  type = "bool"),
 * @Attribute("format",  type = "string"),
 * @Attribute("example",  type = "mixed"),
 * })
 *
 * Example: Defining a path parameter
 *
 * `@Swag\SwagPathParameter(name="id", type="integer", format="int64", description="ID")`
 *
 * ```yaml
 *         parameters:
 *           - name: id
 *             required: true
 *             schema:
 *               description: ID
 *               type: integer
 *               format: int64
 * ```
 *
 * Read OpenAPI specification for exact usage of the attributes:
 * @see https://swagger.io/specification/ search for "Parameter Object"
 * @see https://swagger.io/docs/specification/describing-parameters/#path-parameters
 * @see AbstractParameter
 */
class SwagPathParameter extends AbstractParameter
{
    /**
     * @param array $values annotation values
     */
    public function __construct(array $values)
    {
        $values['required'] = true;
        if (!in_array($values['type'], ['string','integer'])) {
            throw new SwaggerBakeRunTimeException('Path parameter must be an integer or string');
        }

        parent::__construct($values);
    }
}
