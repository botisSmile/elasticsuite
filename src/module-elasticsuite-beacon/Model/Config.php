<?php
/**
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Smile ElasticSuite to newer
 * versions in the future.
 *
 * @category  Smile
 * @package   Smile\ElasticSuiteBeacon
 * @author    Richard Bayet <richard.bayet@smile.fr>
 * @copyright 2021 Smile
 * @license   Licensed to Smile-SA. All rights reserved. No warranty, explicit or implicit, provided.
 *            Unauthorized copying of this file, via any medium, is strictly prohibited.
 */

namespace Smile\ElasticsuiteBeacon\Model;

use Magento\Framework\App\Config\ScopeConfigInterface;

/**
 * Class Config
 *
 * @category Smile
 * @package  Smile\ElasticsuiteBeacon
 */
class Config implements ConfigInterface
{
    /**
     * Elasticsuite premium beacon client_id configuration path
     *
     * @var string
     */
    const CONFIG_CLIENT_ID_XPATH = 'smile_elasticsuite_beacon/general/client_id';

    /**
     * Default value for client ID
     *
     * @var string
     */
    const DEFAULT_CLIENT_ID = 'elasticsuite_premium';

    /**
     * Elasticsuite premium beacon beeps export endpoint url configuration path
     */
    const CONFIG_EXPORT_ENDPOINT_URL_XPATH = 'smile_elasticsuite_beacon/general/endpoint_url';

    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * Config constructor.
     *
     * @param ScopeConfigInterface $scopeConfig Scope config.
     */
    public function __construct(ScopeConfigInterface $scopeConfig)
    {
        $this->scopeConfig = $scopeConfig;
    }

    /**
     * {@inheritDoc}
     */
    public function getClientId(): string
    {
        $clientId = trim($this->scopeConfig->getValue(self::CONFIG_CLIENT_ID_XPATH) ?? self::DEFAULT_CLIENT_ID);
        $clientId = strlen($clientId > 0) ? $clientId : self::DEFAULT_CLIENT_ID;

        return $clientId;
    }

    /**
     * {@inheritDoc}
     */
    public function getEndpointUrl(): string
    {
        return $this->scopeConfig->getValue(self::CONFIG_EXPORT_ENDPOINT_URL_XPATH);
    }
}
