<?php
/**
 * @file
 */

namespace CultuurNet\UDB3\StringFilter;

use Stringy\Stringy as Stringy;

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
    public function filter($string)
    {
        $wordSafe = $this->wordSafe;
        $ellipsis = '';
        $maxLength = max($this->maxLength, 0);
        $minWordSafeLength = max($this->minWordSafeLength, 0);

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
             return (string) Stringy::create($string, 'UTF-8')->safeTruncate(
                 $maxLength,
                 $ellipsis
             );
        } else {
            $string = mb_substr($string, 0, $maxLength);
        }

        if ($this->addEllipsis) {
            // If we're adding an ellipsis, remove any trailing periods or spaces.
            $string = rtrim($string, '. ');

            $string .= $ellipsis;
        }

        return $string;
    }
}
