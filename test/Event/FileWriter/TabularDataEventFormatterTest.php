<?php


namespace CultuurNet\UDB3\EventExport\FileWriter;

class TabularDataEventFormatterTest extends \PHPUnit_Framework_TestCase
{

    private function getJSONEventFromFile($fileName)
    {
        $jsonEvent = file_get_contents(
            __DIR__ . '/' . $fileName
        );

        return $jsonEvent;
    }

    /**
     * @test
     */
    public function it_excludes_all_terms_when_none_are_included()
    {
        $includedProperties = [
            'id',
        ];
        $eventWithTerms = $this->getJSONEventFromFile('event_with_terms.json');
        $formatter = new TabularDataEventFormatter($includedProperties);

        $formattedEvent = $formatter->formatEvent($eventWithTerms);
        $formattedProperties = array_keys($formattedEvent);

        $this->assertEquals($includedProperties, $formattedProperties);
    }

    /**
     * @test
     */
    public function it_excludes_other_terms_when_some_are_included()
    {
        $includedProperties = [
            'id',
            'terms.eventtype'
        ];
        $eventWithTerms = $this->getJSONEventFromFile('event_with_terms.json');
        $formatter = new TabularDataEventFormatter($includedProperties);

        $formattedEvent = $formatter->formatEvent($eventWithTerms);
        $formattedProperties = array_keys($formattedEvent);

        $this->assertEquals($includedProperties, $formattedProperties);
    }

    /**
     * @test
     */
    public function it_formats_included_terms()
    {
        $includedProperties = [
            'id',
            'terms.eventtype',
            'terms.theme'
        ];
        $eventWithTerms = $this->getJSONEventFromFile('event_with_terms.json');
        $formatter = new TabularDataEventFormatter($includedProperties);

        $formattedEvent = $formatter->formatEvent($eventWithTerms);
        $expectedFormatting = array(
            "id" =>"d1f0e71d-a9a8-4069-81fb-530134502c58",
            "terms.eventtype" => "Cursus of workshop",
            "terms.theme" => "Geschiedenis"
        );

        $this->assertEquals($expectedFormatting, $formattedEvent);
    }

    /**
     * @test
     */
    public function it_formats_address_as_separate_columns()
    {
        $includedProperties = [
            'id',
            'address'
        ];
        $eventWithTerms = $this->getJSONEventFromFile('event_with_terms.json');
        $formatter = new TabularDataEventFormatter($includedProperties);

        $formattedEvent = $formatter->formatEvent($eventWithTerms);
        $expectedFormatting = array(
            "id" =>"d1f0e71d-a9a8-4069-81fb-530134502c58",
            "address.streetAddress" => "Sint-Jorisplein 20 ",
            "address.postalCode" => "3300",
            "address.addressLocality" => "Tienen",
            "address.addressCountry" => "BE"
        );

        $this->assertEquals($expectedFormatting, $formattedEvent);
    }

    /**
     * @test
     * @dataProvider eventDateProvider
     */
    public function it_formats_dates($eventFile, $created, $startDate, $endDate)
    {
        $expectedFormatting = [
            'created' => $created,
            'startDate' => $startDate,
            'endDate' => $endDate,
        ];

        $includedProperties = [
            'created',
            'startDate',
            'endDate'
        ];
        $event = $this->getJSONEventFromFile($eventFile);
        $formatter = new TabularDataEventFormatter($includedProperties);
        $formattedEvent = $formatter->formatEvent($event);

        // For some reason the ID is always included in the formatted event, even if we don't want it to be.
        unset($formattedEvent['id']);

        $this->assertEquals($expectedFormatting, $formattedEvent);
    }

    /**
     * Test data provider for it_formats_dates().
     *
     * @return array
     *   Array of individual arrays, each containing the arguments for the test method.
     */
    public function eventDateProvider()
    {
        return array(
            array('event_with_dates.json', '2014-12-11 17:30', '2015-03-02 13:30', '2015-03-30 16:30'),
            array('event_without_end_date.json', '2014-12-11 17:30', '2015-03-02 13:30', ''),
        );
    }
}
