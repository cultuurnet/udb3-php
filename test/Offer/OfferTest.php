<?php

namespace CultuurNet\UDB3\Offer;

use Broadway\EventSourcing\Testing\AggregateRootScenarioTestCase;
use CultuurNet\UDB3\Label;
use CultuurNet\UDB3\LabelCollection;
use CultuurNet\UDB3\Media\Image;
use CultuurNet\UDB3\Media\Properties\MIMEType;
use CultuurNet\UDB3\Offer\Item\Events\ImageAdded;
use CultuurNet\UDB3\Offer\Item\Events\ImageRemoved;
use CultuurNet\UDB3\Offer\Item\Events\ItemCreated;
use CultuurNet\UDB3\Offer\Item\Events\MainImageSelected;
use CultuurNet\UDB3\Offer\Item\Events\Moderation\Approved;
use CultuurNet\UDB3\Offer\Item\Events\Moderation\FlaggedAsDuplicate;
use CultuurNet\UDB3\Offer\Item\Events\Moderation\FlaggedAsInappropriate;
use CultuurNet\UDB3\Offer\Item\Events\Moderation\Published;
use CultuurNet\UDB3\Offer\Item\Events\Moderation\Rejected;
use CultuurNet\UDB3\Offer\Item\Item;
use Exception;
use ValueObjects\Identity\UUID;
use ValueObjects\String\String as StringLiteral;
use ValueObjects\Web\Url;

class OfferTest extends AggregateRootScenarioTestCase
{
    /**
     * @inheritdoc
     */
    protected function getAggregateRootClass()
    {
        return Item::class;
    }

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
        parent::setUp();

        $this->offer = new Item();
        $this->offer->apply(new ItemCreated('foo'));

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
     * @expectedException     Exception
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
        $image = $this->image;

        $this->scenario
            ->withAggregateId('someId')
            ->given(
                [
                    new ItemCreated('someId')
                ]
            )
            ->when(
                function (Item $item) use ($image, $anotherImage) {
                    $item->addImage($image);
                    $item->addImage($anotherImage);
                    $item->selectMainImage($anotherImage);
                }
            )
            ->then(
                [
                    new ImageAdded('someId', $image),
                    new ImageAdded('someId', $anotherImage),
                    new MainImageSelected('someId', $anotherImage)
                ]
            );
    }

    /**
     * @test
     */
    public function it_should_make_the_oldest_image_main_when_deleting_the_current_main_image()
    {
        $oldestImage = new Image(
            new UUID('798b4619-07c4-456d-acca-8f3f3e6fd43f'),
            new MIMEType('image/gif'),
            new StringLiteral('my best selfie'),
            new StringLiteral('Dirk Dirkington'),
            Url::fromNative('http://foo.bar/media/my_best_selfie.gif')
        );
        $newerImage = new Image(
            new UUID('fdfac613-61f9-43ac-b1a9-c75f9fd58386'),
            new MIMEType('image/jpeg'),
            new StringLiteral('pic'),
            new StringLiteral('Henk'),
            Url::fromNative('http://foo.bar/media/pic.jpeg')
        );
        $originalMainImage = $this->image;

        $this->scenario
            ->withAggregateId('someId')
            ->given(
                [
                    new ItemCreated('someId')
                ]
            )
            ->when(
                function (Item $item) use ($originalMainImage, $oldestImage, $newerImage) {
                    $item->addImage($originalMainImage);
                    $item->addImage($oldestImage);
                    $item->addImage($newerImage);
                    $item->removeImage($originalMainImage);
                    // When you attempt to make the oldest image main no event should be triggered
                    $item->selectMainImage($oldestImage);
                }
            )
            ->then(
                [
                    new ImageAdded('someId', $originalMainImage),
                    new ImageAdded('someId', $oldestImage),
                    new ImageAdded('someId', $newerImage),
                    new ImageRemoved('someId', $originalMainImage)
                ]
            );
    }

    /**
     * @test
     */
    public function it_should_make_an_image_main_when_added_to_an_item_without_existing_ones()
    {
        $firstImage = $this->image;

        $this->scenario
            ->withAggregateId('someId')
            ->given(
                [
                    new ItemCreated('someId')
                ]
            )
            ->when(
                function (Item $item) use ($firstImage) {
                    $item->addImage($firstImage);
                    // If no event fires when selecting an image as main, it is already set.
                    $item->selectMainImage($firstImage);
                }
            )
            ->then(
                [
                    new ImageAdded('someId', $firstImage),
                ]
            );
    }

    /**
     * @test
     */
    public function it_should_not_trigger_a_main_image_selected_event_when_the_image_is_already_selected_as_main()
    {
        $originalMainImage = $this->image;
        $newMainImage = new Image(
            new UUID('fdfac613-61f9-43ac-b1a9-c75f9fd58386'),
            new MIMEType('image/jpeg'),
            new StringLiteral('pic'),
            new StringLiteral('Henk'),
            Url::fromNative('http://foo.bar/media/pic.jpeg')
        );

        $this->scenario
            ->withAggregateId('someId')
            ->given(
                [
                    new ItemCreated('someId')
                ]
            )
            ->when(
                function (Item $item) use ($originalMainImage, $newMainImage) {
                    $item->addImage($originalMainImage);
                    $item->addImage($newMainImage);
                    $item->selectMainImage($newMainImage);
                    // When you attempt to make the current main image main, no events should trigger
                    $item->selectMainImage($newMainImage);
                }
            )
            ->then(
                [
                    new ImageAdded('someId', $originalMainImage),
                    new ImageAdded('someId', $newMainImage),
                    new MainImageSelected('someId', $newMainImage),
                ]
            );
    }

    /**
     * @test
     */
    public function it_publishes_an_offer_with_workflow_status_draft()
    {
        $itemId = 'itemId';

        $this->scenario
            ->given([
                new ItemCreated($itemId)
            ])
            ->when(function (Item $item) {
                $item->publish();
            })
            ->then([
                new Published($itemId)
            ]);
    }

    /**
     * @test
     */
    public function it_does_not_publish_an_offer_more_then_once()
    {
        $itemId = 'itemId';

        $this->scenario
            ->given([
                new ItemCreated($itemId),
                new Published($itemId)
            ])
            ->when(function (Item $item) {
                $item->publish();
            })
            ->then([]);
    }

    /**
     * @test
     */
    public function it_throws_when_trying_to_publish_a_non_draft_offer()
    {
        $this->setExpectedException(
            Exception::class,
            'You can not publish an offer that is not draft'
        );

        $itemId = 'itemId';

        $this->scenario
            ->given([
                new ItemCreated($itemId),
                new Published($itemId),
                new FlaggedAsDuplicate($itemId)
            ])
            ->when(function (Item $item) {
                $item->publish();
            })
            ->then([]);
    }

    /**
     * @test
     */
    public function it_should_approve_an_offer_that_is_ready_for_validation()
    {
        $itemId = UUID::generateAsString();

        $this->scenario
            ->withAggregateId($itemId)
            ->given(
                [
                    new ItemCreated($itemId),
                    new Published($itemId)
                ]
            )
            ->when(
                function (Item $item) {
                    $item->approve();
                }
            )
            ->then(
                [
                    new Approved($itemId)
                ]
            );
    }

    /**
     * @test
     */
    public function it_should_not_approve_an_offer_more_than_once()
    {
        $itemId = UUID::generateAsString();

        $this->scenario
            ->withAggregateId($itemId)
            ->given(
                [
                    new ItemCreated($itemId),
                    new Published($itemId)
                ]
            )
            ->when(
                function (Item $item) {
                    $item->approve();
                    $item->approve();
                }
            )
            ->then(
                [
                    new Approved($itemId)
                ]
            );
    }

    /**
     * @test
     * @expectedException        Exception
     * @expectedExceptionMessage You can not approve an offer that is not ready for validation
     */
    public function it_should_not_approve_an_offer_after_it_was_rejected()
    {
        $itemId = UUID::generateAsString();
        $reason = new StringLiteral('There are spelling mistakes in the description.');

        $this->scenario
            ->withAggregateId($itemId)
            ->given(
                [
                    new ItemCreated($itemId),
                    new Rejected($itemId, $reason)
                ]
            )
            ->when(
                function (Item $item) use ($reason) {
                    $item->approve();
                }
            )
            ->then([]);
    }

    /**
     * @test
     */
    public function it_should_not_reject_an_offer_more_than_once_for_the_same_reason()
    {
        $itemId = UUID::generateAsString();
        $reason = new StringLiteral('The title is misleading.');

        $this->scenario
            ->withAggregateId($itemId)
            ->given(
                [
                    new ItemCreated($itemId),
                    new Published($itemId)
                ]
            )
            ->when(
                function (Item $item) use ($reason) {
                    $item->reject($reason);
                    $item->reject($reason);
                }
            )
            ->then(
                [
                    new Rejected($itemId, $reason)
                ]
            );
    }

    /**
     * @test
     * @expectedException        Exception
     * @expectedExceptionMessage The offer has already been rejected for another reason: The title is misleading.
     */
    public function it_should_not_reject_an_offer_that_is_already_rejected_for_a_different_reason()
    {
        $itemId = UUID::generateAsString();
        $reason = new StringLiteral('The title is misleading.');
        $differentReason = new StringLiteral('I\'m afraid I can\'t let you do that.');

        $this->scenario
            ->withAggregateId($itemId)
            ->given(
                [
                    new ItemCreated($itemId),
                    new Rejected($itemId, $reason)
                ]
            )
            ->when(
                function (Item $item) use ($differentReason) {
                    $item->reject($differentReason);
                }
            )
            ->then([]);
    }

    /**
     * @test
     */
    public function it_should_reject_an_offer_that_is_ready_for_validation_with_a_reason()
    {
        $itemId = UUID::generateAsString();
        $reason = new StringLiteral('You forgot to add an organizer.');

        $this->scenario
            ->withAggregateId($itemId)
            ->given(
                [
                    new ItemCreated($itemId),
                    new Published($itemId)
                ]
            )
            ->when(
                function (Item $item) use ($reason) {
                    $item->reject($reason);
                }
            )
            ->then(
                [
                    new Rejected($itemId, $reason)
                ]
            );
    }

    /**
     * @test
     */
    public function it_should_flag_an_offer_that_is_ready_for_validation_as_duplicate()
    {
        $itemId = UUID::generateAsString();

        $this->scenario
            ->withAggregateId($itemId)
            ->given(
                [
                    new ItemCreated($itemId),
                    new Published($itemId)
                ]
            )
            ->when(
                function (Item $item) {
                    $item->flagAsDuplicate();
                }
            )
            ->then(
                [
                    new FlaggedAsDuplicate($itemId)
                ]
            );
    }

    /**
     * @test
     * @expectedException        Exception
     * @expectedExceptionMessage The offer has already been rejected for another reason: duplicate
     */
    public function it_should_reject_an_offer_when_it_is_flagged_as_duplicate()
    {
        $itemId = UUID::generateAsString();
        $reason = new StringLiteral('The theme does not match the description.');

        $this->scenario
            ->withAggregateId($itemId)
            ->given(
                [
                    new ItemCreated($itemId),
                    new FlaggedAsDuplicate($itemId)
                ]
            )
            ->when(
                function (Item $item) use ($reason) {
                    $item->reject($reason);
                }
            )
            ->then([]);
    }

    /**
     * @test
     */
    public function it_should_flag_an_offer_that_is_ready_for_validation_as_inappropriate()
    {
        $itemId = UUID::generateAsString();

        $this->scenario
            ->withAggregateId($itemId)
            ->given(
                [
                    new ItemCreated($itemId),
                    new Published($itemId)
                ]
            )
            ->when(
                function (Item $item) {
                    $item->flagAsInappropriate();
                }
            )
            ->then(
                [
                    new FlaggedAsInappropriate($itemId)
                ]
            );
    }

    /**
     * @test
     * @expectedException        Exception
     * @expectedExceptionMessage The offer has already been rejected for another reason: inappropriate
     */
    public function it_should_not_reject_an_offer_when_it_is_flagged_as_inappropriate()
    {
        $itemId = UUID::generateAsString();
        $reason = new StringLiteral('The theme does not match the description.');

        $this->scenario
            ->withAggregateId($itemId)
            ->given(
                [
                    new ItemCreated($itemId),
                    new FlaggedAsInappropriate($itemId)
                ]
            )
            ->when(
                function (Item $item) use ($reason) {
                    $item->reject($reason);
                }
            )
            ->then([]);
    }

    /**
     * @test
     * @expectedException        Exception
     * @expectedExceptionMessage You can not reject an offer that is not ready for validation
     */
    public function it_should_not_reject_an_offer_that_is_flagged_as_approved()
    {
        $itemId = UUID::generateAsString();
        $reason = new StringLiteral('Yeah, but no, but yeah...');

        $this->scenario
            ->withAggregateId($itemId)
            ->given(
                [
                    new ItemCreated($itemId),
                    new Approved($itemId)
                ]
            )
            ->when(
                function (Item $item) use ($reason) {
                    $item->reject($reason);
                }
            )
            ->then([]);
    }
}
