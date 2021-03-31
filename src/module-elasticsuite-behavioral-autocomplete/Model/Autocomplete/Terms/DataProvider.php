<?php
/**
 * DISCLAIMER
 * Do not edit or add to this file if you wish to upgrade Smile ElasticSuite to newer
 * versions in the future.
 *
 * @category  Smile
 * @package   Smile\ElasticsuiteBehavioralAutocomplete
 * @author    Romain Ruaud <romain.ruaud@smile.fr>
 * @copyright 2021 Smile
 * @license   Licensed to Smile-SA. All rights reserved. No warranty, explicit or implicit, provided.
 *            Unauthorized copying of this file, via any medium, is strictly prohibited.
 */

namespace Smile\ElasticsuiteBehavioralAutocomplete\Model\Autocomplete\Terms;

use Magento\Search\Model\Autocomplete\DataProviderInterface;
use Magento\Search\Model\Autocomplete\ItemFactory;
use Magento\Search\Model\QueryFactory;
use Smile\ElasticsuiteBehavioralAutocomplete\Api\TrendingQueryServiceInterface;
use Smile\ElasticsuiteCore\Helper\Autocomplete as ConfigurationHelper;

/**
 * Popular search terms data provider.
 * This one is based on trending search terms.
 *
 * @category Smile
 * @package  Smile\ElasticsuiteBehavioralAutocomplete
 * @author   Romain Ruaud <romain.ruaud@smile.fr>
 */
class DataProvider extends \Smile\ElasticsuiteCore\Model\Autocomplete\Terms\DataProvider implements DataProviderInterface
{
    /**
     * @var \Magento\Search\Model\Autocomplete\Item[]|null
     */
    private $items;

    /**
     * @var string
     */
    private $type;

    /**
     * @var TrendingQueryServiceInterface
     */
    private $service;

    /**
     * @var boolean
     */
    private $allowFallback;

    /**
     * Constructor.
     *
     * @param TrendingQueryServiceInterface $service             Service
     * @param QueryFactory                  $queryFactory        Search query text factory.
     * @param ItemFactory                   $itemFactory         Suggest terms item facory.
     * @param ConfigurationHelper           $configurationHelper Autocomplete configuration helper.
     * @param string                        $type                Autocomplete items type.
     * @param boolean                       $allowFallback       Allow fallback to default data provider using the database.
     * @SuppressWarnings(PHPMD.BooleanArgumentFlag)
     */
    public function __construct(
        TrendingQueryServiceInterface $service,
        QueryFactory $queryFactory,
        ItemFactory $itemFactory,
        ConfigurationHelper $configurationHelper,
        $type = self::AUTOCOMPLETE_TYPE,
        $allowFallback = true
    ) {
        $this->service             = $service;
        $this->itemFactory         = $itemFactory;
        $this->configurationHelper = $configurationHelper;
        $this->type                = $type;
        $this->allowFallback       = $allowFallback;

        parent::__construct($queryFactory, $itemFactory, $configurationHelper, $type);
    }

    /**
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * {@inheritdoc}
     */
    public function getItems()
    {
        if ($this->items === null) {
            $this->items = [];

            try {
                $collection = $this->service->get(null, $this->getResultsPageSize());
                if ($this->configurationHelper->isEnabled($this->getType())) {
                    foreach ($collection as $item) {
                        $resultItem    = $this->itemFactory->create([
                            'title'       => $item->getQueryText(),
                            'num_results' => $item->getNumResults(),
                            'type'        => $this->getType(),
                        ]);
                        $this->items[] = $resultItem;
                    }
                }
            } catch (\Exception $exception) {
                $this->items = [];
            }
        }

        if (empty($this->items) && $this->allowFallback) {
            $this->items = parent::getItems();
        }

        return $this->items;
    }

    /**
     * Retrieve number of products to display in autocomplete results
     *
     * @return int
     */
    private function getResultsPageSize()
    {
        return $this->configurationHelper->getMaxSize($this->getType());
    }
}
