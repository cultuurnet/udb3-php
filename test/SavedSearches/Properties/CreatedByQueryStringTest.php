<?php

namespace CultuurNet\UDB3\SavedSearches\Properties;

class CreateByQueryStringTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var string
     */
    private $userId;

    /**
     * @var string
     */
    private $emailAddress;

    protected function setUp(): void
    {
        $this->userId = 'cef70b98-2d4d-40a9-95f0-762aae66ef3f';
        $this->emailAddress = 'foo@bar.com';
    }

    /**
     * @test
     */
    public function it_creates_a_created_by_query_in_user_mode()
    {
        $queryString = new CreatedByQueryString(
            [
                $this->userId,
            ]
        );

        $expected = 'createdby:' . $this->userId;

        $this->assertEquals($expected, $queryString);
    }

    /**
     * @test
     */
    public function it_creates_a_created_by_query_in_email_mode()
    {
        $queryString = new CreatedByQueryString(
            [
                $this->emailAddress,
            ]
        );

        $expected = 'createdby:' . $this->emailAddress;

        $this->assertEquals($expected, $queryString);
    }

    /**
     * @test
     */
    public function it_creates_a_created_by_query_in_mixed_mode()
    {
        $queryString = new CreatedByQueryString(
            [
                $this->userId,
                $this->emailAddress,
            ]
        );

        $expected = 'createdby:' . '(' . $this->userId . ' OR ' . $this->emailAddress . ')';

        $this->assertEquals($expected, $queryString);
    }
}
