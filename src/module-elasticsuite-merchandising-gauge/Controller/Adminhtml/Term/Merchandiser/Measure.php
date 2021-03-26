<?php
/**
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this module to newer
 * versions in the future.
 *
 * @category  Smile
 * @package   Smile\ElasticsuiteMerchandiserGauge
 * @author    Richard BAYET <richard.bayet@smile.fr>
 * @copyright 2021 Smile
 * @license   Licensed to Smile-SA. All rights reserved. No warranty, explicit or implicit, provided.
 *            Unauthorized copying of this file, via any medium, is strictly prohibited.
 */

namespace Smile\ElasticsuiteMerchandisingGauge\Controller\Adminhtml\Term\Merchandiser;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Json\Helper\Data;
use Smile\ElasticsuiteMerchandisingGauge\Model\Term\Merchandiser\MeasureFactory;
use Smile\ElasticsuiteMerchandisingGauge\Model\MeasureInterface;
use Magento\Search\Model\QueryFactory;
use Magento\Search\Model\Query;

/**
 * Class Measure
 *
 * @category Smile
 * @package  Smile\ElasticsuiteMerchandiserGauge
 */
class Measure extends Action
{
    /**
     * @var Data
     */
    private $jsonHelper;

    /**
     * @var MeasureFactory
     */
    private $measureFactory;

    /**
     * @var QueryFactory
     */
    private $queryFactory;

    /**
     * Measure constructor.
     *
     * @param Context        $context        Controller context
     * @param Data           $jsonHelper     Json Helper.
     * @param MeasureFactory $measureFactory Search term measure factory.
     * @param QueryFactory   $queryFactory   Search query factory.
     */
    public function __construct(
        Context $context,
        Data $jsonHelper,
        MeasureFactory $measureFactory,
        QueryFactory $queryFactory
    ) {
        parent::__construct($context);

        $this->jsonHelper = $jsonHelper;
        $this->measureFactory = $measureFactory;
        $this->queryFactory = $queryFactory;
    }

    /**
     * {@inheritDoc}
     */
    public function execute()
    {
        $responseData = $this->getMeasureObject()->getData();
        $json         = $this->jsonHelper->jsonEncode($responseData);

        $this->getResponse()->representJson($json);
    }

    /**
     * Returns the measure object
     *
     * @return MeasureInterface
     */
    private function getMeasureObject()
    {
        $query = $this->getQuery();
        $dimension  = $this->getPreferredDimension();
        $pageSize   = $this->getPageSize();
        $previewSize = $this->getPreviewSize();

        $measureObject = $this->measureFactory->create([
            'searchQuery'   => $query,
            'preferredDimension' => $dimension,
            'sampleSize'    => $previewSize,
            'pageSize'      => $pageSize,
        ]);

        return $measureObject;
    }

    /**
     * Return the search query to take the measure of.
     * Applies current admin modifications to the query.
     *
     * @return Query
     */
    private function getQuery()
    {
        $query = $this->loadQuery();

        $this->setSortedProducts($query)
            ->setBlacklistedProducts($query);

        return $query;
    }

    /**
     * Load current category using the request params.
     *
     * @return Query
     */
    private function loadQuery()
    {
        $queryId    = $this->getRequest()->getParam('query_id', 0);
        $query      = $this->queryFactory->create()->load($queryId);

        return $query;
    }

    /**
     * Append products sorted by the user to the query.
     *
     * @param Query $query Search query..
     *
     * @return $this
     */
    private function setSortedProducts(Query $query)
    {
        $productPositions = $this->getRequest()->getParam('product_position', []);
        asort($productPositions);
        $productPositions = array_flip($productPositions);
        $query->setSortedProductIds($productPositions);

        return $this;
    }

    /**
     * Appends products blacklisted by the user in the query.
     *
     * @param Query $query Category.
     *
     * @return $this
     */
    private function setBlacklistedProducts(Query $query)
    {
        $blacklistedProducts = $this->getRequest()->getParam('blacklisted_products', []);
        $query->setBlacklistedProductIds(array_map('intval', $blacklistedProducts));

        return $this;
    }

    /**
     * Return the measure page size
     *
     * @return int
     */
    private function getPageSize()
    {
        return (int) $this->getRequest()->getParam('page_size');
    }

    /**
     * Return the measure page size
     *
     * @return int
     */
    private function getPreviewSize()
    {
        return (int) $this->getRequest()->getParam('preview_size');
    }

    /**
     * Return the preferred dimension
     *
     * @return mixed
     */
    private function getPreferredDimension()
    {
        return $this->getRequest()->getParam('dimension');
    }
}
