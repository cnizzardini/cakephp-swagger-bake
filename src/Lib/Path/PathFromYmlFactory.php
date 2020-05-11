<?php

namespace SwaggerBake\Lib\Path;

use SwaggerBake\Lib\OpenApi\Path;

class PathFromYmlFactory
{
    /**
     * Creates a Path from Yml definitions that have been converted into an array
     *
     * @param string $path
     * @param array $var
     * @return Path|null
     */
    public function create(string $path, array $var) : ?Path
    {
        return (new Path())
            ->setPath($path)
            ->setSummary(isset($var['summary']) ? $var['summary'] : '')
            ->setDescription(isset($var['description']) ? $var['description'] : '');
    }
}