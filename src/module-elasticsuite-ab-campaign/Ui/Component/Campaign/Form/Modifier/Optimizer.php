<?php
/**
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Smile ElasticSuite to newer
 * versions in the future.
 *
 * @category  Smile
 * @package   Smile\ElasticsuiteAbCampaign
 * @author    Pierre LE MAGUER <pierre.lemaguer@smile.fr>
 * @copyright 2021 Smile
 * @license   Licensed to Smile-SA. All rights reserved. No warranty, explicit or implicit, provided.
 *            Unauthorized copying of this file, via any medium, is strictly prohibited.
 */

namespace Smile\ElasticsuiteAbCampaign\Ui\Component\Campaign\Form\Modifier;

use Magento\Ui\DataProvider\Modifier\ModifierInterface;
use Smile\ElasticsuiteAbCampaign\Api\CampaignManagerInterface;
use Smile\ElasticsuiteAbCampaign\Block\Adminhtml\Campaign\Edit\Button\Reopen;
use Smile\ElasticsuiteAbCampaign\Model\Context\Adminhtml\Campaign as CampaignContext;

/**
 * Campaign Ui Component Modifier. Used to enable scenario fieldset.
 *
 * @category Smile
 * @package  Smile\ElasticsuiteAbCampaign
 * @author   Pierre Le Maguer <pierre.lemaguer@smile.fr>
 */
class Optimizer implements ModifierInterface
{
    /**
     * @var CampaignContext
     */
    private $campaignContext;

    /**
     * Optimizer constructor.
     *
     * @param CampaignContext $campaignContext Campaign context
     */
    public function __construct(
        CampaignContext $campaignContext
    ) {
        $this->campaignContext = $campaignContext;
    }

    /**
     * {@inheritdoc}
     */
    public function modifyData(array $data)
    {
        return $data;
    }

    /**
     * {@inheritdoc}
     */
    public function modifyMeta(array $meta)
    {
        /**
         * Only enable the scenario fieldset in edit page.
         * The disable should be set on scenario a and b fieldsets too to avoid issue with required field
         * in the create campagn page.
         */
        $meta['scenario']['arguments']['data']['config']['disabled'] = true;
        $meta['scenario']['arguments']['data']['config']['visible'] = false;
        $meta['scenario']['children']['scenario_a']['arguments']['data']['config']['disabled'] = true;
        $meta['scenario']['children']['scenario_b']['arguments']['data']['config']['disabled'] = true;
        if ($this->campaignContext->getCurrentCampaign()) {
            $meta['scenario']['arguments']['data']['config']['disabled'] = false;
            $meta['scenario']['arguments']['data']['config']['visible'] = true;
            $meta['scenario']['children']['scenario_a']['arguments']['data']['config']['disabled'] = false;
            $meta['scenario']['children']['scenario_b']['arguments']['data']['config']['disabled'] = false;
        }

        return $meta;
    }
}
