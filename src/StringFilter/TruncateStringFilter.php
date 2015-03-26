<?php
/**
 * @file
 */

namespace CultuurNet\UDB3\StringFilter;

/**
 * Based on Drupal 8 Unicode class.
 *
 * @see https://api.drupal.org/api/drupal/core%21lib%21Drupal%21Component%21Utility%21Unicode.php/class/Unicode/8
 */
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
     * @inheritdoc
     */
    public function filter($utfEncodedString)
    {
        $wordSafe = $this->wordSafe;
        $ellipsis = '';
        $maxLength = max($this->maxLength, 0);
        $minWordSafeLength = max($this->minWordSafeLength, 0);
        $string = utf8_decode($utfEncodedString);

        if (mb_strlen($string) <= $maxLength) {
            // No truncation needed, so don't add ellipsis, just return.
            return $string;
        }

        if ($this->addEllipsis) {
            // Truncate ellipsis in case $max_length is small.
            $ellipsis = mb_substr('...', 0, $maxLength);
            $maxLength -= mb_strlen($ellipsis);
            $maxLength = max($maxLength, 0);
        }

        if ($maxLength <= $minWordSafeLength) {
            // Do not attempt word-safe if lengths are bad.
            $wordSafe = false;
        }

        if ($wordSafe) {
            $encoding = "UTF-8";
            $substringLength = mb_strlen($ellipsis, $encoding);
            $length = $maxLength - $substringLength;

            $truncated = mb_substr($string, 0, $length, $encoding);

            // If the last word was truncated
            if (mb_strpos($string, ' ', $length - 1, $encoding) != $length) {
                // Find pos of the last occurrence of a space, get up to that
                $lastPos = mb_strrpos($truncated, ' ', 0, $encoding);
                $truncated = mb_substr($truncated, 0, $lastPos, $encoding);
            }

            $string = $truncated;
        } else {
            $string = mb_substr($string, 0, $maxLength);
        }

        if ($this->addEllipsis) {
            // If we're adding an ellipsis, remove any trailing periods or spaces.
            $string = rtrim($string, '. ');

            $string .= $ellipsis;
        }

        return utf8_encode($string);
    }
}
