<?php
/**
 * @file
 */

namespace CultuurNet\UDB3\UiTID;

use CultuurNet\UDB3\Cdb\CreatedByToUserIdResolverInterface;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use Psr\Log\NullLogger;
use ValueObjects\Exception\InvalidNativeArgumentException;
use ValueObjects\String\String;
use ValueObjects\Web\EmailAddress;

class CdbXmlCreatedByToUserIdResolver implements LoggerAwareInterface, CreatedByToUserIdResolverInterface
{
    use LoggerAwareTrait;

    /**
     * @var UsersInterface
     */
    private $users;

    /**
     * @param UsersInterface $users
     */
    public function __construct(UsersInterface $users)
    {
        $this->users = $users;
        $this->logger = new NullLogger();
    }

    /**
     * @inheritdoc
     */
    public function resolveCreatedByToUserId(String $createdByIdentifier)
    {
        try {
            $email = new EmailAddress($createdByIdentifier->toNative());
            $userId = $this->users->byEmail($email);
        } catch (InvalidNativeArgumentException $e) {
            $userId = $this->users->byNick($createdByIdentifier);
        }

        if (!$userId) {
            $this->logger->warning(
                'Unable to find user with identifier ' . $createdByIdentifier
            );
        }

        return $userId;
    }
}
