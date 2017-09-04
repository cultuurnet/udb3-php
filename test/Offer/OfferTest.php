<?php

namespace CultuurNet\UDB3\Offer;

use Broadway\EventSourcing\Testing\AggregateRootScenarioTestCase;
use CultuurNet\UDB3\Calendar;
use CultuurNet\UDB3\CalendarType;
use CultuurNet\UDB3\Label;
use CultuurNet\UDB3\LabelCollection;
use CultuurNet\UDB3\Language;
use CultuurNet\UDB3\Media\Image;
use CultuurNet\UDB3\Media\ImageCollection;
use CultuurNet\UDB3\Media\Properties\CopyrightHolder;
use CultuurNet\UDB3\Media\Properties\Description;
use CultuurNet\UDB3\Media\Properties\MIMEType;
use CultuurNet\UDB3\Offer\Item\Events\CalendarUpdated;
use CultuurNet\UDB3\Offer\Item\Events\DescriptionTranslated;
use CultuurNet\UDB3\Offer\Item\Events\DescriptionUpdated;
use CultuurNet\UDB3\Offer\Item\Events\ImageUpdated;
use CultuurNet\UDB3\Offer\Item\Events\TitleTranslated;
use CultuurNet\UDB3\Offer\Item\Events\TitleUpdated;
use CultuurNet\UDB3\Offer\Item\Commands\UpdateImage;
use CultuurNet\UDB3\Offer\Item\Events\Image\ImagesImportedFromUDB2;
use CultuurNet\UDB3\Offer\Item\Events\Image\ImagesUpdatedFromUDB2;
use CultuurNet\UDB3\Offer\Item\Events\ImageAdded;
use CultuurNet\UDB3\Offer\Item\Events\ImageRemoved;
use CultuurNet\UDB3\Offer\Item\Events\ItemCreated;
use CultuurNet\UDB3\Offer\Item\Events\LabelAdded;
use CultuurNet\UDB3\Offer\Item\Events\LabelRemoved;
use CultuurNet\UDB3\Offer\Item\Events\MainImageSelected;
use CultuurNet\UDB3\Offer\Item\Events\Moderation\Approved;
use CultuurNet\UDB3\Offer\Item\Events\Moderation\FlaggedAsDuplicate;
use CultuurNet\UDB3\Offer\Item\Events\Moderation\FlaggedAsInappropriate;
use CultuurNet\UDB3\Offer\Item\Events\Moderation\Published;
use CultuurNet\UDB3\Offer\Item\Events\Moderation\Rejected;
use CultuurNet\UDB3\Offer\Item\Events\OrganizerDeleted;
use CultuurNet\UDB3\Offer\Item\Events\OrganizerUpdated;
use CultuurNet\UDB3\Offer\Item\Item;
use CultuurNet\UDB3\Title;
use Exception;
use ValueObjects\Identity\UUID;
use ValueObjects\StringLiteral\StringLiteral;
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
            new Description('my favorite giphy gif'),
            new CopyrightHolder('Bert Ramakers'),
            Url::fromNative('http://foo.bar/media/my_favorite_giphy_gif.gif'),
            new Language('en')
        );
    }

    /**
     * @test
     */
    public function it_should_remember_added_labels()
    {
        $itemId = UUID::generateAsString();

        $this->scenario
            ->given([
                new ItemCreated($itemId),
            ])
            ->when(
                function (Item $item) {
                    $item->addLabel(new Label('purple'));
                    $item->addLabel(new Label('orange'));
                    $item->addLabel(new Label('green'));

                    $item->addLabel(new Label('purple'));
                    $item->addLabel(new Label('orange'));
                    $item->addLabel(new Label('green'));
                }
            )
            ->then([
                new LabelAdded($itemId, new Label('purple')),
                new LabelAdded($itemId, new Label('orange')),
                new LabelAdded($itemId, new Label('green')),
            ]);
    }

    /**
     * @test
     */
    public function it_should_remember_which_labels_were_removed()
    {
        $itemId = UUID::generateAsString();

        $this->scenario
            ->given([
                new ItemCreated($itemId),
            ])
            ->when(
                function (Item $item) {
                    $item->addLabel(new Label('purple'));
                    $item->addLabel(new Label('orange'));
                    $item->addLabel(new Label('green'));

                    $item->removeLabel(new Label('purple'));
                    $item->addLabel(new Label('purple'));
                }
            )
            ->then([
                new LabelAdded($itemId, new Label('purple')),
                new LabelAdded($itemId, new Label('orange')),
                new LabelAdded($itemId, new Label('green')),
                new LabelRemoved($itemId, new Label('purple')),
                new LabelAdded($itemId, new Label('purple')),
            ]);
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
            new Description('my best selfie'),
            new CopyrightHolder('Dirk Dirkington'),
            Url::fromNative('http://foo.bar/media/my_best_selfie.gif'),
            new Language('en')
        );
        $image = $this->image;

        $this->scenario
            ->withAggregateId('someId')
            ->given(
                [
                    new ItemCreated('someId'),
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
                    new MainImageSelected('someId', $anotherImage),
                ]
            );
    }

    /**
     * @test
     */
    public function it_checks_for_presence_of_image_when_updating()
    {
        $updateImage = new UpdateImage(
            'someId',
            new UUID($this->image->getMediaObjectId()),
            new Description('my favorite cat'),
            new CopyrightHolder('Jane Doe')
        );

        $this->scenario
            ->withAggregateId('someId')
            ->given(
                [
                    new ItemCreated('someId'),
                ]
            )
            ->when(
                function (Item $item) use ($updateImage) {
                    $item->addImage($this->image);
                    $item->removeImage($this->image);
                    $item->updateImage($updateImage);
                    $item->addImage($this->image);
                    $item->updateImage($updateImage);
                }
            )
            ->then(
                [
                    new ImageAdded('someId', $this->image),
                    new ImageRemoved('someId', $this->image),
                    new ImageAdded('someId', $this->image),
                    new ImageUpdated(
                        'someId',
                        $this->image->getMediaObjectId(),
                        new Description('my favorite cat'),
                        new CopyrightHolder('Jane Doe')
                    )
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
            new Description('my best selfie'),
            new CopyrightHolder('Dirk Dirkington'),
            Url::fromNative('http://foo.bar/media/my_best_selfie.gif'),
            new Language('en')
        );
        $newerImage = new Image(
            new UUID('fdfac613-61f9-43ac-b1a9-c75f9fd58386'),
            new MIMEType('image/jpeg'),
            new Description('pic'),
            new CopyrightHolder('Henk'),
            Url::fromNative('http://foo.bar/media/pic.jpeg'),
            new Language('en')
        );
        $originalMainImage = $this->image;

        $this->scenario
            ->withAggregateId('someId')
            ->given(
                [
                    new ItemCreated('someId'),
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
                    new ImageRemoved('someId', $originalMainImage),
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
                    new ItemCreated('someId'),
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
            new Description('pic'),
            new CopyrightHolder('Henk'),
            Url::fromNative('http://foo.bar/media/pic.jpeg'),
            new Language('en')
        );

        $this->scenario
            ->withAggregateId('someId')
            ->given(
                [
                    new ItemCreated('someId'),
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
        $now = new \DateTime();

        $this->scenario
            ->given([
                new ItemCreated($itemId),
            ])
            ->when(function (Item $item) use ($now) {
                $item->publish($now);
            })
            ->then([
                new Published($itemId, $now),
            ]);
    }

    /**
     * @test
     */
    public function it_does_not_publish_an_offer_more_then_once()
    {
        $itemId = 'itemId';
        $now = new \DateTime();

        $this->scenario
            ->given([
                new ItemCreated($itemId),
                new Published($itemId, $now),
            ])
            ->when(function (Item $item) use ($now) {
                $item->publish($now);
            })
            ->then([]);
    }

    /**
     * @test
     */
    public function it_throws_when_trying_to_publish_a_non_draft_offer()
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('You can not publish an offer that is not draft');

        $itemId = 'itemId';
        $now = new \DateTime();

        $this->scenario
            ->given([
                new ItemCreated($itemId),
                new Published($itemId, $now),
                new FlaggedAsDuplicate($itemId),
            ])
            ->when(function (Item $item) use ($now) {
                $item->publish($now);
            })
            ->then([]);
    }

    /**
     * @test
     */
    public function it_should_approve_an_offer_that_is_ready_for_validation()
    {
        $itemId = UUID::generateAsString();
        $now = new \DateTime();

        $this->scenario
            ->withAggregateId($itemId)
            ->given(
                [
                    new ItemCreated($itemId),
                    new Published($itemId, $now),
                ]
            )
            ->when(
                function (Item $item) {
                    $item->approve();
                }
            )
            ->then(
                [
                    new Approved($itemId),
                ]
            );
    }

    /**
     * @test
     */
    public function it_should_not_approve_an_offer_more_than_once()
    {
        $itemId = UUID::generateAsString();
        $now = new \DateTime();

        $this->scenario
            ->withAggregateId($itemId)
            ->given(
                [
                    new ItemCreated($itemId),
                    new Published($itemId, $now),
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
                    new Approved($itemId),
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
                    new Rejected($itemId, $reason),
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
        $now = new \DateTime();

        $this->scenario
            ->withAggregateId($itemId)
            ->given(
                [
                    new ItemCreated($itemId),
                    new Published($itemId, $now),
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
                    new Rejected($itemId, $reason),
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
                    new Rejected($itemId, $reason),
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
        $now = new \DateTime();

        $this->scenario
            ->withAggregateId($itemId)
            ->given(
                [
                    new ItemCreated($itemId),
                    new Published($itemId, $now),
                ]
            )
            ->when(
                function (Item $item) use ($reason) {
                    $item->reject($reason);
                }
            )
            ->then(
                [
                    new Rejected($itemId, $reason),
                ]
            );
    }

    /**
     * @test
     */
    public function it_should_flag_an_offer_that_is_ready_for_validation_as_duplicate()
    {
        $itemId = UUID::generateAsString();
        $now = new \DateTime();

        $this->scenario
            ->withAggregateId($itemId)
            ->given(
                [
                    new ItemCreated($itemId),
                    new Published($itemId, $now),
                ]
            )
            ->when(
                function (Item $item) {
                    $item->flagAsDuplicate();
                }
            )
            ->then(
                [
                    new FlaggedAsDuplicate($itemId),
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
                    new FlaggedAsDuplicate($itemId),
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
        $now = new \DateTime();

        $this->scenario
            ->withAggregateId($itemId)
            ->given(
                [
                    new ItemCreated($itemId),
                    new Published($itemId, $now),
                ]
            )
            ->when(
                function (Item $item) {
                    $item->flagAsInappropriate();
                }
            )
            ->then(
                [
                    new FlaggedAsInappropriate($itemId),
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
                    new FlaggedAsInappropriate($itemId),
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
                    new Approved($itemId),
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
    public function it_should_not_update_an_offer_with_an_organizer_when_it_is_already_set()
    {
        $itemId = UUID::generateAsString();
        $organizerId = UUID::generateAsString();

        $this->scenario
            ->withAggregateId($itemId)
            ->given(
                [
                    new ItemCreated($itemId),
                    new OrganizerUpdated($itemId, $organizerId),
                ]
            )
            ->when(
                function (Item $item) use ($organizerId) {
                    $item->updateOrganizer($organizerId);
                }
            )
            ->then([]);
    }

    /**
     * @test
     */
    public function it_should_update_an_offer_with_the_same_organizer_after_removing_it()
    {
        $itemId = UUID::generateAsString();
        $organizerId = UUID::generateAsString();

        $this->scenario
            ->withAggregateId($itemId)
            ->given(
                [
                    new ItemCreated($itemId),
                    new OrganizerUpdated($itemId, $organizerId),
                    new OrganizerDeleted($itemId, $organizerId),
                ]
            )
            ->when(
                function (Item $item) use ($organizerId) {
                    $item->updateOrganizer($organizerId);
                }
            )
            ->then([new OrganizerUpdated($itemId, $organizerId)]);
    }

    /**
     * @test
     */
    public function it_should_ignore_a_title_update_that_does_not_change_the_existing_title()
    {
        $itemId = UUID::generateAsString();
        $title = new Title('Titel');

        $this->scenario
            ->withAggregateId($itemId)
            ->given(
                [
                    new ItemCreated($itemId),
                    new TitleUpdated($itemId, $title),
                ]
            )
            ->when(
                function (Item $item) use ($title) {
                    $item->updateTitle(new Language('nl'), $title);
                }
            )
            ->then([
                new TitleUpdated($itemId, $title),
            ]);
    }

    /**
     * @test
     */
    public function it_should_translate_the_title_when_updating_with_a_foreign_language()
    {
        $itemId = UUID::generateAsString();
        $title = new Title('The Title');
        $language = new Language('en');

        $this->scenario
            ->withAggregateId($itemId)
            ->given(
                [
                    new ItemCreated($itemId),
                    new TitleUpdated($itemId, new Title('Een titel')),
                ]
            )
            ->when(
                function (Item $item) use ($title, $language) {
                    $item->updateTitle($language, $title);
                }
            )
            ->then([
                new TitleTranslated($itemId, $language, $title),
            ]);
    }

    /**
     * @test
     */
    public function it_should_ignore_a_description_update_that_does_not_change_the_existing_descriptions()
    {
        $itemId = UUID::generateAsString();
        $description = new \CultuurNet\UDB3\Description('Een beschrijving');

        $this->scenario
            ->withAggregateId($itemId)
            ->given(
                [
                    new ItemCreated($itemId),
                    new DescriptionUpdated($itemId, (string) $description),
                ]
            )
            ->when(
                function (Item $item) use ($description) {
                    $item->updateDescription($description, new Language('nl'));
                }
            )
            ->then([]);
    }

    /**
     * @test
     */
    public function it_should_translate_the_description_when_updating_with_a_foreign_language()
    {
        $itemId = UUID::generateAsString();
        $description = new \CultuurNet\UDB3\Description('La description');
        $language = new Language('fr');

        $this->scenario
            ->withAggregateId($itemId)
            ->given(
                [
                    new ItemCreated($itemId),
                    new DescriptionUpdated($itemId, 'Een beschrijving'),
                ]
            )
            ->when(
                function (Item $item) use ($description, $language) {
                    $item->updateDescription($description, $language);
                }
            )
            ->then([
                new DescriptionTranslated($itemId, $language, $description),
            ]);
    }

    /**
     * @test
     */
    public function it_handles_calendar_updated_events()
    {
        $itemId = '0f4ea9ad-3681-4f3b-adc2-4b8b00dd845a';

        $calendar = new Calendar(
            CalendarType::SINGLE(),
            \DateTime::createFromFormat(\DateTime::ATOM, '2020-01-26T11:11:11+01:00'),
            \DateTime::createFromFormat(\DateTime::ATOM, '2020-01-27T12:12:12+01:00')
        );

        $this->scenario
            ->withAggregateId($itemId)
            ->given(
                [
                    new ItemCreated($itemId),
                ]
            )
            ->when(
                function (Item $item) use ($calendar) {
                    $item->updateCalendar($calendar);
                }
            )
            ->then(
                [
                    new CalendarUpdated($itemId, $calendar),
                ]
            );
    }

    /**
     * @test
     * @dataProvider imageCollectionDataProvider
     * @param Image $image
     * @param ImageCollection $imageCollection
     */
    public function it_should_import_images_from_udb2_as_media_object_and_main_image(
        Image $image,
        ImageCollection $imageCollection
    ) {
        $itemId = UUID::generateAsString();

        $this->scenario
            ->withAggregateId($itemId)
            ->given([
                new ItemCreated($itemId),
            ])
            ->when(function (Item $item) use ($image, $imageCollection) {
                $item->importImagesFromUDB2($imageCollection);
                $item->addImage($image);
                $item->selectMainImage($image);
            })
            ->then([new ImagesImportedFromUDB2($itemId, $imageCollection)]);
    }

    /**
     * @test
     * @dataProvider imageCollectionDataProvider
     * @param Image $image
     */
    public function it_should_keep_images_translated_in_ubd3_when_updating_images_from_udb2(
        Image $image
    ) {
        $itemId = UUID::generateAsString();

        $dutchUdb3Image = new Image(
            new UUID('0773EB2A-54BE-49AD-B261-5D1099F319D4'),
            new MIMEType('image/jpg'),
            new Description('mijn favoriete wallpaper'),
            new CopyrightHolder('Dirk Dirkingn'),
            Url::fromNative('http://foo.bar/media/mijn_favoriete_wallpaper_<3.jpg'),
            new Language('nl')
        );

        $udb2Images = ImageCollection::fromArray([
            new Image(
                new UUID('de305d54-75b4-431b-adb2-eb6b9e546014'),
                new MIMEType('image/jpg'),
                new Description('episch panorama'),
                new CopyrightHolder('Dirk Dirkingn'),
                Url::fromNative('http://foo.bar/media/episch_panorama.jpg'),
                new Language('nl')
            ),
        ]);

        $this->scenario
            ->withAggregateId($itemId)
            ->given([
                new ItemCreated($itemId),
            ])
            ->when(function (Item $item) use ($image, $dutchUdb3Image, $udb2Images) {
                $item->addImage($image);
                $item->addImage($dutchUdb3Image);
                $item->importImagesFromUDB2($udb2Images);
                $item->addImage($image);
                $item->addImage($dutchUdb3Image);
            })
            ->then([
                new ImageAdded($itemId, $image),
                new ImageAdded($itemId, $dutchUdb3Image),
                new ImagesImportedFromUDB2($itemId, $udb2Images),
                new ImageAdded($itemId, $dutchUdb3Image),
            ]);
    }

    /**
     * @test
     * @dataProvider imageCollectionDataProvider
     * @param Image $image
     * @param ImageCollection $imageCollection
     */
    public function it_should_update_images_from_udb2_as_media_object_and_main_image(
        Image $image,
        ImageCollection $imageCollection
    ) {
        $itemId = UUID::generateAsString();

        $this->scenario
            ->withAggregateId($itemId)
            ->given([
                new ItemCreated($itemId),
            ])
            ->when(function (Item $item) use ($image, $imageCollection) {
                $item->UpdateImagesFromUDB2($imageCollection);
                $item->addImage($image);
                $item->selectMainImage($image);
            })
            ->then([new ImagesUpdatedFromUDB2($itemId, $imageCollection)]);
    }

    public function imageCollectionDataProvider()
    {
        $image = new Image(
            new UUID('de305d54-75b4-431b-adb2-eb6b9e546014'),
            new MIMEType('image/jpg'),
            new Description('my pic'),
            new CopyrightHolder('Dirk Dirkingn'),
            Url::fromNative('http://foo.bar/media/my_pic.jpg'),
            new Language('en')
        );

        return [
            'single image' => [
                'mainImage' => $image,
                'imageCollection' => ImageCollection::fromArray([$image]),
            ],
        ];
    }
}
