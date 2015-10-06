<?php
/**
 * Created by PhpStorm.
 * User: nicolas
 * Date: 06/10/15
 * Time: 10:10
 */

namespace CultuurNet\UDB3;

use ValueObjects\String\String;

class XmlString extends String
{
    public function __construct($value)
    {
        parent::__construct($value);

        $dom = new \DOMDocument('1.0', 'UTF8');
        $isValidXML = $dom->loadXML($value);

        if (!$isValidXML) {
            throw new XMLSyntaxException();
        }
    }
}
