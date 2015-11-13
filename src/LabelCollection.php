<?php
/**
 * @file
 */

namespace CultuurNet\UDB3;

class LabelCollection implements \Countable
{
    /**
     * @var Label[]
     */
    private $labels;

    /**
     * @param Label[] $labels
     */
    public function __construct(array $labels = [])
    {
        array_walk(
            $labels,
            function ($item) {
                if (!$item instanceof Label) {
                    throw new \InvalidArgumentException(
                        'Argument $labels should only contain members of type Label'
                    );
                }
            }
        );

        $this->labels = $labels;
    }

    /**
     * @param Label $label
     * @return LabelCollection
     */
    public function with(Label $label)
    {
        if ($this->contains($label)) {
            throw new \RuntimeException('Label "' . $label . '" is already present in collection.');
        }

        $newLabels = array_merge($this->labels, [$label]);

        return new LabelCollection($newLabels);
    }

    /**
     * @param Label $label
     * @return LabelCollection
     */
    public function without(Label $label)
    {
        $labels = array_filter(
            $this->labels,
            function (Label $existingLabel) use ($label) {
                return !$existingLabel->equals($label);
            }
        );

        return new LabelCollection($labels);
    }

    /**
     * @inheritdoc
     */
    public function count()
    {
        return count($this->labels);
    }

    /**
     * @param Label[] $label
     * @return bool
     */
    public function contains(Label $label)
    {
        $equalLabels = array_filter(
            $this->labels,
            function (Label $existingLabel) use ($label) {
                return $label->equals($existingLabel);
            }
        );

        return !empty($equalLabels);
    }

    /**
     * @return Label[]
     */
    public function asArray()
    {
        return $this->labels;
    }

    /**
     * @param LabelCollection $labelCollection
     * @return LabelCollection
     */
    public function merge(LabelCollection $labelCollectionToMerge)
    {
        $mergedLabels = [];

        // Keep labels from the original collection that are not in the
        // collection to merge.
        foreach ($this->labels as $label) {
            if (!$labelCollectionToMerge->contains($label)) {
                $mergedLabels[] = $label;
            }
        }

        // Add all labels from the collection to merge.
        $add = $labelCollectionToMerge->asArray();
        $mergedLabels = array_merge($mergedLabels, $add);

        return new LabelCollection($mergedLabels);
    }

    /**
     * @param string[] $strings
     */
    public static function fromStrings(array $strings)
    {
        $labelCollection = new LabelCollection();

        foreach ($strings as $string) {
            $label = new Label($string);

            $labelCollection = $labelCollection->with($label);
        }

        return $labelCollection;
    }

    /**
     * @return string[]
     */
    public function toStrings()
    {
        $labels = array_map(
            function (Label $label) {
                return (string) $label;
            },
            $this->labels
        );

        return $labels;
    }
}
