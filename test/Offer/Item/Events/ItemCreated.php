<?php

namespace CultuurNet\UDB3\Offer\Item\Events;

use Broadway\Serializer\SerializableInterface;
use CultuurNet\UDB3\Language;

final class ItemCreated implements SerializableInterface
{
    /**
     * @var string
     */
    protected $itemId;

    /**
     * @var Language
     */
    protected $mainLanguage;

    /**
     * @param string $itemId
     * @param Language $mainLanguage
     */
    final public function __construct(
        string $itemId,
        Language $mainLanguage = null
    ) {
        $this->itemId = $itemId;
        $this->mainLanguage = $mainLanguage ?: new Language('nl');
    }

    /**
     * @return string
     */
    public function getItemId(): string
    {
        return $this->itemId;
    }

    /**
     * @return Language
     */
    public function getMainLanguage(): Language
    {
        return $this->mainLanguage;
    }

    /**
     * @param array $data
     * @return static
     */
    public static function deserialize(array $data): ItemCreated
    {
        return new static($data['itemId'], $data['main_language']);
    }

    /**
     * @return array
     */
    public function serialize(): array
    {
        return [
            'itemId' => $this->itemId,
            'main_language'=> $this->mainLanguage->getCode(),
        ];
    }
}
