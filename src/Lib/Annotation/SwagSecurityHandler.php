<?php

namespace SwaggerBake\Lib\Annotation;

use SwaggerBake\Lib\OpenApi\PathSecurity;

class SwagSecurityHandler
{
    public function getPathSecurity(SwagSecurity $annotation) : PathSecurity
    {
        $security = new PathSecurity();
        $security
            ->setName($annotation->name)
            ->setScopes($annotation->scopes);

        return $security;
    }
}