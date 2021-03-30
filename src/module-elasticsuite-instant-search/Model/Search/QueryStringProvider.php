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
 * @copyright 2021 Smile
 * @license   Licensed to Smile-SA. All rights reserved. No warranty, explicit or implicit, provided.
 *            Unauthorized copying of this file, via any medium, is strictly prohibited.
 */

namespace Smile\ElasticsuiteInstantSearch\Model\Search;

use Magento\Framework\Stdlib\StringUtils;
use Magento\Framework\App\RequestInterface;
use Magento\Search\Model\QueryFactory;

/**
 * Query String provider : will fetch current search query from current request.
 *
 * @category Smile
 * @package  Smile\ElasticsuiteInstantSearch
 * @author   Romain Ruaud <romain.ruaud@smile.fr>
 */
class QueryStringProvider
{
    /**
     * @var RequestInterface
     */
    private $request;

    /** @var StringUtils */
    private $string;

    /**
     * @var null|string
     */
    private $currentQuery = null;

    /**
     * QueryStringProvider constructor.
     *
     * @param RequestInterface $request HTTP Request
     * @param StringUtils      $string  String utils
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
