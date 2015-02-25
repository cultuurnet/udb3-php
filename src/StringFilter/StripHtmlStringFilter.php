<?php

namespace CultuurNet\UDB3\StringFilter;

class StripHtmlStringFilter implements StringFilterInterface
{
    /**
     * {@inheritdoc}
     */
    public function filter($string)
    {
        // Add newlines after break tags.
        $string = $this->addNewLinesAfterClosingTags($string, 'br', true);

        // Add newlines after closing paragraph tags.
        $string = $this->addNewLinesAfterClosingTags($string, 'p');

        // Decode all HTML entities, like &amp;, so they are human-readable.
        $string = html_entity_decode($string);

        // Strip all HTML tags.
        $string = strip_tags($string);

        // Trim any whitespace or newlines from the start and/or end of the string.
        $string = trim($string);

        return $string;
    }

    /**
     * Adds newlines after each occurrence of a specific closing HTML tag, unless there's already one.
     *
     * @param string $string
     *   String to add newlines to.
     * @param string $tag
     *   Tag name. For example "br" to add a newline after each "<br />" or "<br>".
     * @param bool $selfClosing
     *   Indicates whether the tag is self-closing or not.
     *
     * @return string
     *   Processed string.
     */
    protected function addNewLinesAfterClosingTags($string, $tag, $selfClosing = false)
    {
        // Start of the pattern.
        $pattern = '/';

        if ($selfClosing) {
            // Find the self-closing tag, including its attributes and optionally a closing slash.
            $pattern .= '<' . $tag . '\\s*[\\/]?>';
        } else {
            // Find the closing tag.
            $pattern .= '<\\/' . $tag . '>';
        }

        // Make sure the character after the tag is NOT a newline, or that there's no character after the tag. Use a
        // lookahead to make sure the next character is not added to the matched string if it's not a newline or the end
        // of the string.
        $pattern .= '(?=[^\\n]|$)';

        // End of the pattern. Use i to make it case-insensitive, as HTML tags can be both uppercase and lowercase.
        $pattern .= '/i';

        // Append all pattern matches with a newline character.
        $string = preg_replace_callback($pattern, function($match) {
            // Return the whole match (first value) appended by a newline character.
            return $match[0] . PHP_EOL;
        }, $string);
        return $string;
    }
}
