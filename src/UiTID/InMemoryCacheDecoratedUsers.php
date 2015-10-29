<?php
/**
 * @file
 */

namespace CultuurNet\UDB3\UiTID;

use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use Psr\Log\NullLogger;
use ValueObjects\String\String;
use ValueObjects\Web\EmailAddress;

class InMemoryCacheDecoratedUsers implements UsersInterface, LoggerAwareInterface
{
    use LoggerAwareTrait;

    /**
     * @var UsersInterface
     */
    private $wrapped;

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
    }

    /**
     * @inheritdoc
     */
    public function byEmail(EmailAddress $email)
    {
        $key = $email->toNative();
        if (!isset($this->mailMap[$key])) {
            $this->mailMap[$key] = $this->wrapped->byEmail($email);
        }
        else {
            $this->logger->info('found user id of ' . $email->toNative() . ' in cache');
        }

        return $this->mailMap[$key];
    }

    /**
     * @inheritdoc
     */
    public function byNick(String $nick)
    {
        $key = $nick->toNative();
        if (!isset($this->nickMap[$key])) {
            $this->nickMap[$key] = $this->wrapped->byNick($nick);
        }
        else {
            $this->logger->info('found user id of ' . $nick->toNative() . ' in cache');
        }

        return $this->nickMap[$key];
    }

}
