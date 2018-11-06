<?php

namespace CultuurNet\UDB3\SavedSearches\Properties;

use CultuurNet\UDB3\SavedSearches\ValueObject\CreatedByQueryMode;
use CultuurNet\UDB3\SavedSearches\ValueObject\UserId;
use ValueObjects\Web\EmailAddress;

class CreateByQueryStringTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var UserId
     */
    private $userId;

    /**
     * @var EmailAddress
     */
    private $emailAddress;

    protected function setUp(): void
    {
        $this->userId = new UserId('cef70b98-2d4d-40a9-95f0-762aae66ef3f');

        $this->emailAddress = new EmailAddress('foo@bar.com');
    }

    /**
     * @test
     */
    public function it_creates_a_created_by_query_in_user_mode()
    {
        $queryString = new CreatedByQueryString(
            $this->userId,
            $this->emailAddress,
            CreatedByQueryMode::UUID()
        );

        $expected = 'createdby:' . $this->userId->toNative();

        $this->assertEquals($expected, $queryString);
    }

    /**
     * @test
     */
    public function it_creates_a_created_by_query_in_email_mode()
    {
        $queryString = new CreatedByQueryString(
            $this->userId,
            $this->emailAddress,
            CreatedByQueryMode::EMAIL()
        );

        $expected = 'createdby:' . $this->emailAddress->toNative();

        $this->assertEquals($expected, $queryString);
    }

    /**
     * @test
     */
    public function it_creates_a_created_by_query_in_mixed_mode()
    {
        $queryString = new CreatedByQueryString(
            $this->userId,
            $this->emailAddress,
            CreatedByQueryMode::MIXED()
        );

        $uuid = $this->userId->toNative();
        $email = $this->emailAddress->toNative();
        $expected = 'createdby:' . '(' . $uuid . ' OR ' . $email . ')';

        $this->assertEquals($expected, $queryString);
    }
}
