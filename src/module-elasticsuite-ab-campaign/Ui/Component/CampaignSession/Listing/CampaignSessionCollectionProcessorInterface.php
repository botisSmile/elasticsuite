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
 * @license   Licensed to Smile-SA. All rights reserved. No warranty, explicit or implicit, provided.
 *            Unauthorized copying of this file, via any medium, is strictly prohibited.
 */

namespace Smile\ElasticsuiteAbCampaign\Ui\Component\CampaignSession\Listing;

use Smile\ElasticsuiteAbCampaign\Model\ResourceModel\CampaignSession\Collection as CampaignSessionCollection;

/**
 * Optimizer collection processor interface.
 *
 * @SuppressWarnings(PHPMD.LongClassName)
 *
 * @category Smile
 * @package  Smile\ElasticsuiteAbCampaign
 * @author   Botis <botis@smile.fr>
 */
interface CampaignSessionCollectionProcessorInterface
{
    /**
     * Process optimizer collection.
     *
     * @param CampaignSessionCollection $collection Campaign session collection
     * @return void
     */
    public function process(CampaignSessionCollection $collection);
}
