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

        $visibleLabels = LabelCollection::fromKeywords($visibleKeywords)->toStrings();
        $hiddenLabels = LabelCollection::fromKeywords($hiddenKeywords)->toStrings();

        empty($visibleLabels) ?: $jsonLD->labels = $visibleLabels;
        empty($hiddenLabels) ?: $jsonLD->hiddenLabels = $hiddenLabels;
    }
}
