<?php
/**
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this module to newer
 * versions in the future.
 *
 *
 * @category  Smile
 * @package   Smile\ElasticsuiteAbCampaign
 * @author    Botis <botis@smile.fr>
 * @copyright 2021 Smile
 * @license   Licensed to Smile-SA. All rights reserved. No warranty, explicit or implicit, provided.
 *            Unauthorized copying of this file, via any medium, is strictly prohibited.
 */

namespace Smile\ElasticsuiteAbCampaign\Ui\Component\CampaignSession\Listing;

use Magento\Framework\App\RequestInterface;
use Smile\ElasticsuiteAbCampaign\Api\Data\CampaignSessionInterface;
use Smile\ElasticsuiteAbCampaign\Model\ResourceModel\CampaignSession\Collection as CampaignSessionCollection;

/**
 * Filter optimizer collection by campaign.
 *
 * @category Smile
 * @package  Smile\ElasticsuiteAbCampaign
 * @author   Botis <botis@smile.fr>
 */
class FilterCollectionByCampaign implements CampaignSessionCollectionProcessorInterface
{
    /**
     * @var RequestInterface
     */
    private $request;

    /**
     * FilterCollectionByCampaign Constructor.
     *
     * @param RequestInterface $request Request
     */
    public function __construct(RequestInterface $request)
    {
        $this->request = $request;
    }

    /**
     * {@inheritdoc}
     */
    public function process(CampaignSessionCollection $collection)
    {
        $campaignId = (int) $this->request->getParam('campaign_id');
        if ($campaignId) {
            $collection->getSelect()
                ->joinLeft(
                    ['campaign_session' => $collection->getTable(CampaignSessionInterface::TABLE_NAME)],
                    'main_table.campaign_id = campaign_session.campaign_id',
                    []
                )
                ->where('campaign_session.campaign_id = ?', $campaignId);
        }
    }
}
