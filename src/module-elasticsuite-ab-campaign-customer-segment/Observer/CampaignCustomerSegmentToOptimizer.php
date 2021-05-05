<?php
/**
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Smile ElasticSuite to newer
 * versions in the future.
 *
 * @category  Smile
 * @package   Smile\ElasticsuiteAbCampaignCustomerSegment
 * @author    Pierre LE MAGUER <pierre.lemaguer@smile.fr>
 * @copyright 2021 Smile
 * @license   Licensed to Smile-SA. All rights reserved. No warranty, explicit or implicit, provided.
 *            Unauthorized copying of this file, via any medium, is strictly prohibited.
 */

namespace Smile\ElasticsuiteAbCampaignCustomerSegment\Observer;

use Magento\Framework\DataObject;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Smile\ElasticsuiteAbCampaign\Model\Campaign;

/**
 * Observer CampaignCustomerSegmentToOptimizer: Add customer segment when adding campaign context to optimizer.
 *
 * @category Smile
 * @package  Smile\ElasticsuiteAbCampaignCustomerSegment
 * @author   Pierre Le Maguer <pierre.lemaguer@smile.fr>
 */
class CampaignCustomerSegmentToOptimizer implements ObserverInterface
{
    /**
     * Add customer segment when adding campaign context to optimizer.
     *
     * {@inheritDoc}
     */
    public function execute(Observer $observer)
    {
        /** @var Campaign $campaign */
        $campaign         = $observer->getData('campaign');
        /** @var DataObject $optimizerContext */
        $optimizerContext = $observer->getData('optimizer_context');
        if ($campaign->getData('customer_segment')) {
            $customerSegmentData = [];
            $campaignCustomerSegment = $campaign->getData('customer_segment');
            $customerSegmentData['segment_ids'] = [];
            if (isset($campaignCustomerSegment['segment_ids']) && is_array($campaignCustomerSegment['segment_ids'])) {
                foreach ($campaignCustomerSegment['segment_ids'] as $customerSegmentId) {
                    $customerSegmentData['segment_ids'][] = ['id' => $customerSegmentId];
                }
            }
            $customerSegmentData['apply_to'] = $campaignCustomerSegment['apply_to'] ?? ((bool) $customerSegmentData['segment_ids']);
            $optimizerContext->setData('customer_segment', $customerSegmentData);
        }
    }
}
