<?php

namespace CultuurNet\UDB3\UiTID;

use CultuurNet\UDB3\Cdb\CreatedByToUserIdResolverInterface;
use Exception;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use Psr\Log\NullLogger;
use ValueObjects\Exception\InvalidNativeArgumentException;
use ValueObjects\StringLiteral\StringLiteral;
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
    public function resolveCreatedByToUserId(StringLiteral $createdByIdentifier): ?StringLiteral
    {
        $userId = null;

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
            $userId = $this->users->byEmail($email);
        } catch (InvalidNativeArgumentException $e) {
            $userId = $this->users->byNick($createdByIdentifier);
        }

        return $userId;
    }
}
