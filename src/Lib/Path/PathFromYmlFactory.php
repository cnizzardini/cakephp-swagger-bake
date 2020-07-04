<?php
declare(strict_types=1);

namespace SwaggerBake\Lib\Path;

use SwaggerBake\Lib\OpenApi\Path;

/**
 * Class PathFromYmlFactory
 *
 * @package SwaggerBake\Lib\Path
 */
class PathFromYmlFactory
{
    /**
     * Creates a Path from Yml definitions that have been converted into an array
     *
     * @param string $resource Resource name
     * @param array $yaml OpenApi YAML as an array
     * @return \SwaggerBake\Lib\OpenApi\Path
     */
    public function create(string $resource, array $yaml): Path
    {
        return (new Path())->setResource($resource);

        /*
        foreach ($vars as $httpMethod => $var) {
            $operation = (new Operation())
                ->setHttpMethod($httpMethod)
                ->setSummary(isset($var['summary']) ? $var['summary'] : '')
                ->setDescription(isset($var['description']) ? $var['description'] : '')
                ->setTags(isset($var['tags']) ? $var['tags'] : [])
                ->setOperationId(isset($var['operationId']) ? $var['operationId'] : '')
                ->setDeprecated((bool)isset($var['deprecated']) ? $var['deprecated'] : false);

                if (isset($vars['externalDocs'])) {
                    $path->setExternalDocs(
                        (new OperationExternalDoc())
                            ->setDescription($vars['externalDocs']['description'])
                            ->setUrl($vars['externalDocs']['url'])
                    );
                }

                if (isset($vars['security']) && is_array($vars['security'])) {
                    foreach ($vars['security'] as $key => $scopes) {
                        $path->pushSecurity((new PathSecurity())->setName($key)->setScopes($scopes));
                    }
                }
            }
        }
        */
    }
}
