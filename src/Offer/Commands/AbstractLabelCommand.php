<?php

namespace CultuurNet\UDB3\Offer\Commands;

use CultuurNet\UDB3\Label;
use CultuurNet\UDB3\Security\LabelSecurityInterface;
use ValueObjects\String\String as StringLiteral;

abstract class AbstractLabelCommand extends AbstractCommand implements LabelSecurityInterface
{
    /**
     * @var Label
     */
    protected $label;

    /**
     * @var bool
     */
    private $alwaysAllowed = false;

    /**
     * @param $itemId
     *  The id of the item that is targeted by the command.
     *
     * @param Label $label
     *  The label that is used in the command.
     */
    public function __construct($itemId, Label $label)
    {
        parent::__construct($itemId);
        $this->label = $label;
        $this->itemId = $itemId;
    }

    /**
     * @param bool $alwaysAllowed
     * @return AbstractLabelCommand
     */
    public function withAlwaysAllowed($alwaysAllowed)
    {
        $c = clone $this;
        $c->alwaysAllowed = $alwaysAllowed;
        return $c;
    }

    /**
     * @return string
     */
    public function getItemId()
    {
        return $this->itemId;
    }

    /**
     * @return Label
     */
    public function getLabel()
    {
        return $this->label;
    }

    /**
     * @inheritdoc
     */
    public function isIdentifiedByUuid()
    {
        return false;
    }

    /**
     * @inheritdoc
     */
    public function getName()
    {
        return new StringLiteral((string)$this->label);
    }

    /**
     * @inheritdoc
     */
    public function getUuid()
    {
        return null;
    }

    /**
     * @inheritdoc
     */
    public function isAlwaysAllowed()
    {
        return $this->alwaysAllowed;
    }
}
