<?php
/**
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Smile ElasticSuite to newer
 * versions in the future.
 *
 * @category  Smile
 * @package   Smile\ElasticsuiteInstantSearch
 * @author    Romain Ruaud <romain.ruaud@smile.fr>
 * @copyright 2019 Smile
 * @license   Open Software License ("OSL") v. 3.0
 */

namespace Smile\ElasticsuiteInstantSearch\Model\Search;

use \Magento\Framework\Stdlib\StringUtils;
use \Magento\Framework\App\RequestInterface;
use Magento\Search\Model\QueryFactory;

class QueryStringProvider
{
    /**
     * @var \Magento\Framework\App\RequestInterface
     */
    private $request;

    /** @var \Magento\Framework\Stdlib\StringUtils */
    private $stringUtils;

    /**
     * @var null|string
     */
    private $currentQuery = null;

    /**
     * QueryStringProvider constructor.
     *
     * @param \Magento\Framework\App\RequestInterface $request HTTP Request
     * @param \Magento\Framework\Stdlib\StringUtils   $string  String utils
     */
    public function __construct(RequestInterface $request, StringUtils $string)
    {
        $this->request = $request;
        $this->string  = $string;
    }

    /**
     * Get current query string.
     *
     * @return string
     */
    public function get()
    {
        if ($this->currentQuery === null) {
            $queryText = $this->request->getParam(QueryFactory::QUERY_VAR_NAME);

            $this->currentQuery = ($queryText === null || is_array($queryText)) ? '' : $this->string->cleanString(trim($queryText));
        }

        return $this->currentQuery;
    }
}
