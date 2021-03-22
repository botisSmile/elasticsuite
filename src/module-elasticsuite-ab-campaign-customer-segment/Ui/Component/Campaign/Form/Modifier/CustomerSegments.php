<?php
/**
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this module to newer
 * versions in the future.
 *
 *
 * @category  Smile
 * @package   Smile\ElasticsuiteAbCampaignCustomerSegment
 * @author    Pierre Le Maguer <pierre.lemaguer@smile.fr>
 * @copyright 2021 Smile
 * @license   Licensed to Smile-SA. All rights reserved. No warranty, explicit or implicit, provided.
 *            Unauthorized copying of this file, via any medium, is strictly prohibited.
 */

namespace Smile\ElasticsuiteAbCampaignCustomerSegment\Ui\Component\Campaign\Form\Modifier;

use Magento\Config\Model\Config\Source\Yesno;
use Magento\CustomerSegment\Helper\Data as CustomerSegmentHelper;
use Magento\CustomerSegment\Model\ResourceModel\Grid\Collection;
use Magento\CustomerSegment\Model\ResourceModel\Grid\CollectionFactory as CustomerSegmentCollectionFactory;
use Magento\Store\Model\ResourceModel\Website\Collection as WebsiteCollection;
use Magento\Store\Model\ResourceModel\Website\CollectionFactory as WebsiteCollectionFactory;
use Magento\Ui\DataProvider\Modifier\ModifierInterface;
use Smile\ElasticsuiteAbCampaign\Model\Context\Adminhtml\Campaign as CampaignContext;

/**
 * Campaign Ui Component Modifier. Used to populate customer segments.
 *
 * @category Smile
 * @package  Smile\ElasticsuiteAbCampaignCustomerSegment
 * @author   Pierre Le Maguer <pierre.lemaguer@smile.fr>
 */
class CustomerSegments implements ModifierInterface
{
    /**
     * @var CampaignContext
     */
    private $campaignContext;

    /**
     * @var Collection
     */
    private $segmentsCollection;

    /**
     * @var CustomerSegmentHelper
     */
    private $segmentHelper;

    /**
     * @var WebsiteCollection
     */
    private $websiteCollection;

    /**
     * @var array
     */
    private $websiteNames;

    /**
     * @var Yesno
     */
    private $yesNo;

    /**
     * CustomerSegment constructor.
     *
     * @param CampaignContext                  $campaignContext          Campaign context.
     * @param CustomerSegmentCollectionFactory $segmentCollectionFactory Customer segments collection factory.
     * @param CustomerSegmentHelper            $segmentHelper            Customer segments helper.
     * @param WebsiteCollectionFactory         $websiteCollectionFactory Website collection factory.
     * @param Yesno                            $yesNo                    Yes/No source model.
     */
    public function __construct(
        CampaignContext $campaignContext,
        CustomerSegmentCollectionFactory $segmentCollectionFactory,
        CustomerSegmentHelper $segmentHelper,
        WebsiteCollectionFactory $websiteCollectionFactory,
        Yesno $yesNo
    ) {
        $this->campaignContext    = $campaignContext;
        $this->yesNo              = $yesNo;
        $this->segmentsCollection = $segmentCollectionFactory->create();
        $this->segmentHelper      = $segmentHelper;
        $this->websiteCollection  = $websiteCollectionFactory->create();
        $this->initWebsiteNames();
    }

    /**
     * {@inheritdoc}
     */
    public function modifyData(array $data)
    {
        $campaign = $this->campaignContext->getCurrentCampaign();

        if ($campaign && $campaign->getId() && isset($data[$campaign->getId()])) {
            $containerData = $campaign->getCustomerSegment() ?? [];
            $containerData['apply_to'] = (int) false;

            $segmentsData = $this->fillSegmentData($containerData['segment_ids'] ?? []);
            $containerData = [
                'apply_to'      => (int) !empty($segmentsData),
                'segment_ids'   => $segmentsData,
                'enabled'       => $this->segmentHelper->isEnabled(),
            ];

            $data[$campaign->getId()]['customer_segment'] = $containerData;
        }

        return $data;
    }

    /**
     * {@inheritdoc}
     */
    public function modifyMeta(array $data)
    {
        return $data;
    }

    /**
     * Get query data to fill the selected customer segments dynamicRows in Ui Component Form.
     *
     * @param integer[] $segmentIds The customer segment ids
     * @return array
     */
    private function fillSegmentData($segmentIds)
    {
        $data = [];

        if (!empty($segmentIds)) {
            $collection  = $this->segmentsCollection->addFieldToFilter('segment_id', $segmentIds)->addCustomerCountToSelect();
            $yesNoValues = $this->yesNo->toArray();

            foreach ($collection as $segment) {
                $data[] = [
                    'id'    => $segment->getId(),
                    'name'  => $segment->getName(),
                    'is_active' => $yesNoValues[(int) $segment->getIsActive()],
                    'website_ids' => $this->getWebsiteNames($segment->getWebsiteIds()),
                    'customer_count' => $segment->getCustomerCount(),
                ];
            }
        }

        return $data;
    }

    /**
     * Init list of website names.
     *
     * @return void
     */
    private function initWebsiteNames()
    {
        $this->websiteNames = [];
        foreach ($this->websiteCollection as $website) {
            $this->websiteNames[$website->getWebsiteId()] = $website->getName();
        }
    }

    /**
     * Return as a string the list of website names corresponding to a list of website ids.
     *
     * @param array|string $websiteIds List of website ids.
     * @return string
     */
    private function getWebsiteNames($websiteIds)
    {
        $websiteNames = [];

        if (!is_array($websiteIds)) {
            $websiteIds = explode(',', $websiteIds);
        }
        foreach ($websiteIds as $websiteId) {
            $websiteNames[] = $this->websiteNames[$websiteId] ?? $websiteId;
        }

        return implode(', ', $websiteNames);
    }
}
