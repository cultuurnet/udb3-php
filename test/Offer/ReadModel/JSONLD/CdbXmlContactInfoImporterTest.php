<?php

namespace CultuurNet\UDB3\Offer\ReadModel\JSONLD;

class CdbXmlContactInfoImporterTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var CdbXmlContactInfoImporter
     */
    private $cdbXmlContactInfoImporter;

    /**
     * @var \CultureFeed_Cdb_Data_ContactInfo
     */
    private $cdbContactInfo;

    protected function setUp()
    {
        $this->cdbXmlContactInfoImporter = new CdbXmlContactInfoImporter();

        $this->cdbContactInfo = $this->createCdbContactInfo();
    }

    /**
     * @test
     */
    public function it_converts_contact_info_to_booking_info_json()
    {
        $jsonLd = new \StdClass();

        $this->cdbXmlContactInfoImporter->importBookingInfo(
            $jsonLd,
            $this->cdbContactInfo,
            null,
            null
        );

        $this->assertEquals('info@2dotstwice.be', $jsonLd->bookingInfo['email']);
        $this->assertEquals('987654321', $jsonLd->bookingInfo['phone']);
        $this->assertEquals('www.2dotstwice.be', $jsonLd->bookingInfo['url']);
        $this->assertEquals('Reserveer plaatsen', $jsonLd->bookingInfo['urlLabel']);
    }

    /**
     * @test
     */
    public function it_converts_price_to_booking_info_json()
    {
        $cdbPrice = new \CultureFeed_Cdb_Data_Price();
        $cdbPrice->setDescription('Prijs voor volwassen.');
        $cdbPrice->setTitle('Volwassen.');
        $cdbPrice->setValue(9.99);

        $cdbBookingPeriod = new \CultureFeed_Cdb_Data_Calendar_BookingPeriod(
            1483258210,
            1483464325
        );

        $jsonLd = new \StdClass();

        $this->cdbXmlContactInfoImporter->importBookingInfo(
            $jsonLd,
            new \CultureFeed_Cdb_Data_ContactInfo(),
            $cdbPrice,
            $cdbBookingPeriod
        );

        $this->assertEquals('Prijs voor volwassen.', $jsonLd->bookingInfo['description']);
        $this->assertEquals('Volwassen.', $jsonLd->bookingInfo['name']);
        $this->assertEquals(9.99, $jsonLd->bookingInfo['price']);
        $this->assertEquals('EUR', $jsonLd->bookingInfo['priceCurrency']);
        $this->assertEquals('2017-01-01T08:10:10+00:00', $jsonLd->bookingInfo['availabilityStarts']);
        $this->assertEquals('2017-01-03T17:25:25+00:00', $jsonLd->bookingInfo['availabilityEnds']);
    }

    /**
     * @test
     */
    public function it_converts_contact_info_to_contact_point_json()
    {
        $jsonLd = new \StdClass();

        $this->cdbXmlContactInfoImporter->importContactPoint(
            $jsonLd,
            $this->cdbContactInfo
        );

        $this->assertEquals(
            [
                'info@cultuurnet.be',
                'info@gmail.com',
            ],
            $jsonLd->contactPoint['email']
        );

        $this->assertEquals(
            [
                '89898989',
                '12121212'
            ],
            $jsonLd->contactPoint['phone']
        );

        $this->assertEquals(
            [
                'www.cultuurnet.be',
                'www.booking.com',
            ],
            $jsonLd->contactPoint['url']
        );
    }

    /**
     * @return \CultureFeed_Cdb_Data_ContactInfo
     */
    private function createCdbContactInfo()
    {
        $contactInfo = new \CultureFeed_Cdb_Data_ContactInfo();

        $contactInfo->addMail(
            new \CultureFeed_Cdb_Data_Mail(
                'info@cultuurnet.be',
                false,
                false
            )
        );
        $contactInfo->addMail(
            new \CultureFeed_Cdb_Data_Mail(
                'info@2dotstwice.be',
                false,
                true
            )
        );
        $contactInfo->addMail(
            new \CultureFeed_Cdb_Data_Mail(
                'info@gmail.com',
                false,
                false
            )
        );

        $contactInfo->addPhone(
            new \CultureFeed_Cdb_Data_Phone(
                '89898989',
                'mobile',
                false,
                false
            )
        );
        $contactInfo->addPhone(
            new \CultureFeed_Cdb_Data_Phone(
                '987654321',
                'mobile',
                false,
                true
            )
        );
        $contactInfo->addPhone(
            new \CultureFeed_Cdb_Data_Phone(
                '12121212',
                'phone',
                false,
                false
            )
        );

        $contactInfo->addUrl(
            new \CultureFeed_Cdb_Data_Url(
                'www.cultuurnet.be',
                false,
                false
            )
        );
        $contactInfo->addUrl(
            new \CultureFeed_Cdb_Data_Url(
                'www.2dotstwice.be',
                false,
                true
            )
        );
        $contactInfo->addUrl(
            new \CultureFeed_Cdb_Data_Url(
                'www.booking.com',
                false,
                false
            )
        );

        return $contactInfo;
    }
}
