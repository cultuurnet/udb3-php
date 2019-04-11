<?php

namespace CultuurNet\UDB3\Offer\Commands;

use CultuurNet\UDB3\Model\ValueObject\Taxonomy\Label\Label;
use CultuurNet\UDB3\Model\ValueObject\Taxonomy\Label\Labels;
use CultuurNet\UDB3\Security\LabelSecurityInterface;
use ValueObjects\StringLiteral\StringLiteral;

abstract class AbstractImportLabels extends AbstractCommand implements LabelSecurityInterface
{
    /**
     * @var Labels
     */
    private $labels;

    /**
     * @var Labels
     */
    private $labelsToKeepIfAlreadyOnOffer;

    /**
     * @param string $itemId
     * @param Labels $labels
     */
    public function __construct($itemId, Labels $labels)
    {
        parent::__construct($itemId);
        $this->labels = $labels;
        $this->labelsToKeepIfAlreadyOnOffer = new Labels();
    }

    public function withLabelsToKeepIfAlreadyOnOffer(Labels $labels): self
    {
        $c = clone $this;
        $c->labelsToKeepIfAlreadyOnOffer = $labels;
        return $c;
    }

    public function getLabelsToKeepIfAlreadyOnOffer(): Labels
    {
        return $this->labelsToKeepIfAlreadyOnOffer;
    }

    /**
     * @return Labels
     */
    public function getLabels()
    {
        return $this->labels;
    }

    /**
     * @inheritdoc
     */
    public function getNames()
    {
        return array_map(
            function (Label $label) {
                return new StringLiteral($label->getName()->toString());
            },
            $this->getLabels()->toArray()
        );
    }
}
