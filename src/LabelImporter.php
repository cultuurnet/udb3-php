<?php

namespace CultuurNet\UDB3;

use CultureFeed_Cdb_Data_Keyword;

class LabelImporter
{
    /**
     * @param \CultureFeed_Cdb_Item_Base $item
     * @param $jsonLD
     */
    public function importLabels(\CultureFeed_Cdb_Item_Base $item, $jsonLD)
    {
        /** @var CultureFeed_Cdb_Data_Keyword[] $keywords */
        $keywords = array_values($item->getKeywords(true));

        $validKeywords = array_filter(
            $keywords,
            function (CultureFeed_Cdb_Data_Keyword $keyword) {
                return strlen(trim($keyword->getValue())) > 0;
            }
        );

        $visibleKeywords = array_filter(
            $validKeywords,
            function (CultureFeed_Cdb_Data_Keyword $keyword) {
                return $keyword->isVisible();
            }
        );

        $hiddenKeywords = array_filter(
            $validKeywords,
            function (CultureFeed_Cdb_Data_Keyword $keyword) {
                return !$keyword->isVisible();
            }
        );

        $this->addKeywordsAsLabelsProperty($jsonLD, 'labels', $visibleKeywords);
        $this->addKeywordsAsLabelsProperty($jsonLD, 'hiddenLabels', $hiddenKeywords);
    }

    /**
     * @param object $jsonLD
     * @param string $labelsPropertyName
     *  The property where the labels should be listed. Used the differentiate between visible and hidden labels.
     * @param CultureFeed_Cdb_Data_Keyword[] $keywords
     */
    private function addKeywordsAsLabelsProperty($jsonLD, $labelsPropertyName, array $keywords)
    {
        $labels = array_map(
            function ($keyword) {
                /** @var CultureFeed_Cdb_Data_Keyword $keyword */
                return $keyword->getValue();
            },
            $keywords
        );

        // Create a label collection to get rid of duplicates.
        $labelCollection = LabelCollection::fromStrings($labels);

        if (count($labelCollection) > 0) {
            $jsonLD->{$labelsPropertyName} = $labelCollection->toStrings();
        }
    }
}
