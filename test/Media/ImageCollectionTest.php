<?php

namespace CultuurNet\UDB3\Media;

use CultuurNet\UDB3\Language;
use CultuurNet\UDB3\Media\Properties\CopyrightHolder;
use CultuurNet\UDB3\Media\Properties\Description;
use CultuurNet\UDB3\Media\Properties\MIMEType;
use ValueObjects\Identity\UUID;
use ValueObjects\StringLiteral\StringLiteral;
use ValueObjects\Web\Url;

class ImageCollectionTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function it_should_remember_the_main_image()
    {
        $mainImage = new Image(
            new UUID(),
            MIMEType::fromSubtype('jpeg'),
            new Description('my best selfie'),
            new CopyrightHolder('Henk'),
            Url::fromNative('http://du.de/images/henk_032.jpg'),
            new Language('en')
        );
        $images = (new ImageCollection())->withMain($mainImage);

        $this->assertEquals($mainImage, $images->getMain());
    }

    /**
     * @test
     */
    public function it_should_not_contain_items_that_are_not_images()
    {
        $notImages = [
            'please, add me to your collection',
            new UUID(),
            MIMEType::fromSubtype('jpeg'),
            new StringLiteral('my best selfie'),
            Url::fromNative('http://du.de/images/henk_032.jpg'),
        ];

        $images = new ImageCollection($notImages);

        $this->assertEquals(0, $images->length());
    }

    /**
     * @test
     */
    public function it_should_return_the_first_image_as_main_when_set_explicitly()
    {
        $image = new Image(
            new UUID(),
            MIMEType::fromSubtype('jpeg'),
            new Description('my best selfie'),
            new CopyrightHolder('Henk'),
            Url::fromNative('http://du.de/images/henk_032.jpg'),
            new Language('en')
        );
        $images = (new ImageCollection())->with($image);

        $this->assertEquals($image, $images->getMain());
    }

    /**
     * @test
     */
    public function it_should_return_a_main_image_when_empty()
    {
        $this->assertEquals(null, (new ImageCollection())->getMain());
    }
}
