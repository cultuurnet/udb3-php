<?php

namespace CultuurNet\UDB3\Organizer\Events;

use ValueObjects\Web\Url;

class UrlUpdatedTest extends \PHPUnit_Framework_TestCase
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
     * @var UrlUpdated
     */
    private $UrlUpdated;

    /**
     * @var array
     */
    private $urlUpdatedAsArray;

    protected function setUp()
    {
        $this->organizerId = '11cab069-7355-4fbc-bb82-eef9edfd7788';

        $this->url = Url::fromNative('http://www.depot.be');

        $this->UrlUpdated = new UrlUpdated(
            $this->organizerId,
            $this->url
        );

        $this->urlUpdatedAsArray = [
            'organizer_id' =>  $this->organizerId,
            'url' => (string) $this->url
        ];
    }

    /**
     * @test
     */
    public function it_can_serialize_to_an_array()
    {
        $this->assertEquals(
            $this->urlUpdatedAsArray,
            $this->UrlUpdated->serialize()
        );
    }

    /**
     * @test
     */
    public function it_can_deserialize_from_an_array()
    {
        $this->assertEquals(
            UrlUpdated::deserialize($this->urlUpdatedAsArray),
            $this->UrlUpdated
        );
    }
}
