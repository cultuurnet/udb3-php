<?php
/**
 * @file
 */

namespace CultuurNet\UDB3\Variations\Command;

use CultuurNet\Deserializer\JSONDeserializer;
use CultuurNet\UDB3\Variations\Model\Properties\Description;
use CultuurNet\UDB3\Variations\Model\Properties\OwnerId;
use CultuurNet\UDB3\Variations\Model\Properties\Purpose;
use CultuurNet\UDB3\Variations\Model\Properties\Url;
use CultuurNet\UDB3\Variations\Model\Properties\UrlValidator;
use JsonSchema\Validator;
use ValueObjects\String\String;
use stdClass;

class CreateEventVariationJSONDeserializer extends JSONDeserializer
{
    /**
     * @var UrlValidator[]
     */
    private $urlValidators;

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
     * @return CreateEventVariation
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
     * @return CreateEventVariation
     *
     * @throws ValidationException
     */
    private function createTypedObject(stdClass $json)
    {
        $url = new Url($json->same_as);

        foreach ($this->urlValidators as $urlValidator) {
            $urlValidator->validateUrl($url);
        }

        return new CreateEventVariation(
            $url,
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
