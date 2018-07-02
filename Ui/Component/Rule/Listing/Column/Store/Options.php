<?php
/**
 * DISCLAIMER
 * Do not edit or add to this file if you wish to upgrade Smile Elastic Suite to newer
 * versions in the future.
 *
 * @category  Smile
 * @package   Smile\ElasticsuiteVirtualAttribute
 * @author    Romain Ruaud <romain.ruaud@smile.fr>
 * @copyright 2018 Smile
 * @license   Open Software License ("OSL") v. 3.0
 */
namespace Smile\ElasticsuiteVirtualAttribute\Ui\Component\Rule\Listing\Column\Store;

/**
 * Store Options for Smile Elastic Suite Virtual Attributes Rules.
 *
 * @category Smile
 * @package  Smile\ElasticsuiteVirtualAttribute
 * @author   Romain Ruaud <romain.ruaud@smile.fr>
 */
class Options extends \Magento\Store\Ui\Component\Listing\Column\Store\Options
{
    /**
     * All Store Views value
     */
    const ALL_STORE_VIEWS = '0';

    /**
     * Get options
     *
     * @return array
     */
    public function toOptionArray()
    {
        if ($this->options !== null) {
            return $this->options;
        }

        $this->currentOptions['All Store Views']['label'] = __('All Store Views');
        $this->currentOptions['All Store Views']['value'] = self::ALL_STORE_VIEWS;

        $this->generateCurrentOptions();

        $this->options = array_values($this->currentOptions);

        return $this->options;
    }
}
