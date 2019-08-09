<?php
/**
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this module to newer
 * versions in the future.
 *
 * @category  Smile
 * @package   Smile\ElasticsuiteCatalogOptimizerCustomerSegment
 * @author    Richard BAYET <richard.bayet@smile.fr>
 * @copyright 2019 Smile
 * @license   Open Software License ("OSL") v. 3.0
 */

namespace Smile\ElasticsuiteCatalogOptimizerCustomerSegment\Ui\Component\Optimizer\Form\Modifier;

use Smile\ElasticsuiteCatalogOptimizer\Model\Optimizer\Locator\LocatorInterface as OptimizerLocatorInterface;
use Magento\CustomerSegment\Model\ResourceModel\Grid\CollectionFactory as CustomerSegmentCollectionFactory;
use Magento\Store\Model\ResourceModel\Website\CollectionFactory as WebsiteCollectionFactory;
use Magento\Config\Model\Config\Source\Yesno;

/**
 * Optimizer Ui Component Modifier.
 * Used to populate customer segments.
 *
 * @category Smile
 * @package  Smile\ElasticsuiteCatalogOptimizerCustomerSegment
 */
class CustomerSegments implements \Magento\Ui\DataProvider\Modifier\ModifierInterface
{
    /**
     * @var OptimizerLocatorInterface
     */
    private $locator;

    /**
     * @var \Magento\CustomerSegment\Model\ResourceModel\Grid\Collection
     */
    private $segmentsCollection;

    /**
     * @var \Magento\Store\Model\ResourceModel\Website\Collection
     */
    private $websiteCollection;

    /**
     * @var array
     */
    private $websiteNames;

    /**
     * @var \Magento\Config\Model\Config\Source\Yesno
     */
    private $yesNo;

    /**
     * CustomerSegment constructor.
     *
     * @param OptimizerLocatorInterface        $locator                  Optimizer locator.
     * @param CustomerSegmentCollectionFactory $segmentCollectionFactory Customer segments collection factory.
     * @param WebsiteCollectionFactory         $websiteCollectionFactory Website collection factory.
     * @param Yesno                            $yesNo                    Yes/No source model.
     */
    public function __construct (
        OptimizerLocatorInterface $locator,
        CustomerSegmentCollectionFactory $segmentCollectionFactory,
        WebsiteCollectionFactory $websiteCollectionFactory,
        Yesno $yesNo
    ) {
        $this->locator  = $locator;
        $this->yesNo    = $yesNo;
        $this->segmentsCollection = $segmentCollectionFactory->create();
        $this->websiteCollection  = $websiteCollectionFactory->create();
        $this->initWebsiteNames();
    }

    /**
     * {@inheritdoc}
     */
    public function modifyData(array $data)
    {
        $optimizer = $this->locator->getOptimizer();

        if ($optimizer && $optimizer->getId() && isset($data[$optimizer->getId()])) {
            $containerData = $optimizer->getCustomerSegment() ?? [];
            $containerData['apply_to'] = (int) false;

            $segmentsData = $this->fillSegmentData($containerData['segment_ids'] ?? []);
            $containerData = [
                'apply_to' => (int) !empty($segmentsData),
                'segment_ids' => $segmentsData,
            ];

            $data[$optimizer->getId()]['customer_segment'] = $containerData;
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
     *
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
     *
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
