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
use Smile\ElasticsuiteAbCampaign\Api\Data\CampaignOptimizerInterface;
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
        if ($this->campaignContext->getCurrentCampaign()) {
            $currentCampaign = $this->campaignContext->getCurrentCampaign();
            $campaignId      = $currentCampaign->getId();
            $data[$campaignId]['scenario_type_a']            = CampaignOptimizerInterface::SCENARIO_TYPE_A;
            $data[$campaignId]['scenario_type_b']            = CampaignOptimizerInterface::SCENARIO_TYPE_B;
            $data[$campaignId]['scenario_b_optimizer_count'] = count($currentCampaign->getScenarioBOptimizerIds());
            $data[$campaignId]['scenario_a_optimizer_count'] = count($currentCampaign->getScenarioAOptimizerIds());
        }

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
         * in the create campaign page.
         */
        $meta = $this->setScenarioFieldsetDisplay($meta, false);
        if ($this->campaignContext->getCurrentCampaign()) {
            $meta = $this->setScenarioFieldsetDisplay($meta, true);
        }

        return $meta;
    }

    /**
     * Set scenario fieldset display.
     *
     * @param array $meta    Meta
     * @param bool  $display Should display scenario fieldset ?
     * @return array
     */
    private function setScenarioFieldsetDisplay(array $meta, bool $display): array
    {
        $meta['scenario']['arguments']['data']['config']['disabled']                           = !$display;
        $meta['scenario']['arguments']['data']['config']['visible']                            = $display;
        $meta['scenario']['children']['scenario_a']['arguments']['data']['config']['disabled'] = !$display;
        $meta['scenario']['children']['scenario_b']['arguments']['data']['config']['disabled'] = !$display;

        return $meta;
    }
}
