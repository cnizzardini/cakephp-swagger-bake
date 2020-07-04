<?php
declare(strict_types=1);

namespace SwaggerBake\Lib\Annotation;

use Cake\Log\Log;

/**
 * @Annotation
 * @Target({"METHOD"})
 * @Attributes({
 * @Attribute("refEntity", type = "string"),
 * @Attribute("httpCode", type = "integer"),
 * @Attribute("statusCode", type = "string"),
 * @Attribute("description", type = "string"),
 * @Attribute("mimeType", type = "string"),
 * @Attribute("schemaType", type = "string"),
 * @Attribute("schemaFormat", type = "string"),
 * })
 */
class SwagResponseSchema
{
    /** @var array  */
    private const DEFAULTS = [
        'refEntity' => '',
        'httpCode' => '200',
        'statusCode' => '200',
        'description' => '',
        'mimeType' => '',
        'schemaType' => '',
        'schemaFormat' => '',
    ];

    /**
     * @var string
     */
    public $refEntity;

    /**
     * @var int
     */
    public $httpCode = 200;

    /**
     * @var string
     */
    public $statusCode = '200';

    /**
     * @var string
     */
    public $description;

    /**
     * @var string
     */
    public $mimeType;

    /**
     * @var string
     */
    public $schemaType;

    /**
     * @var string
     */
    public $schemaFormat;

    /**
     * @param array $values Annotation attributes as key-value pair
     */
    public function __construct(array $values)
    {
        if (isset($values['httpCode'])) {
            $this->httpCode = (string)$values['httpCode'];
            $deprecationMsg = 'SwaggerBake: httpCode will be deprecated, use statusCode in SwagResponseSchema';
            Log::warning($deprecationMsg);
            deprecationWarning($deprecationMsg);
        }

        if (isset($values['statusCode'])) {
            $this->httpCode = $values['statusCode'];
        }

        $values = array_merge(self::DEFAULTS, $values);

        $this->refEntity = $values['refEntity'];
        $this->description = $values['description'];
        $this->mimeType = $values['mimeType'];
        $this->schemaType = $values['schemaType'];
        $this->schemaFormat = $values['schemaFormat'];
    }
}
