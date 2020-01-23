<?php
/**
 * DISCLAIMER
 * Do not edit or add to this file if you wish to upgrade Smile Elastic Suite to newer
 * versions in the future.
 *
 * @category  Smile
 * @package   Smile\ElasticsuiteInstantSearch
 * @author    Romain Ruaud <romain.ruaud@smile.fr>
 * @copyright 2020 Smile
 * @license   Open Software License ("OSL") v. 3.0
 */

namespace Smile\ElasticsuiteInstantSearch\Controller\Product;


use Magento\Framework\App\Action\HttpGetActionInterface as HttpGetActionInterface;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Search\Model\AutocompleteInterface;
use Magento\Framework\Controller\ResultFactory;

/**
 * Custom controller to render product thumbnail
 *
 * @category Smile
 * @package  Smile\ElasticsuiteInstantSearch
 * @author   Romain Ruaud <romain.ruaud@smile.fr>
 */
class Thumbnail extends Action implements HttpGetActionInterface
{
    /**
     * @var  \Magento\Search\Model\AutocompleteInterface
     */
    private $autocomplete;

    /**
     * @param \Magento\Framework\App\Action\Context $context Controller context.
     */
    public function __construct(
        Context $context
    ) {
        parent::__construct($context);
    }

    public function execute()
    {
        
    }
}
