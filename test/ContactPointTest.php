<?php

namespace CultuurNet\UDB3;

class ContactPointTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var array
     */
    private $phones;

    /**
     * @var array
     */
    private $emails;

    /**
     * @var array
     */
    private $urls;

    /**
     * @var ContactPoint
     */
    private $contactPoint;

    protected function setUp()
    {
        $this->phones = ['012 34 56 78', '987 65 43 21'];

        $this->emails = ['user1@company.com', 'user2@company.com'];

        $this->urls = ['http//www.company.be', 'http//www.company.com'];

        $this->contactPoint = new ContactPoint(
            $this->phones,
            $this->emails,
            $this->urls
        );
    }

    /**
     * @test
     */
    public function it_stores_phones()
    {
        $this->assertEquals($this->phones, $this->contactPoint->getPhones());
    }

    /**
     * @test
     */
    public function it_stores_emails()
    {
        $this->assertEquals($this->emails, $this->contactPoint->getEmails());
    }

    /**
     * @test
     */
    public function it_stores_urls()
    {
        $this->assertEquals($this->urls, $this->contactPoint->getUrls());
    }

    /**
     * @test
     */
    public function it_can_compare()
    {
        $sameContactPoint = new ContactPoint(
            $this->phones,
            $this->emails,
            $this->urls
        );

        $differentOrderContactPoint = new ContactPoint(
            $this->phones,
            $this->emails,
            ['http//www.company.com', 'http//www.company.be']
        );

        $this->assertTrue($this->contactPoint->sameAs($sameContactPoint));
        $this->assertFalse($this->contactPoint->sameAs($differentOrderContactPoint));
    }
}
