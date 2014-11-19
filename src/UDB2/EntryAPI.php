<?php
/**
 * @file
 */

namespace CultuurNet\UDB3\UDB2;


use CultuurNet\Auth\Guzzle\OAuthProtectedService;
use CultuurNet\UDB3\Keyword;
use CultuurNet\UDB3\Language;
use Guzzle\Http\Message\EntityEnclosingRequest;

class EntryAPI extends OAuthProtectedService
{
    const TRANSLATION_MODIFIED = 'TranslationModified';

    const TRANSLATION_CREATED = 'TranslationCreated';

    const KEYWORD_WITHDRAWN = 'KeywordWithdrawn';

    const KEYWORD_PRIVATE = 'PrivateKeyword';

    protected function eventTranslationPath($eventId)
    {
        return "event/{$eventId}/translations";
    }

    protected function eventKeywordsPath($eventId)
    {
        return "event/{$eventId}/keywords";
    }

    /**
     * @param string $eventId
     * @param Language $language
     * @param string $title
     *
     * @return Rsp
     */
    public function translateEventTitle($eventId, Language $language, $title)
    {
        return $this->translate($eventId, $language, ['title' => $title]);
    }

    /**
     * @param string $eventId
     * @param Language $language
     * @param string $description
     *
     * @return Rsp
     */
    public function translateEventDescription(
        $eventId,
        Language $language,
        $description
    ) {
        return $this->translate(
            $eventId,
            $language,
            [
                'longdescription' => $description,
                'shortdescription' => iconv_substr($description, 0, 400),
            ]
        );
    }

    private function translate($eventId, Language $language, $fields)
    {
        $request = $this->getClient()->post(
            $this->eventTranslationPath($eventId),
            null,
            [
                'lang' => (string)$language,
            ] + $fields
        );

        $response = $request->send();

        $rsp = Rsp::fromResponseBody($response->getBody(true));

        $this->guardTranslationResponseIsSuccessful($rsp);

        return $rsp;
    }

    /**
     * @param string $eventId
     * @param Keyword $keyword
     * @return Rsp
     * @throws UnexpectedKeywordDeleteErrorException
     */
    public function deleteKeyword($eventId, Keyword $keyword)
    {
        /** @var EntityEnclosingRequest $request */
        $request = $this->getClient()->delete(
            $this->eventKeywordsPath($eventId)
        );

        $request->getPostFields()->add('keyword', (string)$keyword);

        $response = $request->send();

        $rsp = Rsp::fromResponseBody($response->getBody(true));

        $this->guardDeleteKeywordResponseIsSuccessful($rsp);

        return $rsp;
    }

    private function guardDeleteKeywordResponseIsSuccessful(Rsp $rsp)
    {
        if ($rsp->getCode() !== self::KEYWORD_WITHDRAWN) {
            throw new UnexpectedKeywordDeleteErrorException($rsp);
        }
    }

    private function guardTranslationResponseIsSuccessful(Rsp $rsp)
    {
        $validCodes = [
            self::TRANSLATION_CREATED,
            self::TRANSLATION_MODIFIED
        ];
        if (!in_array($rsp->getCode(), $validCodes)) {
            throw new UnexpectedTranslationErrorException($rsp);
        }
    }
}
