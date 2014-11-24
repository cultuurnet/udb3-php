<?php
/**
 * @file
 */

namespace CultuurNet\UDB3\Event;

use CultuurNet\UDB3\Language;

class DescriptionTranslated extends PropertyTranslated
{
    /**
     * @var string
     */
    protected $description;

    /**
     * @param string $id
     * @param Language $language
     * @param string $description
     */
    public function __construct($id, Language $language, $description)
    {
        parent::__construct($id, $language);
        $this->description = $description;
    }

    /**
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * @return array
     */
    public function serialize()
    {
        return parent::serialize() + array(
            'description' => $this->description,
        );
    }

    /**
     * {@inheritdoc}
     */
    public static function deserialize(array $data)
    {
        return new static(
            $data['event_id'],
            new Language($data['language']),
            $data['description']
        );
    }
}
