<?php
/**
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Smile ElasticSuite to newer
 * versions in the future.
 *
 * @category  Smile
 * @package   Smile\ElasticsuiteAbCampaign
 * @author    Pierre LE MAGUER <pierre.lemaguer@smile.fr>
 * @copyright 2021 Smile
 * @license   Licensed to Smile-SA. All rights reserved. No warranty, explicit or implicit, provided.
 *            Unauthorized copying of this file, via any medium, is strictly prohibited.
 */

namespace Smile\ElasticsuiteAbCampaign\Plugin\Adminhtml;

use Smile\ElasticsuiteAbCampaign\Api\Campaign\OptimizerManagerInterface;
use Smile\ElasticsuiteCatalogOptimizer\Ui\Component\Listing\Column\OptimizerActions as Subject;

/**
 * Plugin OptimizerUiListingActionsPlugin: remove column actions for optimizers with running campaign.
 *
 * @category Smile
 * @package  Smile\ElasticsuiteAbCampaign
 * @author   Pierre Le Maguer <pierre.lemaguer@smile.fr>
 */
class OptimizerUiListingActionsPlugin
{
    /**
     * @var OptimizerManagerInterface
     */
    private $campaignOptimizerManager;

    /**
     * OptimizerUiListingActionsPlugin constructor.
     *
     * @param OptimizerManagerInterface $campaignOptimizerManager Campaign optimizer manager
     */
    public function __construct(OptimizerManagerInterface $campaignOptimizerManager)
    {
        $this->campaignOptimizerManager = $campaignOptimizerManager;
    }

    /**
     * Remove column actions for optimizers with running campaign.
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     *
     * @param Subject $subject    Optimizer actions
     * @param array   $dataSource Datasource
     * @return array
     */
    public function afterPrepareDataSource(Subject $subject, array $dataSource): array
    {
        if (isset($dataSource['data']['items'])) {
            $columnName = $subject->getData('name');
            $optimizerIds = array_column($dataSource['data']['items'], 'optimizer_id');
            $optimizerIdsToRestrain = $this->campaignOptimizerManager->extractOptimizerIdsToRestrain($optimizerIds);
            foreach ($dataSource['data']['items'] as &$item) {
                if (isset($item['optimizer_id'])) {
                    $optimizerId = $item['optimizer_id'];
                    if (in_array($optimizerId, $optimizerIdsToRestrain)) {
                        unset($item[$columnName]['edit']);
                        unset($item[$columnName]['delete']);
                    }
                }
            }
        }

        return $dataSource;
    }
}
