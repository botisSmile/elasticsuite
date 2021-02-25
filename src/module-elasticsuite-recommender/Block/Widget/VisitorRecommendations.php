<?php
/**
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this module to newer
 * versions in the future.
 *
 * @category  Smile
 * @package   Smile\ElasticsuiteRecommender
 * @author    Richard BAYET <richard.bayet@smile.fr>
 * @copyright 2021 Smile
 * @license   Licensed to Smile-SA. All rights reserved. No warranty, explicit or implicit, provided.
 *            Unauthorized copying of this file, via any medium, is strictly prohibited.
 */

namespace Smile\ElasticsuiteRecommender\Block\Widget;

use Magento\Ui\Block\Wrapper;

/**
 * Dynamically creates a visitor recommendations widget ui component, using information
 * from widget instance and widget.xml
 *
 * @category Smile
 * @package  Smile\ElasticsuiteRecommender
 */
class VisitorRecommendations extends Wrapper implements \Magento\Widget\Block\BlockInterface
{
    /**
     * {@inheritDoc}
     */
    public function renderApp($data = [])
    {
        $data = $this->getData();

        $params = [];

        if (isset($data['store_id'])) {
            $params['store_id'] = $data['store_id'];
        }

        if (isset($data['page_size'])) {
            $params['page_size'] = $data['page_size'];
        }

        if (!empty($params)) {
            $this->setData('params', $params);
        }

        return parent::renderApp($data);
    }
}
