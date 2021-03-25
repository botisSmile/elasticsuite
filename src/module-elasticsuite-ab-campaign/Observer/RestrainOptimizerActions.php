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

namespace Smile\ElasticsuiteAbCampaign\Observer;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\ActionFlag;
use Magento\Framework\App\Response\RedirectInterface;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Message\ManagerInterface;
use Smile\ElasticsuiteAbCampaign\Api\Campaign\OptimizerManagerInterface;
use Smile\ElasticsuiteCatalogOptimizer\Api\Data\OptimizerInterface;

/**
 * Observer RestrainOptimizerActions: Restrain optimizer action if it is linked to a running campaign.
 *
 * @category Smile
 * @package  Smile\ElasticsuiteAbCampaign
 * @author   Pierre Le Maguer <pierre.lemaguer@smile.fr>
 */
class RestrainOptimizerActions implements ObserverInterface
{
    /**
     * @var ActionFlag
     */
    private $actionFlag;

    /**
     * @var ManagerInterface
     */
    private $messageManager;

    /**
     * @var OptimizerManagerInterface
     */
    private $campaignOptimizerManager;

    /**
     * @var RedirectInterface
     */
    private $redirect;

    /**
     * RestrainOptimizerActions constructor
     *
     * @param ActionFlag                $actionFlag               Action flag
     * @param ManagerInterface          $messageManager           Message manager
     * @param RedirectInterface         $redirect                 Redirect
     * @param OptimizerManagerInterface $campaignOptimizerManager Campaign optimizer manager
     */
    public function __construct(
        ActionFlag $actionFlag,
        ManagerInterface $messageManager,
        RedirectInterface $redirect,
        OptimizerManagerInterface $campaignOptimizerManager
    ) {
        $this->actionFlag               = $actionFlag;
        $this->messageManager           = $messageManager;
        $this->campaignOptimizerManager = $campaignOptimizerManager;
        $this->redirect                 = $redirect;
    }

    /**
     * Restrain optimizer action if it is linked to a running campaign.
     *
     * {@inheritDoc}
     */
    public function execute(Observer $observer)
    {
        /** @var Action $controller */
        $controller = $observer->getControllerAction();
        $optimizerId = (int) $controller->getRequest()->getParam('id');
        $optimizerId = $optimizerId ?: (int) $controller->getRequest()->getParam(OptimizerInterface::OPTIMIZER_ID);

        if ($optimizerId && $this->campaignOptimizerManager->extractOptimizerIdsToRestrain([$optimizerId])) {
            $this->messageManager->addErrorMessage(
                __("You can edit an optimizer linked to a running campaign only on the campaign page.")
            );
            $this->actionFlag->set('', Action::FLAG_NO_DISPATCH, true);
            $controller->getResponse()->setRedirect($controller->getUrl('*/*/'));
        }
    }
}
