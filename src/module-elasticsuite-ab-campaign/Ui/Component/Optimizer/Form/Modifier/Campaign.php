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

use Exception;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\View\Element\UiComponent\Context;
use Magento\Ui\DataProvider\Modifier\ModifierInterface;
use Smile\ElasticsuiteAbCampaign\Api\Campaign\OptimizerManagerInterface;
use Smile\ElasticsuiteAbCampaign\Api\CampaignRepositoryInterface;

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
     * @var CampaignRepositoryInterface
     */
    private $campaignRepository;

    /**
     * @var OptimizerManagerInterface
     */
    private $optimizerManager;

    /**
     * Campaign constructor.
     *
     * @param RequestInterface            $request            Request
     * @param Context                     $context            Context
     * @param CampaignRepositoryInterface $campaignRepository Campaign repository
     * @param OptimizerManagerInterface   $optimizerManager   Optimizer manager
     */
    public function __construct(
        RequestInterface $request,
        Context $context,
        CampaignRepositoryInterface $campaignRepository,
        OptimizerManagerInterface $optimizerManager
    ) {
        $this->request            = $request;
        $this->context            = $context;
        $this->campaignRepository = $campaignRepository;
        $this->optimizerManager   = $optimizerManager;
    }

    /**
     * {@inheritdoc}
     */
    public function modifyData(array $data)
    {
        $campaignId   = (int) $this->request->getParam('campaign_id');
        $scenarioType = (string) $this->request->getParam('scenario_type');
        $optimizerId  = (int) $this->request->getParam('id');
        $persist      = (string) $this->request->getParam('persist');

        if ($campaignId) {
            // Add campaign id and scenario type to optimizer entity and unset search container data.
            $data = $this->editDataForCampaignOptimizer($data, $campaignId, $scenarioType, $optimizerId);
            if ($persist) {
                $data = $this->prepareForOptimizerPersist($data, $campaignId, $optimizerId);
            }
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
        $persist = (bool) $this->request->getParam('persist');
        if ($isInCampaignPage) {
            $meta = $this->disablePreview($meta);
            if (!$persist) {
                $meta = $this->disableFields($meta);
            }
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

        return $meta;
    }

    /**
     * Disable preview in optimizer form.
     *
     * @param array $meta Meta
     * @return array
     */
    private function disablePreview(array $meta): array
    {
        $meta['optimizer_preview_fieldset']['arguments']['data']['config']['disabled'] = true;
        $meta['optimizer_preview_fieldset']['arguments']['data']['config']['visible'] = false;

        return $meta;
    }

    /**
     * Edit data for campaign optimizer.
     *
     * @param array  $data         Data
     * @param int    $campaignId   Campaign id
     * @param string $scenarioType Scenario type
     * @param int    $optimizerId  Optimizer id
     * @return array
     */
    private function editDataForCampaignOptimizer(
        array $data,
        int $campaignId,
        string $scenarioType,
        int $optimizerId
    ): array {
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

        $data['config']['submit_url'] = $this->getUrl(
            'smile_elasticsuite_ab_campaign/optimizer/ajaxSave',
            ['create_from' => (int) $this->request->getParam('create_from')]
        );

        return $data;
    }

    /**
     * Prepare for optimizer persist.
     *
     * @param array $data        Data
     * @param int   $campaignId  Campaign id
     * @param int   $optimizerId Optimizer id
     * @return array
     * @throws Exception
     */
    private function prepareForOptimizerPersist(
        array $data,
        int $campaignId,
        int $optimizerId
    ): array {
        if (!$optimizerId) {
            throw new Exception('We should have an optimizer id.');
        }

        $campaign = $this->campaignRepository->getById($campaignId);
        $data[$optimizerId] = $this->optimizerManager->addCampaignContextToOptimizer($campaign, $data[$optimizerId]);

        // Change the optimizer form submit url in campaign page.
        $data['config']['submit_url'] = $this->getUrl(
            'smile_elasticsuite_ab_campaign/optimizer/ajaxPersist',
            ['create_new_optimizer' => 1]
        );

        return $data;
    }
}
