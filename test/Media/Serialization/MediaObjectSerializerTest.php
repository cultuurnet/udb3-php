<?php

namespace CultuurNet\UDB3\Media\Serialization;

use CultuurNet\UDB3\Iri\IriGeneratorInterface;
use CultuurNet\UDB3\Media\Image;
use CultuurNet\UDB3\Media\MediaObject;
use CultuurNet\UDB3\Media\Properties\CopyrightHolder;
use CultuurNet\UDB3\Media\Properties\Description;
use CultuurNet\UDB3\Media\Properties\MIMEType;
use Symfony\Component\Serializer\Exception\UnsupportedException;
use Symfony\Component\Serializer\SerializerInterface;
use ValueObjects\Identity\UUID;
use ValueObjects\String\String;
use ValueObjects\Web\Url;

class MediaObjectSerializerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|SerializerInterface
     */
    protected $serializer;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|IriGeneratorInterface
     */
    protected $iriGenerator;

    public function setUp()
    {
        $this->iriGenerator = $this->createMock(IriGeneratorInterface::class);
        $this->serializer = new MediaObjectSerializer($this->iriGenerator);
    }

    /**
     * @test
     */
    public function it_adds_schema_annotations_when_serializing_a_media_object_to_jsonld()
    {
        $mediaObject = new Image(
            new UUID('de305d54-75b4-431b-adb2-eb6b9e546014'),
            new MIMEType('image/jpg'),
            new Description('my pic'),
            new CopyrightHolder('Dirk Dirkington'),
            Url::fromNative('http://foo.bar/media/my_pic.jpg')
        );

        $this->iriGenerator
            ->expects($this->once())
            ->method('iri')
            ->willReturn('http://foo.bar/media/de305d54-75b4-431b-adb2-eb6b9e546014');

        $expectedJsonld = [
            '@id' => 'http://foo.bar/media/de305d54-75b4-431b-adb2-eb6b9e546014',
            '@type' => 'schema:ImageObject',
            'thumbnailUrl' => 'http://foo.bar/media/my_pic.jpg',
            'contentUrl' => 'http://foo.bar/media/my_pic.jpg',
            'description' => 'my pic',
            'copyrightHolder' => 'Dirk Dirkington'
        ];

        $jsonld = $this->serializer->serialize($mediaObject, 'json-ld');

        $this->assertEquals($expectedJsonld, $jsonld);
    }

    /**
     * @test
     */
    public function it_should_serialize_media_objects_with_application_octet_stream_mime_type()
    {
        $mediaObject = new Image(
            new UUID('de305d54-75b4-431b-adb2-eb6b9e546014'),
            new MIMEType('application/octet-stream'),
            new Description('my pic'),
            new CopyrightHolder('Dirk Dirkington'),
            Url::fromNative('http://foo.bar/media/my_pic.jpg')
        );

        $this->iriGenerator
            ->expects($this->once())
            ->method('iri')
            ->willReturn('http://foo.bar/media/de305d54-75b4-431b-adb2-eb6b9e546014');

        $expectedJsonld = [
            '@id' => 'http://foo.bar/media/de305d54-75b4-431b-adb2-eb6b9e546014',
            '@type' => 'schema:mediaObject',
            'thumbnailUrl' => 'http://foo.bar/media/my_pic.jpg',
            'contentUrl' => 'http://foo.bar/media/my_pic.jpg',
            'description' => 'my pic',
            'copyrightHolder' => 'Dirk Dirkington'
        ];

        $jsonld = $this->serializer->serialize($mediaObject, 'json-ld');

        $this->assertEquals($expectedJsonld, $jsonld);
    }

    /**
     * @test
     */
    public function it_should_throw_an_exception_when_trying_to_serialize_unknown_media_types()
    {
        $mediaObject = MediaObject::create(
            new UUID('de305d54-75b4-431b-adb2-eb6b9e546014'),
            new MIMEType('video/avi'),
            new Description('sexy ladies without clothes'),
            new CopyrightHolder('Bart Ramakers'),
            Url::fromNative('http://foo.bar/media/de305d54-75b4-431b-adb2-eb6b9e546014.png')
        );

        $this->setExpectedException(
            UnsupportedException::class,
            'Unsupported MIME-type "video/avi"'
        );

        $this->serializer->serialize($mediaObject, 'json-ld');
    }

    /**
     * @test
     */
    public function it_should_throw_an_exception_when_trying_to_serialize_to_an_unknown_format()
    {
        $mediaObject = MediaObject::create(
            new UUID('de305d54-75b4-431b-adb2-eb6b9e546014'),
            new MIMEType('video/avi'),
            new Description('sexy ladies without clothes'),
            new CopyrightHolder('Bart Ramakers'),
            Url::fromNative('http://foo.bar/media/de305d54-75b4-431b-adb2-eb6b9e546014.png')
        );

        $this->setExpectedException(
            UnsupportedException::class,
            'Unsupported format, only json-ld is available.'
        );

        $this->serializer->serialize($mediaObject, 'xml');
    }

    /**
     * @test
     */
    public function it_should_throw_an_exception_when_trying_to_deserialize()
    {
        $this->setExpectedException(
            \Exception::class,
            'Deserialization currently not supported.'
        );

        $this->serializer->deserialize((object) [], MediaObject::class, 'json-ld');
    }
}
