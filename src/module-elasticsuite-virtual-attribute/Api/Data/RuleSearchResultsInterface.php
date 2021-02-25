<?php
/**
 * DISCLAIMER
 * Do not edit or add to this file if you wish to upgrade Smile Elastic Suite to newer
 * versions in the future.
 *
 * @category  Smile
 * @package   Smile\ElasticsuiteVirtualAttribute
 * @author    Romain Ruaud <romain.ruaud@smile.fr>
 * @copyright 2021 Smile
 * @license   Licensed to Smile-SA. All rights reserved. No warranty, explicit or implicit, provided.
 *            Unauthorized copying of this file, via any medium, is strictly prohibited.
 */
namespace Smile\ElasticsuiteVirtualAttribute\Api\Data;

/**
 * Search Result Interface for Virtual Attributes Rules.
 *
 * @category Smile
 * @package  Smile\ElasticsuiteVirtualAttribute
 * @author   Romain Ruaud <romain.ruaud@smile.fr>
 */
interface RuleSearchResultsInterface extends \Magento\Framework\Api\SearchResultsInterface
{
    /**
     * Get Optimizers list.
     *
     * @return \Smile\ElasticsuiteVirtualAttribute\Api\Data\RuleInterface[]
     */
    public function getItems();

    /**
     * Set Optimizers list.
     *
     * @param \Smile\ElasticsuiteVirtualAttribute\Api\Data\RuleInterface[] $items list of rules
     *
     * @return \Smile\ElasticsuiteVirtualAttribute\Api\Data\RuleSearchResultsInterface
     */
    public function setItems(array $items);
}
