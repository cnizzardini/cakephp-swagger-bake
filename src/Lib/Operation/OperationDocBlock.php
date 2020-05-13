<?php

namespace SwaggerBake\Lib\Operation;

use phpDocumentor\Reflection\DocBlock;
use SwaggerBake\Lib\OpenApi\Operation;
use SwaggerBake\Lib\OpenApi\OperationExternalDoc;

class OperationDocBlock
{
    /**
     * @param Operation $operation
     * @param DocBlock $doc
     * @return Operation
     */
    public function getOperationWithDocBlock(Operation $operation, DocBlock $doc) : Operation
    {
        if ($doc->hasTag('deprecated')) {
            $operation->setDeprecated(true);
        }

        if (!$doc->hasTag('see')) {
            return $operation;
        }

        $tags = $doc->getTagsByName('see');
        $seeTag = reset($tags);
        $str = $seeTag->__toString();
        $pieces = explode(' ', $str);

        if (!filter_var($pieces[0], FILTER_VALIDATE_URL)) {
            return $operation;
        }

        $externalDoc = new OperationExternalDoc();
        $externalDoc->setUrl($pieces[0]);

        array_shift($pieces);

        if (!empty($pieces)) {
            $externalDoc->setDescription(implode(' ', $pieces));
        }

        return $operation->setExternalDocs($externalDoc);
    }
}