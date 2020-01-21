<?php

namespace CultuurNet\UDB3\UiTID;

use CultuurNet\UDB3\Cdb\CreatedByToUserIdResolverInterface;
use CultuurNet\UDB3\User\UserIdentityResolverInterface;
use Exception;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use Psr\Log\NullLogger;
use ValueObjects\Exception\InvalidNativeArgumentException;
use ValueObjects\Identity\UUID;
use ValueObjects\StringLiteral\StringLiteral;
use ValueObjects\Web\EmailAddress;

class CdbXmlCreatedByToUserIdResolver implements LoggerAwareInterface, CreatedByToUserIdResolverInterface
{
    use LoggerAwareTrait;

    /**
     * @var UserIdentityResolverInterface
     */
    private $users;

    public function __construct(UserIdentityResolverInterface $users)
    {
        $this->users = $users;
        $this->logger = new NullLogger();
    }

    /**
     * @inheritdoc
     */
    public function resolveCreatedByToUserId(StringLiteral $createdByIdentifier): ?StringLiteral
    {
        $userId = null;

        try {
            UUID::fromNative($createdByIdentifier->toNative());
            return $createdByIdentifier;
        } catch (InvalidNativeArgumentException $exception) {
            $this->logger->info(
                'The provided createdByIdentifier ' . $createdByIdentifier->toNative() . ' is not a UUID.',
                [
                    'exception' => $exception,
                ]
            );
        }

        try {
            $userId = $this->resolveByEmailOrByNick($createdByIdentifier);

            if (!$userId) {
                $this->logger->warning(
                    'Unable to find user with identifier ' . $createdByIdentifier
                );
            }
        } catch (Exception $e) {
            $this->logger->error(
                sprintf(
                    'An unexpected error occurred while resolving user with identifier %s',
                    $createdByIdentifier
                ),
                [
                    'exception' => $e,
                ]
            );
        }

        return $userId;
    }

    /**
     * @param StringLiteral $createdByIdentifier
     * @return StringLiteral|null
     */
    private function resolveByEmailOrByNick(StringLiteral $createdByIdentifier): ?StringLiteral
    {
        try {
            $email = new EmailAddress($createdByIdentifier->toNative());
            $user = $this->users->getUserByEmail($email);
        } catch (InvalidNativeArgumentException $e) {
            $user = $this->users->getUserByNick($createdByIdentifier);
        }

        if (!$user) {
            return null;
        }

        return $user->getUserId();
    }
}
