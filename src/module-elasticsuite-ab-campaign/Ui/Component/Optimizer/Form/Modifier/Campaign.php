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

namespace Smile\ElasticsuiteAbCampaign\Ui\Component\Optimizer\Form\Modifier;

use Magento\Framework\App\RequestInterface;
use Magento\Framework\View\Element\UiComponent\Context;
use Magento\Ui\DataProvider\Modifier\ModifierInterface;

/**
 * Campaign Ui Component Modifier. Used to edit optimizer form in campaign page.
 *
 * @category Smile
 * @package  Smile\ElasticsuiteAbCampaign
 * @author   Pierre Le Maguer <pierre.lemaguer@smile.fr>
 */
class Campaign implements ModifierInterface
{
    /**
     * @var RequestInterface
     */
    private $request;

    /**
     * @var Context
     */
    private $context;

    /**
     * Campaign constructor.
     *
     * @param RequestInterface $request Request
     * @param Context          $context Context
     */
    public function __construct(
        RequestInterface $request,
        Context $context
    ) {
        $this->request = $request;
        $this->context = $context;
    }

    /**
     * {@inheritdoc}
     */
    public function modifyData(array $data)
    {
        $campaignId = $isInCampaignPage = (int) $this->request->getParam('campaign_id');
        $scenarioType = (string) $this->request->getParam('scenario_type');
        $optimizerId = (int) $this->request->getParam('id');

        // Add campaign id and scenario type to optimizer entity and unset search container data.
        $data['']['campaign_id'] = $campaignId;
        $data['']['scenario_type'] = $scenarioType;
        $data['']['search_container'] = [];
        $data['']['search_containers'] = [];
        if ($optimizerId) {
            $data[$optimizerId]['campaign_id'] = $campaignId;
            $data[$optimizerId]['scenario_type'] = $scenarioType;
            $data[$optimizerId]['search_container'] = [];
            $data[$optimizerId]['search_containers'] = [];
        }

        // Change the optimizer form submit url in campaign page.
        if ($isInCampaignPage) {
            $data['config']['submit_url'] = $this->getUrl(
                'smile_elasticsuite_ab_campaign/optimizer/ajaxSave',
                ['create_from' => (int) $this->request->getParam('create_from')]
            );
        }

        return $data;
    }

    /**
     * {@inheritdoc}
     */
    public function modifyMeta(array $meta)
    {
        // Disable fieldset in the campaign form page.
        $isInCampaignPage = (int) $this->request->getParam('campaign_id');
        if ($isInCampaignPage) {
            $meta = $this->disableFields($meta);
        }

        return $meta;
    }

    /**
     * Generate url by route and parameters
     *
     * @param string $route  The route
     * @param array  $params The params
     * @return string
     */
    private function getUrl($route = '', $params = []): string
    {
        return $this->context->getUrl($route, $params);
    }

    /**
     * Disable fields in optimizer form.
     *
     * @param array $meta Meta
     * @return array
     */
    private function disableFields(array $meta): array
    {
        $meta['general']['children']['search_container']['arguments']['data']['config']['disabled'] = true;
        $meta['general']['children']['search_container']['arguments']['data']['config']['visible'] = false;
        $meta['general']['children']['from_date']['arguments']['data']['config']['disabled'] = true;
        $meta['general']['children']['from_date']['arguments']['data']['config']['visible'] = false;
        $meta['general']['children']['to_date']['arguments']['data']['config']['disabled'] = true;
        $meta['general']['children']['to_date']['arguments']['data']['config']['visible'] = false;
        $meta['general']['children']['storeviews']['arguments']['data']['config']['disabled'] = true;
        $meta['general']['children']['storeviews']['arguments']['data']['config']['visible'] = false;
        $meta['general']['children']['is_active']['arguments']['data']['config']['disabled'] = true;
        $meta['general']['children']['is_active']['arguments']['data']['config']['visible'] = false;
        $meta['quick_search_container']['arguments']['data']['config']['disabled'] = true;
        $meta['quick_search_container']['arguments']['data']['config']['visible'] = false;
        $meta['catalog_view_container']['arguments']['data']['config']['disabled'] = true;
        $meta['catalog_view_container']['arguments']['data']['config']['visible'] = false;
        $meta['optimizer_preview_fieldset']['arguments']['data']['config']['disabled'] = true;
        $meta['optimizer_preview_fieldset']['arguments']['data']['config']['visible'] = false;

        return $meta;
    }
}
