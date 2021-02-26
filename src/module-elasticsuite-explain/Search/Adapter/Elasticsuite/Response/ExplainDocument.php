<?php
/**
 * DISCLAIMER
 * Do not edit or add to this file if you wish to upgrade this module to newer
 * versions in the future.
 *
 * @category  Smile
 * @package   Smile\ElasticsuiteExplain
 * @author    Romain Ruaud <romain.ruaud@smile.fr>
 * @copyright 2021 Smile
 * @license   Licensed to Smile-SA. All rights reserved. No warranty, explicit or implicit, provided.
 *            Unauthorized copying of this file, via any medium, is strictly prohibited.
 */
namespace Smile\ElasticsuiteExplain\Search\Adapter\Elasticsuite\Response;

/**
 * Response document containing an explain field.
 *
 * @category Smile
 * @package  Smile\ElasticsuiteExplain
 * @author   Romain Ruaud <romain.ruaud@smile.fr>
 */
class ExplainDocument extends \Smile\ElasticsuiteCore\Search\Adapter\Elasticsuite\Response\Document
{
    /**
     * @var string
     */
    const EXPLAIN_DOC_FIELD_NAME = "_explanation";

    /**
     * @var string
     */
    const SORT_DOC_FIELD_NAME = "sort";

    /**
     * Return search document explain.
     *
     * @return array
     */
    public function getExplain()
    {
        return $this->_get(self::EXPLAIN_DOC_FIELD_NAME);
    }

    /**
     * Return search document sort values.
     *
     * @return array
     */
    public function getSort()
    {
        return $this->_get(self::SORT_DOC_FIELD_NAME);
    }
}
