<?php

namespace CultuurNet\UDB3\MyOrganizers\ReadModel\Doctrine;

use CultuurNet\UDB3\Iri\IriGeneratorInterface;
use CultuurNet\UDB3\MyOrganizers\MyOrganizersLookupServiceInterface;
use CultuurNet\UDB3\MyOrganizers\PartOfCollection;
use Doctrine\DBAL\Connection;
use PDO;
use ValueObjects\Number\Natural;
use ValueObjects\StringLiteral\StringLiteral;

class DBALLookupService implements MyOrganizersLookupServiceInterface
{
    use DBALHelperTrait;

    /** @var StringLiteral */
    private $tableName;

    /**
     * @var IriGeneratorInterface
     */
    private $iriGenerator;

    /**
     * @param Connection $connection
     * @param StringLiteral $tableName
     */
    public function __construct(
        Connection $connection,
        StringLiteral $tableName,
        IriGeneratorInterface $iriGenerator
    ) {
        $this->connection = $connection;
        $this->tableName = $tableName;
        $this->iriGenerator = $iriGenerator;
    }

    public function itemsOwnedByUser(
        string $userId,
        Natural $limit,
        Natural $start
    ): PartOfCollection {
        $queryBuilder = $this->connection->createQueryBuilder();

        $expr = $this->connection->getExpressionBuilder();
        $itemIsOwnedByUser = $expr->eq(Columns::UID, $this->parameter(Columns::UID));

        $queryBuilder->select(Columns::ID)
            ->from($this->tableName->toNative())
            ->where($itemIsOwnedByUser)
            ->orderBy(Columns::UPDATED, 'DESC')
            ->addOrderBy(Columns::ID, 'ASC')
            ->setMaxResults($limit->toNative())
            ->setFirstResult($start->toNative());

        $queryBuilder->setParameter(Columns::UID, $userId);

        $parameters = $queryBuilder->getParameters();

        $results = $queryBuilder->execute();

        // @todo transform @id here to an object that has a full URL
        // when json-encoded
        $organizers = array_map(
            function ($resultRow) {
                return [
                    '@id' => $this->iriGenerator->iri($resultRow[Columns::ID]),
                    '@type' => 'Organizer',
                ];
            },
            $results->fetchAll(PDO::FETCH_ASSOC)
        );

        $q = $this->connection->createQueryBuilder();

        $totalItems = $q->resetQueryParts()->select('COUNT(*) AS total')
            ->from($this->tableName->toNative())
            ->where($itemIsOwnedByUser)
            ->setParameters($parameters)
            ->execute()
            ->fetchColumn(0);

        return new PartOfCollection($organizers, new Natural($totalItems));
    }
}
