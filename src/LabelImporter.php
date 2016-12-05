<?php

namespace CultuurNet\UDB3;

class LabelImporter
{
    /**
     * @param \CultureFeed_Cdb_Item_Base $item
     * @param $jsonLD
     */
    public function importLabels(\CultureFeed_Cdb_Item_Base $item, $jsonLD)
    {
        $labelCollection = LabelCollection::fromKeywords(
            $item->getKeywords(true)
        );

        $visibleLabelCollection = new LabelCollection();
        $hiddenLabelCollection = new LabelCollection();
        foreach ($labelCollection->asArray() as $label) {
            if ($label->isVisible()) {
                $visibleLabelCollection = $visibleLabelCollection->with($label);
            } else {
                $hiddenLabelCollection = $hiddenLabelCollection->with($label);
            }
        }

        $visibleLabels = $visibleLabelCollection->toStrings();
        $hiddenLabels = $hiddenLabelCollection->toStrings();

        empty($visibleLabels) ?: $jsonLD->labels = $visibleLabels;
        empty($hiddenLabels) ?: $jsonLD->hiddenLabels = $hiddenLabels;
    }
}
