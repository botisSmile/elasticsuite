<?php
/**
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Smile ElasticSuite to newer
 * versions in the future.
 *
 * @category  Smile
 * @package   Smile\ElasticsuiteBehavioralAutocomplete
 * @author    Richard Bayet <richard.bayet@smile.fr>
 * @copyright 2021 Smile
 * @license   Licensed to Smile-SA. All rights reserved. No warranty, explicit or implicit, provided.
 *            Unauthorized copying of this file, via any medium, is strictly prohibited.
 */

namespace Smile\ElasticsuiteBehavioralAutocomplete\Model;

use Magento\Framework\App\Config\ScopeConfigInterface;

/**
 * Behavioral Autocomplete config helper.
 *
 * @category Smile
 * @package  Smile\ElasticsuiteBehavioralAutocomplete
 * @author   Richard Bayet <richard.bayet@smile.fr>
 */
class Config
{
    /**
     * XML path for configuration variable dedicated to trending search terms selection.
     */
    const XML_PATH_TRENDING_ENABLED = 'smile_elasticsuite_behavioral_autocomplete/general/enabled';

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * @var boolean|null
     */
    private $trendingEnabled = null;

    /**
     * Config constructor.
     *
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfiguration Scope Config
     */
    public function __construct(ScopeConfigInterface $scopeConfiguration)
    {
        $this->scopeConfig = $scopeConfiguration;
    }

    /**
     * Returns true if trending search terms should be preferred to all-time popular terms.
     *
     * @return bool
     */
    public function isTrendingEnabled()
    {
        if ($this->trendingEnabled === null) {
            $this->trendingEnabled = $this->scopeConfig->isSetFlag(self::XML_PATH_TRENDING_ENABLED);
        }

        return $this->trendingEnabled;
    }
}
