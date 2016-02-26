<?php

namespace CultuurNet\UDB3\Offer;

use CultuurNet\UDB3\Label;
use CultuurNet\UDB3\LabelCollection;
use CultuurNet\UDB3\Media\Image;
use CultuurNet\UDB3\Media\Properties\MIMEType;
use CultuurNet\UDB3\Offer\Item\Item;
use PHPUnit_Framework_MockObject_MockObject;
use ValueObjects\Identity\UUID;
use ValueObjects\String\String as StringLiteral;
use ValueObjects\Web\Url;

class OfferTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Item
     */
    protected $offer;

    /**
     * @var LabelCollection
     */
    protected $labels;

    /**
     * @var Image
     */
    protected $image;

    public function setUp()
    {
        $this->offer = new Item('foo');
        $this->labels = (new LabelCollection())
            ->with(new Label('test'))
            ->with(new Label('label'))
            ->with(new Label('cultuurnet'));
        $this->image = new Image(
            new UUID('de305d54-75b4-431b-adb2-eb6b9e546014'),
            new MIMEType('image/gif'),
            new StringLiteral('my favorite giphy gif'),
            new StringLiteral('Bert Ramakers'),
            Url::fromNative('http://foo.bar/media/my_favorite_giphy_gif.gif')
        );
    }

    /**
     * @test
     */
    public function it_can_set_and_return_labels()
    {
        $this->offer->addLabel(new Label('test'));
        $this->offer->addLabel(new Label('label'));
        $this->offer->addLabel(new Label('cultuurnet'));
        $labels = $this->offer->getLabels();
        $expectedLabels = $this->labels;

        $this->assertEquals($expectedLabels, $labels);
    }

    /**
     * @test
     */
    public function it_can_delete_labels()
    {
        $this->offer->addLabel(new Label('test'));
        $this->offer->addLabel(new Label('label'));
        $this->offer->addLabel(new Label('cultuurnet'));

        $this->offer->deleteLabel(new Label('cultuurnet'));

        $expectedLabels = (new LabelCollection())
            ->with(new Label('test'))
            ->with(new Label('label'));

        $this->assertEquals($expectedLabels, $this->offer->getLabels());
    }

    /**
     * @test
     * @expectedException     \Exception
     */
    public function it_should_throw_an_exception_when_selecting_an_unknown_main_image()
    {
        $this->offer->selectMainImage($this->image);
    }

    /**
     * @test
     */
    public function it_should_set_the_main_image_when_selecting_another_one()
    {
        $anotherImage = new Image(
            new UUID('798b4619-07c4-456d-acca-8f3f3e6fd43f'),
            new MIMEType('image/jpeg'),
            new StringLiteral('my best selfie'),
            new StringLiteral('Dirk Dirkington'),
            Url::fromNative('http://foo.bar/media/my_best_selfie.gif')
        );
        $expectedImageId = new UUID('798b4619-07c4-456d-acca-8f3f3e6fd43f');
        $this->offer->addImage($this->image);
        $this->offer->addImage($anotherImage);

        $this->offer->selectMainImage($anotherImage);

        $this->assertEquals($expectedImageId, $this->offer->getMainImageId());
    }

    /**
     * @test
     */
    public function it_should_make_the_oldest_image_main_when_deleting_the_current_main_image()
    {
        $anotherImage = new Image(
            new UUID('798b4619-07c4-456d-acca-8f3f3e6fd43f'),
            new MIMEType('image/jpeg'),
            new StringLiteral('my best selfie'),
            new StringLiteral('Dirk Dirkington'),
            Url::fromNative('http://foo.bar/media/my_best_selfie.gif')
        );
        $expectedImageId = new UUID('798b4619-07c4-456d-acca-8f3f3e6fd43f');
        $this->offer->addImage($this->image);
        $this->offer->addImage($anotherImage);

        $this->offer->removeImage($this->image);

        $this->assertEquals($expectedImageId, $this->offer->getMainImageId());
    }

    /**
     * @test
     */
    public function it_should_make_an_image_main_when_added_to_an_item_without_existing_ones()
    {
        $expectedImageId = new UUID('de305d54-75b4-431b-adb2-eb6b9e546014');
        $this->offer->addImage($this->image);

        $this->assertEquals($expectedImageId, $this->offer->getMainImageId());
    }

    /**
     * @test
     */
    public function it_should_not_trigger_a_main_image_selected_event_when_the_image_is_already_selected_as_main()
    {
        /** @var Item|PHPUnit_Framework_MockObject_MockObject $offer */
        $offer = $this->getMock(
            Item::class,
            ['applyMainImageSelected'],
            ['foo']
        );

        $offer->expects($this->never())
            ->method('applyMainImageSelected');

        $offer->addImage($this->image);

        $offer->selectMainImage($this->image);
    }
}
