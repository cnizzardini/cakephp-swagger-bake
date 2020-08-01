<?php
declare(strict_types=1);

namespace SwaggerBake\Lib\Annotation;

use Cake\Log\Log;

/**
 * Method level annotation for defining custom response schema for OpenApi response content.
 *
 * @Annotation
 * @Target({"METHOD"})
 * @Attributes({
 * @Attribute("refEntity", type = "string"),
 * @Attribute("httpCode", type = "integer"),
 * @Attribute("statusCode", type = "string"),
 * @Attribute("description", type = "string"),
 * @Attribute("mimeType", type = "string"),
 * @Attribute("mimeTypes", type = "string"),
 * @Attribute("schemaType", type = "string"),
 * @Attribute("schemaFormat", type = "string"),
 * @Attribute("schemaItems", type = "array")
 * })
 * @see https://swagger.io/docs/specification/describing-responses/
 * @see https://swagger.io/specification/
 * @todo remove httpCode in future version
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
     * Response HTTP Status Code such as 200, 40x, or 5xx
     *
     * @var string
     * @example 200
     */
    public $httpCode = '200';

    /**
     * Response Schema description
     *
     * @var string
     */
    public $description;

    /**
     * Response Content mime type
     *
     * @var string
     * @example application/json
     * @deprecated use $mimeTypes
     */
    public $mimeType;

    /**
     * Response Content mime types
     *
     * @var array
     * @example mimeTypes={"application/json","application/xml"}
     */
    public $mimeTypes;

    /**
     * The data type of the schema
     *
     * @var string
     * @example object
     * @example array
     * @example string
     */
    public $schemaType;

    /**
     * The date format of the schema
     *
     * @var string
     * @example date-time
     * @example base64
     */
    public $schemaFormat;

    /**
     * Key-value pair for schema items
     *
     * @var array
     */
    public $schemaItems = [];

    /**
     * Default values
     *
     * @var array
     */
    private const DEFAULTS = [
        'refEntity' => '',
        'httpCode' => '200',
        'statusCode' => '200',
        'description' => '',
        'mimeType' => '',
        'mimeTypes' => [],
        'schemaType' => '',
        'schemaFormat' => '',
        'schemaItems' => [],
    ];

    /**
     * @param array $values Annotation attributes as key-value pair
     */
    public function __construct(array $values)
    {
        if (isset($values['httpCode'])) {
            $this->httpCode = (string)$values['httpCode'];
            $msg = 'SwaggerBake: `httpCode` is deprecated, use `statusCode` in SwagResponseSchema';
            Log::warning($msg);
            deprecationWarning($msg);
        }

        if (isset($values['mimeType'])) {
            array_push($values['mimeTypes'], $values['mimeType']);
            $msg = 'SwaggerBake: `mimeType` is deprecated, use `mimeTypes` in SwagResponseSchema';
            Log::warning($msg);
            deprecationWarning($msg);
        }

        if (isset($values['statusCode'])) {
            $this->httpCode = $values['statusCode'];
        }

        $values = array_merge(self::DEFAULTS, $values);

        $this->refEntity = $values['refEntity'];
        $this->description = $values['description'];
        $this->mimeTypes = $values['mimeTypes'];
        $this->schemaType = $values['schemaType'];
        $this->schemaFormat = $values['schemaFormat'];
        $this->schemaItems = $values['schemaItems'];
    }
}
