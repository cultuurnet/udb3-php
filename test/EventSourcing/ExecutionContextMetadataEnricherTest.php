<?php
/**
 * @file
 */

namespace CultuurNet\UDB3\EventSourcing;


use Broadway\Domain\Metadata;

class ExecutionContextMetadataEnricherTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ExecutionContextMetadataEnricher
     */
    protected $enricher;

    public function setUp()
    {
        $this->enricher = new ExecutionContextMetadataEnricher();
    }

    /**
     * @test
     */
    public function it_is_a_metadata_enricher()
    {
        $this->assertInstanceOf(
            'Broadway\\EventSourcing\\MetadataEnrichment\\MetadataEnricherInterface',
            $this->enricher
        );
    }

    /**
     * @test
     */
    public function it_is_context_aware()
    {
        $this->assertInstanceOf(
            'CultuurNet\\UDB3\\CommandHandling\\ContextAwareInterface',
            $this->enricher
        );
    }

    /**
     * @test
     */
    public function it_adds_the_execution_context_to_metadata()
    {
        $context = new Metadata(
            [
                'user_id' => 1,
                'user_nick' => 'admin'
            ]
        );

        $this->enricher->setContext($context);

        $metadata = new Metadata(
            ['foo' => 'bar']
        );

        $enrichedMetadata = $this->enricher->enrich($metadata);

        $this->assertEquals(
            [
                'foo' => 'bar',
                'user_id' => 1,
                'user_nick' => 'admin'
            ],
            $enrichedMetadata->serialize()
        );
    }

} 
