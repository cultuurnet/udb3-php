<?php

namespace CultuurNet\UDB3\Organizer\Events;

use CultuurNet\UDB3\Language;
use CultuurNet\UDB3\Title;
use ValueObjects\Web\Url;

class OrganizerCreatedWithUniqueWebsite extends OrganizerEvent
{
    /**
     * @var Language
     */
    private $mainLanguage;

    /**
     * @var Url
     */
    private $website;

    /**
     * @var Title
     */
    private $title;

    /**
     * @param string $id
     * @param Language $mainLanguage
     * @param Url $website
     * @param Title $title
     */
    public function __construct(
        $id,
        Language $mainLanguage,
        Url $website,
        Title $title
    ) {
        parent::__construct($id);

        $this->mainLanguage = $mainLanguage;
        $this->website = $website;
        $this->title = $title;
    }

    /**
     * @return Language
     */
    public function getMainLanguage()
    {
        return $this->mainLanguage;
    }

    /**
     * @return Url
     */
    public function getWebsite()
    {
        return $this->website;
    }

    /**
     * @return Title
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * @return array
     */
    public function serialize()
    {
        return parent::serialize() + array(
            'main_language' => $this->getMainLanguage()->getCode(),
            'website' => (string) $this->getWebsite(),
            'title' => (string) $this->getTitle(),
        );
    }

    /**
     * @return static
     */
    public static function deserialize(array $data)
    {
        return new static(
            $data['organizer_id'],
            new Language($data['main_language']),
            Url::fromNative($data['website']),
            new Title($data['title'])
        );
    }
}
