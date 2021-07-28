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
     * @return \SwaggerBake\Lib\OpenApi\Path
     */
    public function create(string $resource): Path
    {
        return (new Path())->setResource($resource);
    }
}
