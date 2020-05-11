<?php

namespace SwaggerBake\Lib\Path;

use SwaggerBake\Lib\OpenApi\Path;

class PathFromYmlFactory
{
    /**
     * Creates a Path from Yml definitions that have been converted into an array
     *
     * @param string $resource
     * @param array $var
     * @return Path
     */
    public function create(string $resource, array $var) : Path
    {
        return (new Path())
            ->setResource($resource)
            ->setSummary(isset($var['summary']) ? $var['summary'] : '')
            ->setDescription(isset($var['description']) ? $var['description'] : '');
    }
}