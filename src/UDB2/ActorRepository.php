<?php

/**
 * @file
 * Contains \Cultuurnet\UDB3\UDB2\ActorRepository.
 */

namespace CultuurNet\UDB3\UDB2;

/**
 * Repository decorator that first updates UDB2.
 *
 * When a failure on UDB2 occurs, the whole transaction will fail.
 */
abstract class ActorRepository extends EntityRepository
{

}
