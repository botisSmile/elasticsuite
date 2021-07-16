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
 * @license   Open Software License ("OSL") v. 3.0
 */
namespace Smile\ElasticsuiteAbCampaign\Helper;

use Magento\Framework\App\Helper\AbstractHelper;

/**
 * Smile AbCampaign helper.
 *
 * @category Smile
 * @package  Smile\ElasticsuiteAbCampaign
 * @author   Botis <botis@smile.fr>
 */
class Data extends AbstractHelper
{
    /**
     * Format in percentage.
     *
     * @param string $value Value
     * @return string
     */
    public function formatInPercentage(string $value): string
    {
        return number_format($value * 100, 2) . '%';
    }

    /**
     * Format campaign session value.
     *
     * @param int $totalSessionCount    Total session count
     * @param int $scenarioSessionCount Scenario session count
     * @return string
     */
    public function formatCampaignSessionValue(int $totalSessionCount, int $scenarioSessionCount): string
    {
        return $scenarioSessionCount . ' (' .
            $this->formatInPercentage($scenarioSessionCount / $totalSessionCount) . ')';
    }

    /**
     * Render Significance.
     *
     * @param bool $significance Significance
     * @return string
     */
    public function renderSignificance(bool $significance): string
    {
        return $significance ? __('Yes') : __('No');
    }
}
