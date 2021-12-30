<?php
declare(strict_types=1);

namespace SwaggerBake\Lib;

use SwaggerBake\Lib\OpenApi\Schema;

interface OpenApiExceptionSchemaInterface
{
    /**
     * The HTTP status code (numeric string) of this exception. This value will be used to build the OpenAPI response.
     *
     * @link https://spec.openapis.org/oas/v3.0.3#responses-object
     * @return string
     */
    public static function getExceptionCode(): string;

    /**
     * The OpenAPI response description. This value will be used to build the OpenAPI response. Returning null will
     * omit the response description.
     *
     * @link https://spec.openapis.org/oas/v3.0.3#responses-object
     * @return string|null
     */
    public static function getExceptionDescription(): ?string;

    /**
     * Describes an exception by returning an OpenAPI Schema or a $ref string. If returning a OpenAPI $ref string, the
     * $ref must exist in your base OpenAPI YAML file.
     *
     * @see \SwaggerBake\Lib\OpenApi\Schema
     * @return \SwaggerBake\Lib\OpenApi\Schema|string
     */
    public static function getExceptionSchema(): Schema|string;
}
