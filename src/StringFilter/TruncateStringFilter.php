<?php
/**
 * @file
 */

namespace CultuurNet\UDB3\StringFilter;

use Stringy\Stringy as Stringy;

class TruncateStringFilter implements StringFilterInterface
{
    /**
     * @var bool
     */
    protected $wordSafe = false;

    /**
     * @var int
     */
    protected $minWordSafeLength;

    /**
     * @var bool
     */
    protected $addEllipsis = false;

    /**
     * @var bool
     */
    protected $sentenceFriendly = false;

    /**
     * @var int
     */
    protected $maxLength;

    /**
     * @param int $maxLength
     */
    public function __construct($maxLength)
    {
        $this->setMaxLength($maxLength);
    }

    /**
     * @param int $maxLength
     */
    public function setMaxLength($maxLength)
    {
        $this->maxLength = $maxLength;
    }

    /**
     * @param bool $toggle
     */
    public function addEllipsis($toggle = true)
    {
        $this->addEllipsis = $toggle;
    }

    /**
     * @param int $minWordSafeLength
     */
    public function turnOnWordSafe($minWordSafeLength = 1)
    {
        $this->wordSafe = true;
        $this->minWordSafeLength = $minWordSafeLength;
    }

    /**
     * When turned on, the filter will try not to truncate in the middle of a sentence.
     */
    public function beSentenceFriendly()
    {
        $this->sentenceFriendly = true;
    }

    /**
     * @inheritdoc
     */
    public function filter($string)
    {
        // Maximum length and minimum length to enable word-safe truncating should always be greater than zero.
        $maxLength = max($this->maxLength, 0);
        $minWordSafeLength = max($this->minWordSafeLength, 0);

        // Do not attempt word-safe truncating if the maximum length is smaller than the minimum length to do
        // word-safe truncating.
        $wordSafe = $this->wordSafe && $maxLength >= $minWordSafeLength;

        // Define the suffix of the truncated string.
        $suffix = '';
        if ($this->addEllipsis) {
            $suffix = Stringy::create('...', 'UTF-8');

            // If the ellipsis is longer or equal to the maximum length, simply truncate the ellipsis so it fits in
            // the maximum length and return it.
            if ($suffix->length() >= $maxLength) {
                return (string) $suffix->truncate($maxLength);
            }
        }

        $stringy = Stringy::create($string, 'UTF-8');

        $endingSymbols = ['. ', '? ', '! ', '.'.PHP_EOL, '?'.PHP_EOL, '!'.PHP_EOL];
        $hasEndingSymbolInRange = $stringy
          ->first($maxLength)
          ->containsAny($endingSymbols);

        if ($this->sentenceFriendly && $hasEndingSymbolInRange) {
            $lastOccurrence = 0;
            $haystack = (string) $stringy->first($maxLength);

            foreach ($endingSymbols as $needle) {
                $position = mb_strrpos($haystack, $needle);
                if ($position && $position > $lastOccurrence) {
                    // Add one to include the first character of the ending symbol
                    $lastOccurrence = $position + 1;
                }
            }

            $truncated = $stringy->truncate($lastOccurrence + strlen($suffix), $suffix);
        } else if ($wordSafe) {
            $truncated = $stringy->safeTruncate($maxLength, $suffix);
        } else {
            $truncated = $stringy->truncate($maxLength, $suffix);
        }

        if ($this->addEllipsis) {
            // Make sure the string does not end in more than 3 dots. The pattern looks for a sequence of
            // 4 or more ("{4,}") dots ("(\\.)") at the end of the string ("$").
            $pattern = '(\\.){4,}$';
            $truncated = $truncated->regexReplace($pattern, $suffix);
        }

        return (string) $truncated;
    }
}
