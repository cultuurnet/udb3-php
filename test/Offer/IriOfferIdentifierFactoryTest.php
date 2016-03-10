<?php

namespace CultuurNet\UDB3\Offer;

use CultuurNet\UDB3\Variations\Command\ValidationException;

class IriOfferIdentifierFactoryTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var string
     */
    private $regex;

    /**
     * @var IriOfferIdentifierFactory
     */
    private $iriOfferIdentifierFactory;

    public function setUp()
    {
        $this->regex = 'https?://foo\.bar/(?<offertype>[event|place]+)/(?<offerid>[a-zA-Z0-9\-]+)';
        $this->iriOfferIdentifierFactory = new IriOfferIdentifierFactory(
            $this->regex
        );
    }

    /**
     * @test
     */
    public function it_throws_an_error_when_using_a_malformed_url()
    {
        $this->setExpectedException(
            ValidationException::class,
            'Invalid data'
        );

        $this->iriOfferIdentifierFactory->fromIri(
            'abcdefghijklmnopqrstuvwxyz'
        );
    }

    /**
     * @test
     */
    public function it_throws_an_error_when_using_an_unsupported_offer_type()
    {
        $this->setExpectedException(
            ValidationException::class,
            'Invalid data'
        );

        $this->iriOfferIdentifierFactory->fromIri(
            'https://culudb-silex.dev:8080/kwiet/foo-bar'
        );
    }

    /**
     * @test
     */
    public function it_throws_an_error_when_the_offertype_index_is_not_found()
    {
        $iriOfferIdentifierFactory = new IriOfferIdentifierFactory(
            'https?://foo\.bar/(?<offer>[event|place]+)/(?<offerid>[a-zA-Z0-9\-]+)'
        );

        $this->setExpectedException(
            \InvalidArgumentException::class,
            'Regular expression pattern should capture group named "offertype"'
        );

        $iriOfferIdentifierFactory->fromIri(
            'https://foo.bar/place/foo-bar'
        );
    }

    /**
     * @test
     */
    public function it_throws_an_error_when_the_offerid_index_is_not_found()
    {
        $iriOfferIdentifierFactory = new IriOfferIdentifierFactory(
            'https?://foo\.bar/(?<offertype>[event|place]+)/(?<id>[a-zA-Z0-9\-]+)'
        );

        $this->setExpectedException(
            \InvalidArgumentException::class,
            'Regular expression pattern should capture group named "offerid"'
        );

        $iriOfferIdentifierFactory->fromIri(
            'https://foo.bar/place/foo-bar'
        );
    }

    /**
     * @test
     */
    public function it_returns_a_correct_self_when_url_is_valid()
    {
        $iriOfferIdentifier = $this->iriOfferIdentifierFactory->fromIri(
            'https://foo.bar/place/1234'
        );

        $expectedIriOfferIdentifier = new IriOfferIdentifier(
            'https://foo.bar/place/1234',
            '1234',
            OfferType::PLACE()
        );

        $this->assertEquals($expectedIriOfferIdentifier, $iriOfferIdentifier);
    }
}
