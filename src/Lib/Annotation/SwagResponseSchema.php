<?php
declare(strict_types=1);

namespace SwaggerBake\Lib\Annotation;

/**
 * Method level annotation for defining custom response schema for OpenApi response content.
 *
 * @Annotation
 * @Target({"METHOD"})
 * @Attributes({
 * @Attribute("refEntity", type = "string"),
 * @Attribute("statusCode", type = "string"),
 * @Attribute("description", type = "string"),
 * @Attribute("mimeTypes", type = "array"),
 * @Attribute("schemaType", type = "string"),
 * @Attribute("schemaFormat", type = "string"),
 * @Attribute("associations", type = "array"),
 * })
 * @see https://swagger.io/docs/specification/describing-responses/
 * @see https://swagger.io/specification/
 */
class SwagResponseSchema
{
    /**
     * Schema.$ref
     *
     * @var string
     * @example #/components/schema/EntityName
     */
    public $refEntity;

    /**
     * The http status code, can be alphanumeric (e.g. 200, 20x, 4xx)
     *
     * @var string
     */
    public $statusCode;

    /**
     * Response Schema description
     *
     * @var string
     */
    public $description;

    /**
     * Response Content mime types
     *
     * @var array
     * @example mimeTypes={"application/json","application/xml"}
     */
    public $mimeTypes;

    /**
     * The data type of the response schema, generally object or array
     *
     * @var string
     * @example object
     * @example array
     * @example string
     */
    public $schemaType;

    /**
     * The date format of the schema, not generally applicable for object or array schemaType's
     *
     * @var string
     * @example date-time
     * @example base64
     */
    public $schemaFormat;

    /**
     * Configuration for displaying a resources associations. If set to empt the defaults below will be used
     *
     * @var array
     * - table<string|null> - the base table name, default is to infer from the controller but if not found then an
     * exception will be thrown.
     * - whiteList<array|null> - a list of tables to show n the sample schema, defaults to all associations for the depth
     */
    public $associations;

    /**
     * @param array $values Annotation attributes as key-value pair
     */
    public function __construct(array $values)
    {
        $this->statusCode = $values['statusCode'] ?? '200';
        $this->refEntity = $values['refEntity'] ?? '';
        $this->description = $values['description'] ?? '';
        $this->mimeTypes = $values['mimeTypes'] ?? null;
        $this->schemaType = $values['schemaType'] ?? '';
        $this->schemaFormat = $values['schemaFormat'] ?? '';
        if (isset($values['associations'])) {
            $this->associations = array_replace(
                ['depth' => 1, 'table' => null, 'whiteList' => null],
                $values['associations']
            );
        }
    }
}
