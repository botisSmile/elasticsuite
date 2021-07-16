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

namespace Smile\ElasticsuiteAbCampaign\Api\Data;

/**
 * Interface CampaignSessionInterface.
 *
 * @category Smile
 * @package  Smile\ElasticsuiteAbCampaign
 * @author   Botis <botis@smile.fr>
 */
interface CampaignSessionInterface
{
    /**
     * Name of the main DB Table.
     */
    const TABLE_NAME  = 'smile_elasticsuite_campaign_session';

    /**
     * Constant for field campaign_session_id.
     */
    const CAMPAIGN_SESSION_ID = 'campaign_session_id';

    /**
     * Constant for field campaign_id.
     */
    const CAMPAIGN_ID = 'campaign_id';

    /**
     * Constant for field session_count_total.
     */
    const SESSION_COUNT_TOTAL   = 'session_count_total';

    /**
     * Constant for field session_count_a.
     */
    const SESSION_COUNT_A = 'session_count_a';

    /**
     * Constant for field sales_count_a.
     */
    const SALES_COUNT_A = 'sales_count_a';

    /**
     * Constant for field conversion_rate_a.
     */
    const CONVERSION_RATE_A = 'conversion_rate_a';

    /**
     * Constant for field session_count_b.
     */
    const SESSION_COUNT_B = 'session_count_b';

    /**
     * Constant for field sales_count_b.
     */
    const SALES_COUNT_B = 'sales_count_b';

    /**
     * Constant for field conversion_rate_b.
     */
    const CONVERSION_RATE_B = 'conversion_rate_b';

    /**
     * Constant for field significance.
     */
    const SIGNIFICANCE = 'significance';

    /**
     * Get campaign session id.
     *
     * @return int|null
     */
    public function getId(): ?int;

    /**
     * Get campaign id.
     *
     * @return int
     */
    public function getCampaignId(): int;

    /**
     * Get session count total.
     *
     * @return int
     */
    public function getSessionCountTotal(): int;

    /**
     * Get session count for the scenario A.
     *
     * @return int
     */
    public function getSessionCountA(): int;

    /**
     * Get sales count for the scenario A.
     *
     * @return int
     */
    public function getSalesCountA(): int;

    /**
     * Get conversion rate for the scenario A.
     *
     * @return float
     */
    public function getConversionRateA(): float;

    /**
     * Get session count for the scenario B.
     *
     * @return int
     */
    public function getSessionCountB(): int;

    /**
     * Get sales count for the scenario B.
     *
     * @return int
     */
    public function getSalesCountB(): int;

    /**
     * Get conversion rate for the scenario B.
     *
     * @return float
     */
    public function getConversionRateB(): float;

    /**
     * Get significance.
     *
     * @return bool
     */
    public function isSignificance(): bool;

    /**
     * Set campaign session id.
     *
     * @param mixed $campaignSessionId Campaign session id
     * @return CampaignSessionInterface
     */
    public function setId($campaignSessionId): CampaignSessionInterface;

    /**
     * Set campaign id.
     *
     * @param int $campaignId Campaign id
     * @return CampaignSessionInterface
     */
    public function setCampaignId(int $campaignId): CampaignSessionInterface;

    /**
     * Set session count total.
     *
     * @param int $sessionCountTotal Session count total
     * @return CampaignSessionInterface
     */
    public function setSessionCountTotal(int $sessionCountTotal): CampaignSessionInterface;

    /**
     * Set session count for the scenario A.
     *
     * @param int $sessionCountA Session count A
     * @return CampaignSessionInterface
     */
    public function setSessionCountA(int $sessionCountA): CampaignSessionInterface;

    /**
     * Set sales count for the scenario A.
     *
     * @param int $salesCountA Sales count A
     * @return CampaignSessionInterface
     */
    public function setSalesCountA(int $salesCountA): CampaignSessionInterface;

    /**
     * Set conversion rate for the scenario A.
     *
     * @param float $conversionRateB Conversion rate B
     * @return CampaignSessionInterface
     */
    public function setConversionRateA(float $conversionRateB): CampaignSessionInterface;

    /**
     * Set session count for the scenario B.
     *
     * @param int $sessionCountB Session count B
     * @return CampaignSessionInterface
     */
    public function setSessionCountB(int $sessionCountB): CampaignSessionInterface;

    /**
     * Set sales count for the scenario B.
     *
     * @param int $salesCountB Sales count B
     * @return CampaignSessionInterface
     */
    public function setSalesCountB(int $salesCountB): CampaignSessionInterface;

    /**
     * Set conversion rate for the scenario B.
     *
     * @param float $conversionRateB Conversion rate B
     * @return CampaignSessionInterface
     */
    public function setConversionRateB(float $conversionRateB): CampaignSessionInterface;

    /**
     * Set significance.
     *
     * @param bool $significance Significance
     * @return CampaignSessionInterface
     */
    public function setSignificance(float $significance): CampaignSessionInterface;
}
