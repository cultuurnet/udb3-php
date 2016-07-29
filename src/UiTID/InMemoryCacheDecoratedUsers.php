<?php
/**
 * @file
 */

namespace CultuurNet\UDB3\UiTID;

use CultuurNet\UDB3\User\UserIdentityDetails;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use Psr\Log\NullLogger;
use ValueObjects\String\String as StringLiteral;
use ValueObjects\Web\EmailAddress;

class InMemoryCacheDecoratedUsers implements UsersInterface, LoggerAwareInterface
{
    use LoggerAwareTrait;

    /**
     * @var UsersInterface
     */
    private $wrapped;

    /**
     * @var UserIdentityDetails[]
     */
    private $userByIdMap;

    /**
     * @var UserIdentityDetails[]
     */
    private $userByEmailMap;

    /**
     * @var UserIdentityDetails[]
     */
    private $userByNickMap;

    /**
     * @var String[]
     */
    private $nickMap;

    /**
     * @var String[]
     */
    private $mailMap;

    /**
     * @param UsersInterface $wrapped
     */
    public function __construct(UsersInterface $wrapped)
    {
        $this->wrapped = $wrapped;
        $this->logger = new NullLogger();
        $this->nickMap = [];
        $this->mailMap = [];
    }

    /**
     * @inheritdoc
     */
    public function getUserById(StringLiteral $userId)
    {
        $key = $userId->toNative();

        if (!array_key_exists($key, $this->userByIdMap)) {
            $this->userByIdMap[$key] = $this->wrapped->getUserById($userId);
        } else {
            $this->logger->info(
                'found user with id ' . $key . ' in cache'
            );
        }

        return $this->userByIdMap[$key];
    }

    /**
     * @inheritdoc
     */
    public function getUserByEmail(EmailAddress $email)
    {
        $key = $email->toNative();

        if (!array_key_exists($key, $this->userByEmailMap)) {
            $this->userByIdMap[$key] = $this->wrapped->getUserByEmail($email);
        } else {
            $this->logger->info(
                'found user with email ' . $key . ' in cache'
            );
        }

        return $this->userByEmailMap[$key];
    }

    /**
     * @inheritdoc
     */
    public function getUserByNick(StringLiteral $nick)
    {
        $key = $nick->toNative();

        if (!array_key_exists($key, $this->userByNickMap)) {
            $this->userByIdMap[$key] = $this->wrapped->getUserByNick($nick);
        } else {
            $this->logger->info(
                'found user with nick ' . $key . ' in cache'
            );
        }

        return $this->userByNickMap[$key];
    }

    /**
     * @inheritdoc
     */
    public function byEmail(EmailAddress $email)
    {
        $key = $email->toNative();
        if (!array_key_exists($key, $this->mailMap)) {
            $this->mailMap[$key] = $this->wrapped->byEmail($email);
        } else {
            $this->logger->info(
                'found user id mapping of ' . $email->toNative() . ' in cache'
            );
        }

        return $this->mailMap[$key];
    }

    /**
     * @inheritdoc
     */
    public function byNick(StringLiteral $nick)
    {
        $key = $nick->toNative();
        if (!array_key_exists($key, $this->nickMap)) {
            $this->nickMap[$key] = $this->wrapped->byNick($nick);
        } else {
            $this->logger->info(
                'found user id mapping of ' . $nick->toNative() . ' in cache'
            );
        }

        return $this->nickMap[$key];
    }
}
