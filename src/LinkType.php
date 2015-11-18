<?php
/**
 * Created by PhpStorm.
 * User: nicolas
 * Date: 18/11/15
 * Time: 12:14
 */

namespace CultuurNet\UDB3;

use ValueObjects\String\String;

class LinkType extends String
{

    public function __construct($value)
    {
        $possibleValues = [
            'photo',
            'roadmap',
            'text',
            'imageweb',
            'webresource',
            'blog',
            'website',
            'youtube',
            'google-plus',
            'twitter',
            'facebook',
            'tagline',
            'reservations',
            'culturefeed-page',
            'collaboration'
        ];

        if (!in_array($value, $possibleValues)) {
            throw new \InvalidArgumentException(
                'Invalid link type: ' . $value
            );
        }

        parent::__construct($value);
    }
}
