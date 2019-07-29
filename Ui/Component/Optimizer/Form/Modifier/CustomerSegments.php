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

/**
 * Class CustomerSegment
 *
 * @category Smile
 * @package  Smile\ElasticsuiteCatalogOptimizerCustomerSegment
 */
class CustomerSegments implements \Magento\Ui\DataProvider\Modifier\ModifierInterface
{
    /**
     * @var \Smile\ElasticsuiteCatalogOptimizer\Model\Optimizer\Locator\LocatorInterface
     */
    private $locator;

    /**
     * @var \Magento\CustomerSegment\Model\ResourceModel\Grid\Collection
     */
    private $segmentsCollection;

    /**
     * @var \Magento\Config\Model\Config\Source\Yesno
     */
    private $yesNo;

    /**
     * CustomerSegment constructor.
     *
     * @param \Smile\ElasticsuiteCatalogOptimizer\Model\Optimizer\Locator\LocatorInterface $locator    Optimizer locator.
     * @param \Magento\CustomerSegment\Model\ResourceModel\Grid\CollectionFactory          $segmentCollectionFactory Customer segments collection factory.
     * @param \Magento\Config\Model\Config\Source\Yesno                                    $yesNo      Yes/No source model.
     */
    public function __construct (
        \Smile\ElasticsuiteCatalogOptimizer\Model\Optimizer\Locator\LocatorInterface $locator,
        \Magento\CustomerSegment\Model\ResourceModel\Grid\CollectionFactory $segmentCollectionFactory,
        \Magento\Config\Model\Config\Source\Yesno $yesNo
    ) {
        $this->locator  = $locator;
        $this->yesNo    = $yesNo;
        $this->segmentsCollection = $segmentCollectionFactory->create();
    }

    /**
     * {@inheritdoc}
     */
    public function modifyData(array $data)
    {
        $optimizer = $this->locator->getOptimizer();

        if ($optimizer && $optimizer->getId() && isset($data[$optimizer->getId()])) {
            if (isset($data[$optimizer->getId()]['customer_segment'])
                && isset($data[$optimizer->getId()]['customer_segment']['segment_ids'])) {
                $segmentsData = $this->fillSegmentData($data[$optimizer->getId()]['customer_segment']['segment_ids']);
                if (!empty($segmentsData)) {
                    $data[$optimizer->getId()]['customer_segment']['segment_ids'] = $segmentsData;
                }
            }
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

        $collection  = $this->segmentsCollection->addFieldToFilter('segment_id', $segmentIds)->addCustomerCountToSelect();
        $yesNoValues = $this->yesNo->toArray();

        foreach ($collection as $segment) {
            $data[] = [
                'id'    => $segment->getId(),
                'name'  => $segment->getName(),
                'is_active' => $yesNoValues[(int) $segment->getIsActive()],
                'website_ids' => $segment->getWebsiteIds(),
                'customer_count' => $segment->getCustomerCount(),
            ];
        }

        return $data;
    }
}
