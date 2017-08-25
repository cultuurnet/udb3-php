<?php

namespace CultuurNet\UDB3\Search;

use CultuurNet\UDB3\Offer\IriOfferIdentifierFactoryInterface;
use CultuurNet\UDB3\Offer\OfferIdentifierCollection;
use Guzzle\Http\QueryString;
use GuzzleHttp\Psr7\Request;
use Http\Client\HttpClient;
use Psr\Http\Message\UriInterface;
use ValueObjects\Number\Integer;
use ValueObjects\Web\Url;

class Sapi3SearchService implements SearchServiceInterface
{
    /**
     * @var UriInterface
     */
    private $searchLocation;

    /**
     * @var HttpClient
     */
    private $httpClient;

    /**
     * @var IriOfferIdentifierFactoryInterface
     */
    private $offerIdentifier;

    /**
     * @param UriInterface $searchLocation
     * @param HttpClient $httpClient
     */
    public function __construct(
        UriInterface $searchLocation,
        HttpClient $httpClient,
        IriOfferIdentifierFactoryInterface $offerIdentifier
    ) {
        $this->searchLocation = $searchLocation;
        $this->httpClient = $httpClient;
        $this->offerIdentifier = $offerIdentifier;
    }

    public function search($query, $limit = 30, $start = 0, $sort = null)
    {
        $query = QueryString::fromString('q=' . $query . '&start=' . $start . '&limit=' . $limit);
        $offerQueryUri = $this->searchLocation->withQuery((string) $query);

        $offerRequest = new Request('GET', $offerQueryUri);

        $searchResponseData = json_decode($this->httpClient
            ->sendRequest($offerRequest)
            ->getBody()
            ->getContents());

        $offerIds = array_reduce(
            $searchResponseData->{'member'},
            function (OfferIdentifierCollection $offerIds, $item) {
                return $offerIds->with(
                    $this->offerIdentifier->fromIri(Url::fromNative($item->{'@id'}))
                );
            },
            new OfferIdentifierCollection()
        );

        return new Results($offerIds, new Integer($searchResponseData->{'totalItems'}));
    }
}
