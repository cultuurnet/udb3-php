<?php
namespace CultuurNet\UDB3\ReadModel\Index\Doctrine;

use CultuurNet\UDB3\Dashboard\DashboardItemLookupServiceInterface;
use CultuurNet\UDB3\Offer\IriOfferIdentifier;
use CultuurNet\UDB3\Offer\OfferIdentifierCollection;
use CultuurNet\UDB3\Offer\OfferType;
use CultuurNet\UDB3\Organizer\ReadModel\Lookup\OrganizerLookupServiceInterface;
use CultuurNet\UDB3\Place\ReadModel\Lookup\PlaceLookupServiceInterface;
use CultuurNet\UDB3\ReadModel\Index\EntityIriGeneratorFactoryInterface;
use CultuurNet\UDB3\ReadModel\Index\EntityType;
use CultuurNet\UDB3\ReadModel\Index\RepositoryInterface;
use CultuurNet\UDB3\Search\Results;
use DateTimeInterface;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Query\Expression\CompositeExpression;
use Doctrine\DBAL\Query\QueryBuilder;
use ValueObjects\Number\Integer;
use ValueObjects\Number\Natural;
use ValueObjects\String\String as StringLiteral;
use ValueObjects\Web\Domain;
use ValueObjects\Web\Url;

class DBALRepository implements RepositoryInterface, PlaceLookupServiceInterface, OrganizerLookupServiceInterface, DashboardItemLookupServiceInterface
{
    /**
     * @var Connection
     */
    protected $connection;

    /**
     * @var StringLiteral
     */
    protected $tableName;

    /**
     * @var EntityIriGeneratorFactoryInterface
     */
    protected $iriGeneratorFactory;

    /**
     * @param Connection $connection
     * @param StringLiteral $tableName
     * @param EntityIriGeneratorFactoryInterface $iriGeneratorFactory
     */
    public function __construct(
        Connection $connection,
        StringLiteral $tableName,
        EntityIriGeneratorFactoryInterface $iriGeneratorFactory
    ) {
        $this->connection = $connection;
        $this->tableName = $tableName;
        $this->iriGeneratorFactory = $iriGeneratorFactory;
    }

    /**
     * @inheritdoc
     */
    public function updateIndex(
        $id,
        EntityType $entityType,
        $userId,
        $name,
        $postalCode,
        Domain $owningDomain,
        DateTimeInterface $created = null
    ) {
        $this->connection->beginTransaction();

        try {
            $iriGenerator = $this->iriGeneratorFactory->forEntityType($entityType);
            $iri = $iriGenerator->iri($id);

            if ($this->itemExists($id, $entityType)) {
                $q = $this->connection->createQueryBuilder();
                $q->update($this->tableName->toNative())
                    ->where($this->matchesIdAndEntityType())
                    ->set('uid', ':uid')
                    ->set('title', ':title')
                    ->set('zip', ':zip')
                    ->set('owning_domain', ':owning_domain')
                    ->set('entity_iri', ':entity_iri');

                if ($created instanceof DateTimeInterface) {
                    $q->set('created', ':created');
                }

                $this->setIdAndEntityType($q, $id, $entityType);
                $this->setValues($q, $userId, $name, $postalCode, $owningDomain, $created);
                $q->setParameter('entity_iri', $iri);

                $q->execute();
            } else {
                if (!$created instanceof DateTimeInterface) {
                    $created = new \DateTimeImmutable('now');
                }

                $q = $this->connection->createQueryBuilder();
                $q->insert($this->tableName->toNative())
                    ->values(
                        [
                            'entity_id' => ':entity_id',
                            'entity_type' => ':entity_type',
                            'entity_iri' => ':entity_iri',
                            'uid' => ':uid',
                            'title' => ':title',
                            'zip' => ':zip',
                            'created' => ':created',
                            'updated' => ':created',
                            'owning_domain' => ':owning_domain'
                        ]
                    );

                $this->setIdAndEntityType($q, $id, $entityType);
                $this->setValues(
                    $q,
                    $userId,
                    $name,
                    $postalCode,
                    $owningDomain,
                    $created
                );
                $q->setParameter('entity_iri', $iri);

                $q->execute();
            }
        } catch (\Exception $e) {
            $this->connection->rollBack();

            throw $e;
        }

        $this->connection->commit();
    }

    /**
     * @param QueryBuilder $q
     * @param string $userId
     * @param string $name
     * @param string $postalCode
     * @param Domain $owningDomain
     * @param DateTimeInterface $created
     */
    private function setValues(
        QueryBuilder $q,
        $userId,
        $name,
        $postalCode,
        Domain $owningDomain,
        DateTimeInterface $created = null
    ) {
        $q->setParameter('uid', $userId);
        $q->setParameter('title', $name);
        $q->setParameter('zip', $postalCode);
        $q->setParameter('owning_domain', $owningDomain->toNative());
        if ($created instanceof DateTimeInterface) {
            $q->setParameter('created', $created->getTimestamp());
        }
    }

    /**
     * Returns the WHERE predicates for matching the id and entity_type columns.
     *
     * @return \Doctrine\DBAL\Query\Expression\CompositeExpression
     */
    private function matchesIdAndEntityType()
    {
        $expr = $this->connection->getExpressionBuilder();

        return $expr->andX(
            $expr->eq('entity_id', ':entity_id'),
            $expr->eq('entity_type', ':entity_type')
        );
    }

    /**
     * @param QueryBuilder $q
     * @param string $id
     * @param EntityType $entityType
     */
    private function setIdAndEntityType(
        QueryBuilder $q,
        $id,
        EntityType $entityType
    ) {
        $q->setParameter('entity_id', $id);
        $q->setParameter('entity_type', $entityType->toNative());
    }

    /**
     * @param $id
     * @param EntityType $entityType
     * @return bool
     */
    private function itemExists($id, EntityType $entityType)
    {
        $q = $this->connection->createQueryBuilder();

        $q->select('1')->from($this->tableName->toNative())->where(
            $this->matchesIdAndEntityType()
        );

        $this->setIdAndEntityType($q, $id, $entityType);

        $result = $q->execute();
        $items = $result->fetchAll();

        return count($items) > 0;
    }

    /**
     * @inheritdoc
     */
    public function deleteIndex($id, EntityType $entityType)
    {
        $q = $this->connection->createQueryBuilder();

        $q->delete($this->tableName->toNative())
            ->where($this->matchesIdAndEntityType());

        $this->setIdAndEntityType($q, $id, $entityType);

        $q->execute();
    }

    /**
     * @inheritdoc
     */
    public function findPlacesByPostalCode($postalCode)
    {
        $q = $this->connection->createQueryBuilder();
        $expr = $q->expr();

        $q->select('entity_id')
            ->from($this->tableName->toNative())
            ->where(
                $expr->andX(
                    $expr->eq('entity_type', ':entity_type'),
                    $expr->eq('zip', ':zip')
                )
            );

        $q->setParameter('entity_type', EntityType::PLACE()->toNative());
        $q->setParameter('zip', $postalCode);

        $results = $q->execute();

        return $results->fetchAll(\PDO::FETCH_COLUMN);
    }

    /**
     * @inheritdoc
     */
    public function findOrganizersByPartOfTitle($part)
    {
        $q = $this->connection->createQueryBuilder();
        $expr = $q->expr();

        $q->select('entity_id')
            ->from($this->tableName->toNative())
            ->where(
                $expr->andX(
                    $expr->eq('entity_type', ':entity_type'),
                    $expr->like('title', ':title')
                )
            );

        $q->setParameter('entity_type', EntityType::ORGANIZER()->toNative());
        $q->setParameter('title', '%' . $part . '%');

        $results = $q->execute();

        return $results->fetchAll(\PDO::FETCH_COLUMN);
    }

    public function findByUser($userId, Natural $limit, Natural $start)
    {
        $expr = $this->connection->getExpressionBuilder();
        $itemIsOwnedByUser = $expr->andX(
            $expr->eq('uid', ':user_id'),
            $expr->orX(
                $expr->eq('entity_type', '"event"'),
                $expr->eq('entity_type', '"place"')
            )
        );

        return $this->getPagedDashboardItems(
            $userId,
            $limit,
            $start,
            $itemIsOwnedByUser
        );
    }

    public function findByUserForDomain(
        $userId,
        Natural $limit,
        Natural $start,
        Domain $owningDomain
    ) {
        $expr = $this->connection->getExpressionBuilder();
        $ownedByUserForDomain = $expr->andX(
            $expr->eq('uid', ':user_id'),
            $expr->eq('owning_domain', ':owning_domain'),
            $expr->orX(
                $expr->eq('entity_type', '"event"'),
                $expr->eq('entity_type', '"place"')
            )
        );
        $parameters = ['owning_domain' => $owningDomain->toNative()];

        return $this->getPagedDashboardItems(
            $userId,
            $limit,
            $start,
            $ownedByUserForDomain,
            $parameters
        );
    }

    private function getPagedDashboardItems(
        $userId,
        Natural $limit,
        Natural $start,
        CompositeExpression $filterExpression,
        $parameters = []
    ) {
        $queryBuilder = $this->connection->createQueryBuilder();
        $queryBuilder->select('entity_id', 'entity_iri', 'entity_type')
            ->from($this->tableName->toNative())
            ->where($filterExpression)
            ->orderBy('updated', 'DESC')
            ->setMaxResults($limit->toNative())
            ->setFirstResult($start->toNative());

        $queryBuilder->setParameter('user_id', $userId);
        foreach ($parameters as $param => $value) {
            $queryBuilder->setParameter($param, $value);
        }

        $parameters = $queryBuilder->getParameters();

        $results = $queryBuilder->execute();
        $offerIdentifierArray = array_map(
            function ($resultRow) {
                $offerIdentifier = new IriOfferIdentifier(
                    Url::fromNative($resultRow['entity_iri']),
                    $resultRow['entity_id'],
                    OfferType::fromNative(ucfirst($resultRow['entity_type']))
                );

                return $offerIdentifier;
            },
            $results->fetchAll(\PDO::FETCH_ASSOC)
        );

        $itemCount = count($offerIdentifierArray);
        // We can skip an additional query to determine to total items count
        // if the amount of rows on the first page does not reach the limit.
        $onFirstPage = $queryBuilder->getFirstResult() === 0;
        $hasSinglePage = $itemCount < $queryBuilder->getMaxResults();
        if ($onFirstPage && $hasSinglePage) {
            $totalItems = $itemCount;
        } else {
            $q = $this->connection->createQueryBuilder();

            $totalItems = $q->resetQueryParts()->select('COUNT(*) AS total')
                ->from($this->tableName->toNative())
                ->where($filterExpression)
                ->setParameters($parameters)
                ->execute()
                ->fetchColumn(0);
        }

        return new Results(
            OfferIdentifierCollection::fromArray($offerIdentifierArray),
            new Integer($totalItems)
        );
    }

    public function setUpdateDate($id, DateTimeInterface $updated)
    {
        $queryBuilder = $this->connection->createQueryBuilder();
        $expr = $this->connection->getExpressionBuilder();

        $queryBuilder
            ->update($this->tableName->toNative())
            ->where(
                $expr->andX(
                    $expr->eq('entity_id', ':entity_id')
                )
            )
            ->set('updated', $updated->getTimestamp())
            ->setParameter('entity_id', $id)
            ->execute();
    }
}
