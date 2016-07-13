<?php

namespace CultuurNet\UDB3\Role\Commands;

use CultuurNet\UDB3\Role\MissingContentTypeException;
use CultuurNet\UDB3\Role\UnknownContentTypeException;
use Symfony\Component\HttpFoundation\Request;
use ValueObjects\Identity\UUID;
use ValueObjects\String\String as StringLiteral;

class UpdateRoleRequestDeserializer
{
    public function deserialize(Request $request, $roleId)
    {
        $contentType = $request->headers->get('Content-Type');
        $body_content = json_decode($request->getContent());

        if (empty($contentType)) {
            throw new MissingContentTypeException;
        }

        switch ($contentType) {
            case 'application/ld+json;domain-model=RenameRole':
                return new RenameRole(
                    new UUID($roleId),
                    new StringLiteral($body_content->name)
                );
                break;

            case 'application/ld+json;domain-model=SetConstraint':
                return new SetConstraint(
                    new UUID($roleId),
                    new StringLiteral($body_content->constraint)
                );
                break;
            default:
                throw new UnknownContentTypeException;
                break;
        }
    }
}
