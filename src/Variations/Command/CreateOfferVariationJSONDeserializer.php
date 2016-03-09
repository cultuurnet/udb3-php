<?php

namespace CultuurNet\UDB3\Variations\Command;

use CultuurNet\Deserializer\JSONDeserializer;
use CultuurNet\UDB3\Offer\IriOfferIdentifierFactory;
use CultuurNet\UDB3\Offer\IriOfferIdentifierFactoryInterface;
use CultuurNet\UDB3\Variations\Model\Properties\Description;
use CultuurNet\UDB3\Variations\Model\Properties\OwnerId;
use CultuurNet\UDB3\Variations\Model\Properties\Purpose;
use CultuurNet\UDB3\Variations\Model\Properties\Url;
use CultuurNet\UDB3\Variations\Model\Properties\UrlValidator;
use JsonSchema\Validator;
use ValueObjects\String\String;
use stdClass;

class CreateOfferVariationJSONDeserializer extends JSONDeserializer
{
    /**
     * @var UrlValidator[]
     */
    private $urlValidators = [];

    /**
     * @var IriOfferIdentifierFactory
     */
    private $iriOfferIdentifierFactory;

    public function __construct(IriOfferIdentifierFactoryInterface $iriOfferIdentifierFactory)
    {
        $this->iriOfferIdentifierFactory = $iriOfferIdentifierFactory;
    }

    /**
     * @param UrlValidator $urlValidator
     */
    public function addUrlValidator(UrlValidator $urlValidator)
    {
        $this->urlValidators[] = $urlValidator;
    }

    /**
     * @inheritdoc
     *
     * @return CreateOfferVariation
     */
    public function deserialize(String $data)
    {
        $json = parent::deserialize(
            $data
        );

        $this->guardValidnessWithJSONSchema($json);

        return $this->createTypedObject($json);
    }

    /**
     * @param stdClass $json
     *
     * @return CreateOfferVariation
     *
     * @throws ValidationException
     */
    private function createTypedObject(stdClass $json)
    {
        $url = new Url($json->same_as);
        $iriOfferIdentifier = $this->iriOfferIdentifierFactory->fromIri(
            $url->toNative()
        );

        foreach ($this->urlValidators as $urlValidator) {
            $urlValidator->validateUrl($url);
        }

        return new CreateOfferVariation(
            $iriOfferIdentifier,
            new OwnerId($json->owner),
            new Purpose($json->purpose),
            new Description($json->description)
        );
    }

    /**
     * @param mixed $json
     *
     * @throws ValidationException
     */
    private function guardValidnessWithJSONSchema($json)
    {
        // @todo JSON-SCHEMA inside swagger.json should be reused here
        $schema = json_decode('{
            "type": "object",
            "properties": {
                "owner": {
                    "type": "string"
                },
                "purpose": {
                    "type": "string"
                },
                "same_as": {
                    "type": "string"
                },
                "description": {
                    "type": "string"
                }
            },
            "required": [
                "owner",
                "purpose",
                "same_as",
                "description"
            ]
        }');

        $validator = new Validator();
        $validator->check($json, $schema);

        if (!$validator->isValid()) {
            $errors = $validator->getErrors();

            $errorMessages = $this->getErrorMessages($errors);

            throw new ValidationException($errorMessages);
        }
    }

    /**
     * @param array $validationErrors
     * @return string[]
     */
    private function getErrorMessages($validationErrors)
    {
        $errorMessages = array_map(
            function ($error) {
                return $error['message'];
            },
            $validationErrors
        );

        return $errorMessages;
    }
}
