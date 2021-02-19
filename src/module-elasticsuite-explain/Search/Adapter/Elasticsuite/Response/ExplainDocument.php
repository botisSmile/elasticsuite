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
 * @license   Open Software License ("OSL") v. 3.0
 */
namespace Smile\ElasticsuiteExplain\Search\Adapter\Elasticsuite\Response;

/**
 * Response document containing an explain field.
 *
 * @category Smile
 * @package  Smile\ElasticSuiteDebug
 * @author   Romain Ruaud <romain.ruaud@smile.fr>
 */
class ExplainDocument extends \Smile\ElasticsuiteCore\Search\Adapter\Elasticsuite\Response\Document
{
    /**
     * @var string
     */
    const EXPLAIN_DOC_FIELD_NAME = "_explanation";

    /**
     * Return search document explain.
     *
     * @return array
     */
    public function getExplain()
    {
        return $this->_get(self::EXPLAIN_DOC_FIELD_NAME);
    }
}
