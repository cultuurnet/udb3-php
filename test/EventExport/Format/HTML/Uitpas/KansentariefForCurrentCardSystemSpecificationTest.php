<?php
/**
 * @file
 */

namespace CultuurNet\UDB3\EventExport\Format\HTML\Uitpas;

use CultureFeed_Uitpas_DistributionKey;
use CultureFeed_Uitpas_DistributionKey_Condition as Condition;

class KansentariefForCurrentCardSystemSpecificationTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var KansentariefForCurrentCardSystemSpecification
     */
    protected $specification;

    public function setUp()
    {
        $this->specification =
            new KansentariefForCurrentCardSystemSpecification();
    }

    public function satisfyingDistributionKeysProvider()
    {
        $key = new CultureFeed_Uitpas_DistributionKey();
        $key->tariff = '1.0';
        $key->conditions[] = new Condition();


        $data = [
            [
                $this->buildKey(
                    '1.0',
                    [
                        $this->buildCondition(
                            Condition::DEFINITION_KANSARM,
                            Condition::OPERATOR_IN,
                            Condition::VALUE_MY_CARDSYSTEM
                        ),
                    ]
                )
            ],
            [
                $this->buildKey(
                    '0.0',
                    [
                        $this->buildCondition(
                            Condition::DEFINITION_KANSARM,
                            Condition::OPERATOR_IN,
                            Condition::VALUE_MY_CARDSYSTEM
                        ),
                    ]
                )
            ],
            [
                $this->buildKey(
                    '1.0',
                    [
                        $this->buildCondition(
                            Condition::DEFINITION_KANSARM,
                            Condition::OPERATOR_IN,
                            Condition::VALUE_MY_CARDSYSTEM
                        ),
                        $this->buildCondition(
                            Condition::DEFINITION_PRICE,
                            Condition::OPERATOR_LESS_THAN,
                            7
                        ),
                    ]
                ),
            ]
        ];

        return $data;
    }

    /**
     * @param string $tariff
     * @param Condition[] $conditions
     * @return CultureFeed_Uitpas_DistributionKey
     */
    private function buildKey($tariff, $conditions)
    {
        $key = new CultureFeed_Uitpas_DistributionKey();
        $key->tariff = $tariff;
        $key->conditions = $conditions;

        return $key;
    }

    /**
     * @param string $definition
     * @param string $operator
     * @param string $value
     * @return Condition
     */
    private function buildCondition($definition, $operator, $value)
    {
        $condition = new Condition();
        $condition->definition = $definition;
        $condition->operator = $operator;
        $condition->value = $value;

        return $condition;
    }

    /**
     * @test
     * @dataProvider satisfyingDistributionKeysProvider
     * @param CultureFeed_Uitpas_DistributionKey $key
     */
    public function it_is_satisfied_by_one_kansarm_distribution_key_condition(
        CultureFeed_Uitpas_DistributionKey $key
    ) {
        $this->assertTrue(
            $this->specification->isSatisfiedBy($key)
        );
    }

    public function nonSatisfyingDistributionKeysProvider()
    {
        $data = [
            [
                $this->buildKey(
                    '1.0',
                    [
                        $this->buildCondition(
                            Condition::DEFINITION_KANSARM,
                            Condition::OPERATOR_IN,
                            Condition::VALUE_AT_LEAST_ONE_CARDSYSTEM
                        ),
                    ]
                )
            ],
            [
                $this->buildKey(
                    '0.0',
                    [
                        $this->buildCondition(
                            Condition::DEFINITION_KANSARM,
                            Condition::OPERATOR_IN,
                            Condition::VALUE_AT_LEAST_ONE_CARDSYSTEM
                        ),
                    ]
                )
            ],
            [
                $this->buildKey(
                    '0.0',
                    [
                        $this->buildCondition(
                            Condition::DEFINITION_KANSARM,
                            Condition::OPERATOR_IN,
                            Condition::VALUE_AT_LEAST_ONE_CARDSYSTEM
                        ),
                        $this->buildCondition(
                            Condition::DEFINITION_PRICE,
                            Condition::OPERATOR_LESS_THAN,
                            '7'
                        ),
                    ]
                )
            ],
        ];

        return $data;
    }

    /**
     * @test
     * @dataProvider nonSatisfyingDistributionKeysProvider
     * @param CultureFeed_Uitpas_DistributionKey $key
     */
    public function it_is_not_satisfied_by_other_distribution_key_conditions(
        CultureFeed_Uitpas_DistributionKey $key
    ) {
        $this->assertFalse(
            $this->specification->isSatisfiedBy($key)
        );
    }
}
