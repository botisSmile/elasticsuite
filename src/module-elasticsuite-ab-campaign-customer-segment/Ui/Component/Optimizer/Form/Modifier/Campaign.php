<?php
/**
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Smile ElasticSuite to newer
 * versions in the future.
 *
 * @category  Smile
 * @package   Smile\ElasticsuiteAbCampaignCustomerSegment
 * @author    Pierre LE MAGUER <pierre.lemaguer@smile.fr>
 * @copyright 2021 Smile
 * @license   Licensed to Smile-SA. All rights reserved. No warranty, explicit or implicit, provided.
 *            Unauthorized copying of this file, via any medium, is strictly prohibited.
 */

namespace Smile\ElasticsuiteAbCampaignCustomerSegment\Ui\Component\Optimizer\Form\Modifier;

use Magento\Framework\App\RequestInterface;
use Magento\Ui\DataProvider\Modifier\ModifierInterface;

/**
 * Campaign Ui Component Modifier. Used to disable fields in optimizer form in campaign page.
 *
 * @category Smile
 * @package  Smile\ElasticsuiteAbCampaignCustomerSegment
 * @author   Pierre Le Maguer <pierre.lemaguer@smile.fr>
 */
class Campaign implements ModifierInterface
{
    /**
     * @var RequestInterface
     */
    private $request;

    /**
     * Optimizer constructor.
     *
     * @param RequestInterface $request Campaign context
     */
    public function __construct(
        RequestInterface $request
    ) {
        $this->request = $request;
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
        // Disable fieldset in the campaign form page in the optimizer form except in the case of optimizer persisting.
        $isInCampaignPage = (int) $this->request->getParam('campaign_id');
        $persist = (bool) $this->request->getParam('persist');
        if ($isInCampaignPage && !$persist) {
            $meta = $this->disableFields($meta);
        }

        return $meta;
    }

    /**
     * Disable unwanted fields from optimizer form in campaign page.
     *
     * @param array $meta Meta
     * @return array
     */
    private function disableFields(array $meta): array
    {
        $meta['customer_segment']['arguments']['data']['config']['disabled'] = true;
        $meta['customer_segment']['arguments']['data']['config']['visible'] = false;

        return $meta;
    }
}
