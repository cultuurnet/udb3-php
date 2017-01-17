<?php
/**
 * @file
 */

namespace CultuurNet\UDB3\UiTID;

use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use Psr\Log\NullLogger;
use ValueObjects\StringLiteral\StringLiteral;
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
        $this->nickMap = [];
        $this->mailMap = [];
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
