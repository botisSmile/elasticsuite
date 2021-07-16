<?php
/**
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Smile ElasticSuite to newer
 * versions in the future.
 *
 * @category  Smile
 * @package   Smile\ElasticsuiteAbCampaign
 * @author    Botis <botis@smile.fr>
 * @copyright 2021 Smile
 * @license   Licensed to Smile-SA. All rights reserved. No warranty, explicit or implicit, provided.
 *            Unauthorized copying of this file, via any medium, is strictly prohibited.
 */

namespace Smile\ElasticsuiteAbCampaign\Model;

use Magento\Framework\Model\AbstractModel;
use Magento\Framework\DataObject\IdentityInterface;
use Smile\ElasticsuiteAbCampaign\Api\Data\CampaignSessionInterface;

/**
 * Campaign session model.
 *
 * @SuppressWarnings(CamelCasePropertyName)
 *
 * @category Smile
 * @package  Smile\ElasticsuiteAbCampaign
 * @author    Botis <botis@smile.fr>
 */
class CampaignSession extends AbstractModel implements CampaignSessionInterface, IdentityInterface
{
    /**
     * @var string
     */
    const CACHE_TAG = 'smile_elasticsuite_campaign_session';

    /**
     * {@inheritDoc}
     */
    public function getId(): ?int
    {
        return $this->getData(self::CAMPAIGN_SESSION_ID);
    }

    /**
     * {@inheritDoc}
     */
    public function getCampaignId(): int
    {
        return $this->getData(self::CAMPAIGN_ID);
    }

    /**
     * {@inheritDoc}
     */
    public function getSessionCountTotal(): int
    {
        return  $this->getData(self::SESSION_COUNT_TOTAL);
    }

    /**
     * {@inheritDoc}
     */
    public function getSessionCountA(): int
    {
        return  $this->getData(self::SESSION_COUNT_A);
    }

    /**
     * {@inheritDoc}
     */
    public function getSalesCountA(): int
    {
        return  $this->getData(self::SALES_COUNT_A);
    }

    /**
     * {@inheritDoc}
     */
    public function getConversionRateA(): float
    {
        return $this->getData(self::CONVERSION_RATE_A);
    }

    /**
     * {@inheritDoc}
     */
    public function getSessionCountB(): int
    {
        return  $this->getData(self::SESSION_COUNT_B);
    }

    /**
     * {@inheritDoc}
     */
    public function getSalesCountB(): int
    {
        return  $this->getData(self::SALES_COUNT_B);
    }

    /**
     * {@inheritDoc}
     */
    public function getConversionRateB(): float
    {
        return $this->getData(self::CONVERSION_RATE_B);
    }

    /**
     * {@inheritDoc}
     */
    public function isSignificance(): bool
    {
        return $this->getData(self::SIGNIFICANCE);
    }

    /**
     * {@inheritDoc}
     */
    public function setId($campaignSessionId): CampaignSessionInterface
    {
        return $this->setData(self::CAMPAIGN_SESSION_ID, $campaignSessionId);
    }

    /**
     * {@inheritDoc}
     */
    public function setCampaignId(int $campaignId): CampaignSessionInterface
    {
        return $this->setData(self::CAMPAIGN_ID, $campaignId);
    }

    /**
     * {@inheritDoc}
     */
    public function setSessionCountTotal(int $sessionCountTotal): CampaignSessionInterface
    {
        return $this->setData(self::SESSION_COUNT_TOTAL, $sessionCountTotal);
    }

    /**
     * {@inheritDoc}
     */
    public function setSessionCountA(int $sessionCountA): CampaignSessionInterface
    {
        return $this->setData(self::SESSION_COUNT_A, $sessionCountA);
    }

    /**
     * {@inheritDoc}
     */
    public function setSalesCountA(int $salesCountA): CampaignSessionInterface
    {
        return $this->setData(self::SALES_COUNT_A, $salesCountA);
    }

    /**
     * {@inheritDoc}
     */
    public function setConversionRateA(float $conversionRateB): CampaignSessionInterface
    {
        return $this->setData(self::CONVERSION_RATE_A, $conversionRateB);
    }

    /**
     * {@inheritDoc}
     */
    public function setSessionCountB(int $sessionCountB): CampaignSessionInterface
    {
        return $this->setData(self::SESSION_COUNT_B, $sessionCountB);
    }

    /**
     * {@inheritDoc}
     */
    public function setSalesCountB(int $salesCountB): CampaignSessionInterface
    {
        return $this->setData(self::SALES_COUNT_B, $salesCountB);
    }

    /**
     * {@inheritDoc}
     */
    public function setConversionRateB(float $conversionRateB): CampaignSessionInterface
    {
        return $this->setData(self::CONVERSION_RATE_B, $conversionRateB);
    }

    /**
     * {@inheritDoc}
     */
    public function setSignificance(float $significance): CampaignSessionInterface
    {
        return $this->setData(self::SIGNIFICANCE, $significance);
    }

    /**
     * {@inheritdoc}
     */
    public function getIdentities(): array
    {
        return [self::CACHE_TAG . '_' . $this->getId()];
    }

    /**
     * @SuppressWarnings(PHPMD.CamelCaseMethodName)
     *
     * {@inheritDoc}
     */
    protected function _construct()
    {
        $this->_init(\Smile\ElasticsuiteAbCampaign\Model\ResourceModel\CampaignSession::class);
    }
}
