<?php
/**
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Smile ElasticSuite to newer
 * versions in the future.
 *
 * @category  Smile
 * @package   Smile\ElasticsuiteAbCampaign
 * @author    Botis <botis@smile.fr>
 * @copyright 2021 Smile
 * @license   Open Software License ("OSL") v. 3.0
 */
namespace Smile\ElasticsuiteAbCampaign\Model\Search\Campaign;

use Smile\ElasticsuiteAnalytics\Model\Report\QueryProviderInterface;
use Smile\ElasticsuiteCore\Search\Request\QueryInterface;
use Smile\ElasticsuiteCore\Search\Request\Query\QueryFactory;

/**
 * Date filter query provider
 *
 * @category Smile
 * @package  Smile\ElasticsuiteAnalytics
 * @author   Botis <botis@smile.fr>
 */
class DateFilterQueryProvider implements QueryProviderInterface
{
    /**
     * @var QueryFactory
     */
    private $queryFactory;

    /**
     * @var array
     */
    private $range = [];

    /**
     * DateFilterQueryProvider constructor.
     *
     * @param QueryFactory $queryFactory Query factory.
     */
    public function __construct(QueryFactory $queryFactory)
    {
        $this->queryFactory = $queryFactory;
    }

    /**
     * Set range date.
     *
     * @param string $fromtDate From date
     * @param string $toDate    To date
     */
    public function setRange(string $fromtDate, string $toDate)
    {
        $this->range = [
            'from' => $fromtDate,
            'to' => $toDate,
        ];
    }

    /**
     * {@inheritDoc}
     */
    public function getQuery(): QueryInterface
    {
        return  $this->queryFactory->create(
            QueryInterface::TYPE_BOOL,
            [
                'must' => [
                    $this->queryFactory->create(
                        QueryInterface::TYPE_RANGE,
                        [
                            'field' => 'start_date',
                            'bounds' => [
                                'gte' => $this->range['from'],
                                'lte' => $this->range['to'],
                            ],
                        ]
                    ),
                ],
            ]
        );
    }
}
