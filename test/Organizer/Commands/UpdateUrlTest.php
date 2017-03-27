<?php

namespace CultuurNet\UDB3\Organizer\Commands;

use ValueObjects\Web\Url;

class UpdateUrlTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var string
     */
    private $organizerId;

    /**
     * @var Url
     */
    private $url;

    /**
     * @var UpdateUrl
     */
    private $updateUrl;

    protected function setUp()
    {
        $this->organizerId = '8f9f5180-1099-474e-804c-461fc3701e5c';

        $this->url = Url::fromNative('http://www.company.be');

        $this->updateUrl = new UpdateUrl(
            $this->organizerId,
            $this->url
        );
    }

    /**
     * @test
     */
    public function it_stores_an_organizer_id()
    {
        $this->assertEquals(
            $this->organizerId,
            $this->updateUrl->getOrganizerId()
        );
    }

    /**
     * @test
     */
    public function it_stores_a_url()
    {
        $this->assertEquals(
            $this->url,
            $this->updateUrl->getUrl()
        );
    }
}
