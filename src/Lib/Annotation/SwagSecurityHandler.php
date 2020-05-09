<?php

namespace SwaggerBake\Lib\Annotation;

use SwaggerBake\Lib\OpenApi\PathSecurity;

class SwagSecurityHandler
{
    /**
     * @param SwagSecurity $annotation
     * @return PathSecurity
     */
    public function getPathSecurity(SwagSecurity $annotation) : PathSecurity
    {
        return (new PathSecurity())
            ->setName($annotation->name)
            ->setScopes($annotation->scopes)
        ;
    }
}