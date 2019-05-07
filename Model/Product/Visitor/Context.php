<?php
/**
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this module to newer
 * versions in the future.
 *
 * @category  Smile
 * @package   Smile\ElasticsuiteRecommender
 * @author    Richard BAYET <richard.bayet@smile.fr>
 * @copyright 2019 Smile
 * @license   Open Software License ("OSL") v. 3.0
 */

namespace Smile\ElasticsuiteRecommender\Model\Product\Visitor;

use Smile\ElasticsuiteRecommender\Model\Product\Matcher\ProductProvider\ContextInterface;

/**
 * Default context for the visitor product provider.
 *
 * @category Smile
 * @package  Smile\ElasticsuiteRecommender
 */
class Context implements ContextInterface
{
    /**
     * @var int[]
     */
    private $categories;

    /**
     * @var integer
     */
    private $maxAge;

    /**
     * @var integer
     */
    private $maxSize;

    /**
     * Context constructor.
     *
     * @param array   $categories Categories the products must belong to.
     * @param integer $maxAge     Max age of events to look for products.
     * @param integer $maxSize    Max number of products to provide.
     */
    public function __construct(
        $categories = [],
        $maxAge = 30,
        $maxSize = 10
    ) {
        $this->categories = $categories;
        $this->maxAge = $maxAge;
        $this->maxSize = $maxSize;
    }

    /**
     * {@inheritDoc}
     */
    public function getCategories()
    {
        return $this->categories;
    }

    /**
     * {@inheritDoc}
     */
    public function getMaxAge()
    {
        return $this->maxAge;
    }

    /**
     * {@inheritDoc}
     */
    public function getMaxSize()
    {
        return $this->maxSize;
    }
}
