<?php

namespace CultuurNet\UDB3\Offer;

use Broadway\CommandHandling\Testing\CommandHandlerScenarioTestCase;
use Broadway\EventHandling\EventBusInterface;
use Broadway\EventStore\EventStoreInterface;
use Broadway\Repository\RepositoryInterface;
use CultuurNet\UDB3\Label;
use CultuurNet\UDB3\Label\ReadModels\JSON\Repository\Entity;
use CultuurNet\UDB3\Label\ReadModels\JSON\Repository\ReadRepositoryInterface;
use CultuurNet\UDB3\Label\ValueObjects\Privacy;
use CultuurNet\UDB3\Label\ValueObjects\Visibility;
use CultuurNet\UDB3\Language;
use CultuurNet\UDB3\Offer\Item\Commands\AddLabel;
use CultuurNet\UDB3\Offer\Item\Commands\DeleteLabel;
use CultuurNet\UDB3\Offer\Item\Commands\Moderation\Approve;
use CultuurNet\UDB3\Offer\Item\Commands\Moderation\FlagAsDuplicate;
use CultuurNet\UDB3\Offer\Item\Commands\Moderation\FlagAsInappropriate;
use CultuurNet\UDB3\Offer\Item\Commands\Moderation\Publish;
use CultuurNet\UDB3\Offer\Item\Commands\Moderation\Reject;
use CultuurNet\UDB3\Offer\Item\Commands\TranslateDescription;
use CultuurNet\UDB3\Offer\Item\Commands\TranslateTitle;
use CultuurNet\UDB3\Offer\Item\Commands\UpdatePriceInfo;
use CultuurNet\UDB3\Offer\Item\Events\DescriptionTranslated;
use CultuurNet\UDB3\Offer\Item\Events\ItemCreated;
use CultuurNet\UDB3\Offer\Item\Events\LabelAdded;
use CultuurNet\UDB3\Offer\Item\Events\LabelDeleted;
use CultuurNet\UDB3\Offer\Item\Events\Moderation\Approved;
use CultuurNet\UDB3\Offer\Item\Events\Moderation\FlaggedAsDuplicate;
use CultuurNet\UDB3\Offer\Item\Events\Moderation\FlaggedAsInappropriate;
use CultuurNet\UDB3\Offer\Item\Events\Moderation\Published;
use CultuurNet\UDB3\Offer\Item\Events\Moderation\Rejected;
use CultuurNet\UDB3\Offer\Item\Events\PriceInfoUpdated;
use CultuurNet\UDB3\Offer\Item\Events\TitleTranslated;
use CultuurNet\UDB3\Offer\Item\ItemCommandHandler;
use CultuurNet\UDB3\Offer\Item\ItemRepository;
use CultuurNet\UDB3\Offer\Mock\Commands\AddLabel as AddLabelToSomethingElse;
use CultuurNet\UDB3\Offer\Mock\Commands\DeleteLabel as DeleteLabelFromSomethingElse;
use CultuurNet\UDB3\Offer\Mock\Commands\TranslateTitle as TranslateTitleOnSomethingElse;
use CultuurNet\UDB3\Offer\Mock\Commands\TranslateDescription as TranslateDescriptionOnSomethingElse;
use CultuurNet\UDB3\Offer\Mock\Commands\UpdatePriceInfo as UpdatePriceInfoOnSomethingElse;
use CultuurNet\UDB3\PriceInfo\BasePrice;
use CultuurNet\UDB3\PriceInfo\Price;
use CultuurNet\UDB3\PriceInfo\PriceInfo;
use PHPUnit_Framework_MockObject_MockObject;
use ValueObjects\Identity\UUID;
use ValueObjects\Money\Currency;
use ValueObjects\String\String as StringLiteral;

class OfferCommandHandlerTest extends CommandHandlerScenarioTestCase
{
    /**
     * @var int
     */
    protected $id;

    /**
     * @var Label
     */
    protected $label;

    /**
     * @var Language
     */
    protected $language;

    /**
     * @var StringLiteral
     */
    protected $title;

    /**
     * @var StringLiteral
     */
    protected $description;

    /**
     * @var PriceInfo
     */
    protected $priceInfo;

    /**
     * @var ItemCreated
     */
    protected $itemCreated;

    /**
     * @var RepositoryInterface|PHPUnit_Framework_MockObject_MockObject
     */
    protected $organizerRepository;

    /**
     * @var RepositoryInterface|PHPUnit_Framework_MockObject_MockObject
     */
    protected $labelRepository;

    public function setUp()
    {
        parent::setUp();

        $this->id = '123';
        $this->label = new Label('foo');
        $this->language = new Language('en');
        $this->title = new StringLiteral('English title');
        $this->description = new StringLiteral('English description');

        $this->itemCreated = new ItemCreated($this->id);

        $this->priceInfo = new PriceInfo(
            new BasePrice(
                Price::fromFloat(10.5),
                Currency::fromNative('EUR')
            )
        );
    }

    protected function createCommandHandler(
        EventStoreInterface $eventStore,
        EventBusInterface $eventBus
    ) {
        $this->organizerRepository = $this->getMock(RepositoryInterface::class);

        $this->labelRepository = $this->getMock(ReadRepositoryInterface::class);
        $this->labelRepository->method('getByName')
            ->with(new StringLiteral('foo'))
            ->willReturn(new Entity(
                new UUID(),
                new StringLiteral('foo'),
                Visibility::VISIBLE(),
                Privacy::PRIVACY_PUBLIC()
            ));

        return new ItemCommandHandler(
            new ItemRepository($eventStore, $eventBus),
            $this->organizerRepository,
            $this->labelRepository
        );
    }

    /**
     * @test
     */
    public function it_handles_add_label_commands_from_the_correct_namespace()
    {
        $this->scenario
            ->withAggregateId($this->id)
            ->given(
                [
                    $this->itemCreated
                ]
            )
            ->when(
                new AddLabel($this->id, $this->label)
            )
            ->then(
                [
                    new LabelAdded($this->id, $this->label)
                ]
            );
    }

    /**
     * @test
     */
    public function it_ignores_add_label_commands_from_incorrect_namespaces()
    {
        $this->scenario
            ->withAggregateId($this->id)
            ->given(
                [
                    $this->itemCreated
                ]
            )
            ->when(
                new AddLabelToSomethingElse($this->id, $this->label)
            )
            ->then([]);
    }

    /**
     * @test
     */
    public function it_handles_delete_label_commands_from_the_correct_namespace()
    {
        $this->scenario
            ->withAggregateId($this->id)
            ->given(
                [
                    $this->itemCreated,
                    new LabelAdded($this->id, $this->label),
                ]
            )
            ->when(
                new DeleteLabel($this->id, $this->label)
            )
            ->then(
                [
                    new LabelDeleted($this->id, $this->label)
                ]
            );
    }

    /**
     * @test
     */
    public function it_ignores_delete_label_commands_from_incorrect_namespaces()
    {
        $this->scenario
            ->withAggregateId($this->id)
            ->given(
                [
                    $this->itemCreated,
                    new LabelAdded($this->id, $this->label),
                ]
            )
            ->when(
                new DeleteLabelFromSomethingElse($this->id, $this->label)
            )
            ->then([]);
    }

    /**
     * @test
     */
    public function it_handles_translate_title_commands_from_the_correct_namespace()
    {
        $this->scenario
            ->withAggregateId($this->id)
            ->given(
                [
                    $this->itemCreated
                ]
            )
            ->when(
                new TranslateTitle($this->id, $this->language, $this->title)
            )
            ->then(
                [
                    new TitleTranslated($this->id, $this->language, $this->title)
                ]
            );
    }

    /**
     * @test
     */
    public function it_ignores_translate_title_commands_from_incorrect_namespace()
    {
        $this->scenario
            ->withAggregateId($this->id)
            ->given(
                [
                    $this->itemCreated
                ]
            )
            ->when(
                new TranslateTitleOnSomethingElse($this->id, $this->language, $this->title)
            )
            ->then([]);
    }

    /**
     * @test
     */
    public function it_handles_translate_description_commands_from_the_correct_namespace()
    {
        $this->scenario
            ->withAggregateId($this->id)
            ->given(
                [
                    $this->itemCreated
                ]
            )
            ->when(
                new TranslateDescription($this->id, $this->language, $this->description)
            )
            ->then(
                [
                    new DescriptionTranslated($this->id, $this->language, $this->description)
                ]
            );
    }

    /**
     * @test
     */
    public function it_ignores_translate_description_commands_from_incorrect_namespace()
    {
        $this->scenario
            ->withAggregateId($this->id)
            ->given(
                [
                    $this->itemCreated
                ]
            )
            ->when(
                new TranslateDescriptionOnSomethingElse($this->id, $this->language, $this->description)
            )
            ->then([]);
    }

    /**
     * @test
     */
    public function it_handles_price_info_commands_from_the_correct_namespace()
    {
        $this->scenario
            ->withAggregateId($this->id)
            ->given(
                [
                    $this->itemCreated,
                ]
            )
            ->when(new UpdatePriceInfo($this->id, $this->priceInfo))
            ->then(
                [
                    new PriceInfoUpdated($this->id, $this->priceInfo),
                ]
            );
    }

    /**
     * @test
     */
    public function it_does_not_update_price_info_if_there_were_no_changes()
    {
        $this->scenario
            ->withAggregateId($this->id)
            ->given(
                [
                    $this->itemCreated,
                    new PriceInfoUpdated($this->id, $this->priceInfo),
                ]
            )
            ->when(new UpdatePriceInfo($this->id, $this->priceInfo))
            ->then([]);
    }

    /**
     * @test
     */
    public function it_ignores_price_info_commands_from_incorrect_namespace()
    {
        $this->scenario
            ->withAggregateId($this->id)
            ->given(
                [
                    $this->itemCreated,
                ]
            )
            ->when(new UpdatePriceInfoOnSomethingElse($this->id, $this->priceInfo))
            ->then([]);
    }

    /**
     * @test
     */
    public function it_handles_approve_command_on_ready_for_validation_item()
    {
        $this->scenario
            ->withAggregateId($this->id)
            ->given([
                $this->itemCreated,
                new Published($this->id, new \DateTime())
            ])
            ->when(new Approve($this->id))
            ->then([
                new Approved($this->id)
            ]);
    }

    /**
     * @test
     */
    public function it_handles_flag_as_duplicate_command_on_ready_for_validation_item()
    {
        $this->scenario
            ->withAggregateId($this->id)
            ->given([
                $this->itemCreated,
                new Published($this->id, new \DateTime())
            ])
            ->when(new FlagAsDuplicate($this->id))
            ->then([
                new FlaggedAsDuplicate($this->id)
            ]);
    }

    /**
     * @test
     */
    public function it_handles_flag_as_inappropriate_command_on_ready_for_validation_item()
    {
        $this->scenario
            ->withAggregateId($this->id)
            ->given([
                $this->itemCreated,
                new Published($this->id, new \DateTime())
            ])
            ->when(new FlagAsInappropriate($this->id))
            ->then([
                new FlaggedAsInappropriate($this->id)
            ]);
    }

    /**
     * @test
     */
    public function it_handles_reject_command_on_ready_for_validation_item()
    {
        $reason = new StringLiteral('reject reason');

        $this->scenario
            ->withAggregateId($this->id)
            ->given([
                $this->itemCreated,
                new Published($this->id, new \DateTime())
            ])
            ->when(new Reject($this->id, $reason))
            ->then([
                new Rejected($this->id, $reason)
            ]);
    }
}
