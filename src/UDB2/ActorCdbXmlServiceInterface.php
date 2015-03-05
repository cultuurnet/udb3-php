<?php
/**
 * @file
 */

namespace CultuurNet\UDB3\UDB2;

interface ActorCdbXmlServiceInterface
{
    /**
     * @return string
     */
    public function getCdbXmlNamespaceUri();

    /**
     * @param string $actorId
     * @return string
     * @throws ActorNotFoundException If the actor can not be found.
     */
    public function getCdbXmlOfActor($actorId);
}
