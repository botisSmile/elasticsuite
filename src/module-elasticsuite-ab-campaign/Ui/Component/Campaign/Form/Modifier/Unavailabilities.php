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
use Smile\ElasticsuiteAbCampaign\Model\Context\Adminhtml\Campaign as CampaignContext;

/**
 * Campaign Ui Component Modifier. Used to populate campaign unavailabilities.
 *
 * @category Smile
 * @package  Smile\ElasticsuiteAbCampaign
 * @author   Pierre Le Maguer <pierre.lemaguer@smile.fr>
 */
class Unavailabilities implements ModifierInterface
{
    /**
     * @var CampaignContext
     */
    private $campaignContext;

    /**
     * @var CampaignManagerInterface
     */
    private $campaignManager;

    /**
     * Unavailabilities constructor.
     *
     * @param CampaignContext          $campaignContext Campaign context
     * @param CampaignManagerInterface $campaignmanager Campaign Manager
     */
    public function __construct(
        CampaignContext $campaignContext,
        CampaignManagerInterface $campaignmanager
    ) {
        $this->campaignContext = $campaignContext;
        $this->campaignManager = $campaignmanager;
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
        $options = [
            'unavailabilities' => $this->campaignManager->getUnavailabilities(
                $this->campaignContext->getCurrentCampaign()
            ),
        ];

        $meta['general']['children']['start_date']['arguments']['data']['config']['options'] = $options;
        $meta['general']['children']['end_date']['arguments']['data']['config']['options']   = $options;

        return $meta;
    }
}
