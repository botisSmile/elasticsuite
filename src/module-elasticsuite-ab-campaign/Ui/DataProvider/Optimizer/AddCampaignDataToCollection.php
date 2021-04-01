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

namespace Smile\ElasticsuiteAbCampaign\Ui\DataProvider\Optimizer;

use Magento\Framework\Data\Collection;
use Magento\Ui\DataProvider\AddFieldToCollectionInterface;
use Smile\ElasticsuiteCatalogOptimizer\Model\ResourceModel\Optimizer\Collection as OptimizerCollection;
use Smile\ElasticsuiteAbCampaign\Model\ResourceModel\Campaign\Optimizer as CampaignOptimizerResource;

/**
 * Class AddCampaignDataToCollection
 *
 * @category Smile
 * @package  Smile\ElasticsuiteAbCampaign
 * @author   Pierre Le Maguer <pierre.lemaguer@smile.fr>
 */
class AddCampaignDataToCollection implements AddFieldToCollectionInterface
{
    /**
     * @var CampaignOptimizerResource
     */
    private $campaignOptimizerResource;

    /**
     * AddCampaignDataToCollection constructor
     *
     * @param CampaignOptimizerResource $campaignOptimizerResource Campaign optimizer resource
     */
    public function __construct(CampaignOptimizerResource $campaignOptimizerResource)
    {
        $this->campaignOptimizerResource = $campaignOptimizerResource;
    }

    /**
     * {@inheritdoc}
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function addField(Collection $collection, $field, $condition = null)
    {
        /** @var OptimizerCollection $collection */
        $this->campaignOptimizerResource->addCampaignDataToOptimizerCollection($collection);
    }
}
