<?php
declare(strict_types=1);

/**
 * @SuppressWarnings(PHPMD)
 * @deprecated this class will be removed soon, use SwaggerBake\Lib\ParameterFromAnnotationFactory instead
 */
trigger_deprecation(
    'SwaggerBake\Lib\Factory\ParameterFromAnnotationFactory',
    '2.0.0',
    'this class will be removed soon, use SwaggerBake\Lib\ParameterFromAnnotationFactory instead'
);

class_alias(
    'SwaggerBake\Lib\ParameterFromAnnotationFactory',
    'SwaggerBake\Lib\Factory\ParameterFromAnnotationFactory'
);
