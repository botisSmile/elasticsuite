<?php
/**
 * DISCLAIMER
 * Do not edit or add to this file if you wish to upgrade Smile Elastic Suite to newer
 * versions in the future.
 *
 * @category  Smile
 * @package   Smile\ElasticsuiteBehavioralData
 * @author    Romain Ruaud <romain.ruaud@smile.fr>
 * @copyright 2019 Smile
 * @license   Open Software License ("OSL") v. 3.0
 */

namespace Smile\ElasticsuiteBehavioralData\Model;

use Magento\Framework\App\Config\ScopeConfigInterface;

/**
 * Behavioral data index configuration
 *
 * @category Smile
 * @package  Smile\ElasticsuiteBehavioralData
 * @author   Romain Ruaud <romain.ruaud@smile.fr>
 */
class Config
{
    /**
     * XML path for configuration variable dedicated to the use of weekly stats.
     */
    const XML_USE_WEEKLY_STATS = 'smile_elasticsuite_behavioral_data/general/use_weekly_stats';

    /**
     * XML path for configuration variable dedicated to daily interval.
     */
    const XML_DAILY_FROM = 'smile_elasticsuite_behavioral_data/general/daily_from';

    /**
     * XML path for configuration variable dedicated to weekly interval.
     */
    const XML_WEEKLY_FROM = 'smile_elasticsuite_behavioral_data/general/weekly_from';

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * @var null
     */
    private $useWeeklyStats = null;

    /**
     * @var null
     */
    private $dailyFrom = null;

    /**
     * @var null
     */
    private $weeklyFrom = null;

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
     * @return bool
     */
    public function isUseWeeklyStats()
    {
        if ($this->useWeeklyStats === null) {
            $this->useWeeklyStats = $this->scopeConfig->isSetFlag(self::XML_USE_WEEKLY_STATS);
        }

        return $this->useWeeklyStats;
    }

    /**
     * Get max anterior date to compute daily statistics.
     *
     * @return string
     */
    public function getDailyFrom()
    {
        if ($this->dailyFrom === null) {
            $this->dailyFrom = $this->scopeConfig->getValue(self::XML_DAILY_FROM) . 'day';
        }

        return $this->dailyFrom;
    }

    /**
     * Get max anterior date to compute weekly statistics.
     *
     * @return string
     */
    public function getWeeklyFrom()
    {
        if ($this->weeklyFrom === null) {
            $this->weeklyFrom = $this->scopeConfig->getValue(self::XML_WEEKLY_FROM) . 'day';
        }

        return $this->weeklyFrom;
    }
}
