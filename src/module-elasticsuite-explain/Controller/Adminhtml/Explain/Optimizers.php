<?php
/**
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this module to newer
 * versions in the future.
 *
 * @category  Smile
 * @package   Smile\ElasticsuiteExplain
 * @author    Pierre Gauthier <pigau@smile.fr>
 * @copyright 2021 Smile
 * @license   Licensed to Smile-SA. All rights reserved. No warranty, explicit or implicit, provided.
 *            Unauthorized copying of this file, via any medium, is strictly prohibited.
 */

namespace Smile\ElasticsuiteExplain\Controller\Adminhtml\Explain;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Controller\ResultFactory;
use Smile\ElasticsuiteExplain\Model\Optimizer\Percolator;

/**
 * Explain Adminhtml Index controller.
 *
 * @category Smile
 * @package  Smile\ElasticsuiteExplain
 * @author   Pierre Gauthier <pigau@smile.fr>
 */
class Optimizers extends Action
{
    /** @var Percolator */
    private $optimizersPercolator;

    /**
     * Constructor.
     *
     * @param Context    $context              Controller context.
     * @param Percolator $optimizersPercolator Optimizers percolator.
     */
    public function __construct(
        Context $context,
        Percolator $optimizersPercolator
    ) {
        parent::__construct($context);
        $this->optimizersPercolator = $optimizersPercolator;
    }

    /**
     * {@inheritDoc}
     */
    public function execute()
    {
        $optimizerIds = $this->optimizersPercolator->getMatchingOptimizerIds(
            (int) $this->getRequest()->getParam('product_id'),
            (int) $this->getRequest()->getParam('store_id'),
            $this->getRequest()->getParam('search_container')
        );
        $response = $this->resultFactory->create(ResultFactory::TYPE_JSON);
        $response->setData($optimizerIds);

        return $response;
    }
}
