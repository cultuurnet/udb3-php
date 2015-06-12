<?php
/**
 * @file
 */

namespace CultuurNet\UDB3\Variations\Command;

use CultuurNet\Deserializer\JSONDeserializer;
use CultuurNet\UDB3\Variations\Model\Properties\Description;
use CultuurNet\UDB3\Variations\Model\Properties\Id;
use JsonSchema\Validator;
use ValueObjects\String\String;
use stdClass;

class EditDescriptionJSONDeserializer extends JSONDeserializer
{
    /**
     * @var Id
     */
    protected $id;

    /**
     * @param Id $id The id of the variation that's being edited
     */
    public function __construct(Id $id)
    {
        $this->id = $id;
    }

    /**
     * @inheritdoc
     *
     * @return EditDescription
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
     */
    private function createTypedObject(stdClass $json)
    {
        return new EditDescription(
            $this->id,
            new Description($json->description)
        );
    }

    /**
     * @param mixed $json
     *
     * @throws ValidationException
     */
    private function guardValidnessWithJSONSchema($json) {
        // @todo JSON-SCHEMA inside swagger.json should be reused here
        $schema = json_decode('{
            "type": "object",
            "properties": {
                "description": {
                    "type": "string"
                }
            },
            "required": [
                "description"
            ],
            "additionalProperties": false
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
