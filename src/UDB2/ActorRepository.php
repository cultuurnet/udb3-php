<?php

/**
 * @file
 * Contains \Cultuurnet\UDB3\UDB2\ActorRepository.
 */

namespace CultuurNet\UDB3\UDB2;

use Broadway\Repository\AggregateNotFoundException;
use CultuurNet\Search\Parameter\FilterQuery;
use CultuurNet\Search\Parameter\Query;

/**
 * Repository decorator that first updates UDB2.
 *
 * When a failure on UDB2 occurs, the whole transaction will fail.
 */
abstract class ActorRepository extends EntityRepository
{

}
