<?php

namespace CultuurNet\UDB3\Label;

use Broadway\EventSourcing\Testing\AggregateRootScenarioTestCase;
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

class LabelTest extends AggregateRootScenarioTestCase
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
    protected function getAggregateRootClass()
    {
        return Label::class;
    }

    /**
     * @test
     */
    public function it_can_create_a_new_label()
    {
        $this->scenario
            ->when(function () {
                return Label::create(
                    $this->uuid,
                    $this->name,
                    $this->visibility,
                    $this->privacy
                );
            })
            ->then([$this->created]);
    }

    /**
     * @test
     */
    public function it_can_create_a_copied_label()
    {
        $this->scenario
            ->when(function () {
                return Label::createCopy(
                    $this->uuid,
                    $this->name,
                    $this->visibility,
                    $this->privacy,
                    $this->parentUuid
                );
            })
            ->then([$this->copyCreated]);
    }

    /**
     * @test
     */
    public function it_can_make_a_label_visible_when_invisible()
    {
        $this->scenario
            ->withAggregateId($this->uuid)
            ->given([$this->created])
            ->when(function ($label) {
                /** @var Label $label */
                $label->makeVisible();
            })
            ->then([new MadeVisible($this->uuid)]);
    }

    /**
     * @test
     */
    public function it_does_not_make_a_label_invisible_when_already_visible()
    {
        $this->scenario
            ->withAggregateId($this->uuid)
            ->given([$this->created, new MadeVisible($this->uuid)])
            ->when(function ($label) {
                /** @var Label $label */
                $label->makeVisible();
            })
            ->then([]);
    }

    /**
     * @test
     */
    public function it_can_make_a_label_invisible_when_visible()
    {
        $this->scenario
            ->withAggregateId($this->uuid)
            ->given([$this->created, new MadeVisible($this->uuid)])
            ->when(function ($label) {
                /** @var Label $label */
                $label->makeInvisible();
            })
            ->then([new MadeInvisible($this->uuid)]);
    }

    /**
     * @test
     */
    public function it_does_not_make_a_label_invisible_when_already_invisible()
    {
        $this->scenario
            ->withAggregateId($this->uuid)
            ->given([$this->created])
            ->when(function ($label) {
                /** @var Label $label */
                $label->makeInvisible();
            })
            ->then([]);
    }

    /**
     * @test
     */
    public function it_can_make_a_label_public_when_private()
    {
        $this->scenario
            ->withAggregateId($this->uuid)
            ->given([$this->created])
            ->when(function ($label) {
                /** @var Label $label */
                $label->makePublic();
            })
            ->then([new MadePublic($this->uuid)]);
    }

    /**
     * @test
     */
    public function it_does_not_make_a_label_public_when_already_public()
    {
        $this->scenario
            ->withAggregateId($this->uuid)
            ->given([$this->created, new MadePublic($this->uuid)])
            ->when(function ($label) {
                /** @var Label $label */
                $label->makePublic();
            })
            ->then([]);
    }

    /**
     * @test
     */
    public function it_can_make_a_label_private_when_public()
    {
        $this->scenario
            ->withAggregateId($this->uuid)
            ->given([$this->created, new MadePublic($this->uuid)])
            ->when(function ($label) {
                /** @var Label $label */
                $label->makePrivate();
            })
            ->then([new MadePrivate($this->uuid)]);
    }

    /**
     * @test
     */
    public function it_does_not_make_a_label_private_when_already_private()
    {
        $this->scenario
            ->withAggregateId($this->uuid)
            ->given([$this->created])
            ->when(function ($label) {
                /** @var Label $label */
                $label->makePrivate();
            })
            ->then([]);
    }
}