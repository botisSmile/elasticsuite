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

namespace Smile\ElasticsuiteAbCampaign\Plugin\Adminhtml;

use Magento\Framework\App\RequestInterface;
use Smile\ElasticsuiteCatalogOptimizer\Block\Adminhtml\Optimizer\Edit\Button\AbstractButton;
use Smile\ElasticsuiteCatalogOptimizer\Block\Adminhtml\Optimizer\Edit\Button\AbstractButton as Subject;

/**
 * Plugin ReplaceOptimizerButtonPlugin: replace optimizer buttons by buttons adapted to campaign page.
 *
 * @category Smile
 * @package  Smile\ElasticsuiteAbCampaign
 * @author   Pierre Le Maguer <pierre.lemaguer@smile.fr>
 */
class ReplaceOptimizerButtonPlugin
{
    /**
     * @var RequestInterface
     */
    private $request;

    /**
     * @var AbstractButton[]
     */
    private $buttonMaps;

    /**
     * @var AbstractButton[]
     */
    private $createFromButtonsMaps;

    /**
     * ReplaceOptimizerButtonPlugin constructor
     *
     * @param RequestInterface $request               Request
     * @param array            $buttonMaps            Button mapping
     * @param array            $createFromButtonsMaps Button mapping for create from form
     */
    public function __construct(
        RequestInterface $request,
        array $buttonMaps = [],
        array $createFromButtonsMaps = []
    ) {
        $this->request               = $request;
        $this->buttonMaps            = $buttonMaps;
        $this->createFromButtonsMaps = $createFromButtonsMaps;
    }

    /**
     * Replace optimizer buttons by buttons adapted to campaign page.
     *
     * @param Subject $subject    Optimizer button
     * @param array   $buttonData Button Data
     * @return array
     */
    public function afterGetButtonData(Subject $subject, array $buttonData): array
    {
        // Replace buttons in the campaign form page.
        $isInCampaignPage = (int) $this->request->getParam('campaign_id');
        if ($isInCampaignPage) {
            $buttonData = $this->getButtonDataFromMapping($subject, $this->buttonMaps, $buttonData);
            // In case we are on create from form, use its specific mapping.
            $isCreateFromPage = (bool) $this->request->getParam('create_from');
            if ($isCreateFromPage) {
                $buttonData = $this->getButtonDataFromMapping($subject, $this->createFromButtonsMaps, $buttonData);
            }
        }

        return $buttonData;
    }

    /**
     * Get button data from mapping.
     *
     * @param Subject $button        Button instance
     * @param array   $mapping       Mapping
     * @param array   $oldButtonData Old button data
     * @return array
     */
    private function getButtonDataFromMapping(Subject $button, array $mapping, array $oldButtonData): array
    {
        $newButtonData = null;
        foreach ($mapping as $optimizerButtonClass => $campaignOptimizerButton) {
            if ($button instanceof $optimizerButtonClass) {
                $newButtonData = [];
                if ($campaignOptimizerButton instanceof AbstractButton) {
                    $newButtonData = $campaignOptimizerButton->getButtonData();
                    break;
                }
            }
        }

        return $newButtonData === null ? $oldButtonData : $newButtonData;
    }
}
