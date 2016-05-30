<?php

namespace CultuurNet\UDB3\Label;

use Broadway\CommandHandling\Testing\CommandHandlerScenarioTestCase;
use Broadway\EventHandling\EventBusInterface;
use Broadway\EventStore\EventStoreInterface;
use CultuurNet\UDB3\Label\Commands\Create;
use CultuurNet\UDB3\Label\Commands\CreateCopy;
use CultuurNet\UDB3\Label\Commands\MakeInvisible;
use CultuurNet\UDB3\Label\Commands\MakePrivate;
use CultuurNet\UDB3\Label\Commands\MakePublic;
use CultuurNet\UDB3\Label\Commands\MakeVisible;
use CultuurNet\UDB3\Label\Events\CopyCreated;
use CultuurNet\UDB3\Label\Events\Created;
use CultuurNet\UDB3\Label\Events\MadeInvisible;
use CultuurNet\UDB3\Label\Events\MadePrivate;
use CultuurNet\UDB3\Label\Events\MadePublic;
use CultuurNet\UDB3\Label\Events\MadeVisible;
use CultuurNet\UDB3\Label\ValueObjects\Privacy;
use CultuurNet\UDB3\Label\ValueObjects\Visibility;
use ValueObjects\Identity\UUID;
use ValueObjects\String\String as StringLiteral;

class CommandHandlerTest extends CommandHandlerScenarioTestCase
{
    /**
     * @var UUID
     */
    private $uuid;

    /**
     * @var StringLiteral
     */
    private $name;

    /**
     * @var Visibility
     */
    private $visibility;

    /**
     * @var Privacy
     */
    private $privacy;

    /**
     * @var UUID
     */
    private $parentUuid;

    /**
     * @var Created
     */
    private $created;

    /**
     * @var CopyCreated
     */
    private $copyCreated;

    public function setUp()
    {
        parent::setUp();

        $this->uuid = new UUID();
        $this->name = new StringLiteral('labelName');
        $this->visibility = Visibility::INVISIBLE();
        $this->privacy = Privacy::PRIVACY_PRIVATE();
        $this->parentUuid = new UUID();

        $this->created = new Created(
            $this->uuid,
            $this->name,
            $this->visibility,
            $this->privacy
        );

        $this->copyCreated = new CopyCreated(
            $this->uuid,
            $this->name,
            $this->visibility,
            $this->privacy,
            $this->parentUuid
        );
    }

    /**
     * @inheritdoc
     */
    protected function createCommandHandler(
        EventStoreInterface $eventStore,
        EventBusInterface $eventBus
    ) {
        return new CommandHandler(new LabelRepository(
            $eventStore,
            $eventBus
        ));
    }

    /**
     * @test
     */
    public function it_handles_create()
    {
        $this->scenario
            ->withAggregateId($this->uuid)
            ->given([])
            ->when(new Create(
                $this->uuid,
                $this->name,
                $this->visibility,
                $this->privacy
            ))
            ->then([$this->created]);
    }

    /**
     * @test
     */
    public function it_handles_create_copy()
    {
        $this->scenario
            ->withAggregateId($this->uuid)
            ->given([])
            ->when(new CreateCopy(
                $this->uuid,
                $this->name,
                $this->visibility,
                $this->privacy,
                $this->parentUuid
            ))
            ->then([$this->copyCreated]);
    }

    /**
     * @test
     */
    public function it_handles_make_visible_when_invisible()
    {
        $this->scenario
            ->withAggregateId($this->uuid)
            ->given([$this->created])
            ->when(new MakeVisible($this->uuid))
            ->then([new MadeVisible($this->uuid)]);
    }

    /**
     * @test
     */
    public function it_does_not_handle_make_visible_when_already_visible()
    {
        $this->scenario
            ->withAggregateId($this->uuid)
            ->given([$this->created, new MadeVisible($this->uuid)])
            ->when(new MakeVisible($this->uuid))
            ->then([]);
    }

    /**
     * @test
     */
    public function it_handles_make_invisible_when_visible()
    {
        $this->scenario
            ->withAggregateId($this->uuid)
            ->given([$this->created, new MadeVisible($this->uuid)])
            ->when(new MakeInvisible($this->uuid))
            ->then([new MadeInvisible($this->uuid)]);
    }

    /**
     * @test
     */
    public function it_does_not_handle_make_invisible_when_already_invisible()
    {
        $this->scenario
            ->withAggregateId($this->uuid)
            ->given([$this->created])
            ->when(new MakeInvisible($this->uuid))
            ->then([]);
    }

    /**
     * @test
     */
    public function it_handles_make_public_when_private()
    {
        $this->scenario
            ->withAggregateId($this->uuid)
            ->given([$this->created])
            ->when(new MakePublic($this->uuid))
            ->then([new MadePublic($this->uuid)]);
    }

    /**
     * @test
     */
    public function it_does_not_handle_make_public_when_already_public()
    {
        $this->scenario
            ->withAggregateId($this->uuid)
            ->given([$this->created, new MadePublic($this->uuid)])
            ->when(new MakePublic($this->uuid))
            ->then([]);
    }

    /**
     * @test
     */
    public function it_handles_make_private_when_public()
    {
        $this->scenario
            ->withAggregateId($this->uuid)
            ->given([$this->created, new MadePublic($this->uuid)])
            ->when(new MakePrivate($this->uuid))
            ->then([new MadePrivate($this->uuid)]);
    }

    /**
     * @test
     */
    public function it_does_not_handle_make_private_when_already_private()
    {
        $this->scenario
            ->withAggregateId($this->uuid)
            ->given([$this->created])
            ->when(new MakePrivate($this->uuid))
            ->then([]);
    }
}
