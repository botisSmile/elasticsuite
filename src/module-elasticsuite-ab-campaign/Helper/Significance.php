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
 * Significance helper.
 *
 * The major part of this class is inspired by the package andreekeberg/abby (class Abby\Result).
 * Git url of andreekeberg/abby package : https://github.com/andreekeberg/abby
 *
 * @category Smile
 * @package  Smile\ElasticsuiteAbCampaign
 * @author   Botis <botis@smile.fr>
 */
class Significance extends AbstractHelper
{
    /** @var float  */
    private $minimumConfidence = 0.95;

    /** @var boolean */
    private $confident = false;

    /**
     * Return whether we can be confident of the result.
     *
     * This requires the confidence to be greater than or equal to the configured
     * minimum confidence.
     *
     * @param int $totalCountA Total count scenario A
     * @param int $salesCountA sales count scenario B
     * @param int $totalCountB Total count scenario B
     * @param int $salesCountB sales count scenario A
     *
     * @return bool
     */
    public function isConfident(int $totalCountA, int $salesCountA, int $totalCountB, int $salesCountB): bool
    {
        $this->calculate($totalCountA, $salesCountA, $totalCountB, $salesCountB);

        return $this->confident;
    }

    /**
     * Error function
     *
     * @param float $x x
     * @return float
     *
     * @SuppressWarnings(PHPMD.ShortVariable)
     */
    protected function erf(float $x): float
    {
        $cof = array(
            -1.3026537197817094, 6.4196979235649026e-1, 1.9476473204185836e-2,
            -9.561514786808631e-3, -9.46595344482036e-4, 3.66839497852761e-4,
            4.2523324806907e-5, -2.0278578112534e-5, -1.624290004647e-6,
            1.303655835580e-6, 1.5626441722e-8, -8.5238095915e-8,
            6.529054439e-9, 5.059343495e-9, -9.91364156e-10,
            -2.27365122e-10, 9.6467911e-11, 2.394038e-12,
            -6.886027e-12, 8.94487e-13, 3.13092e-13,
            -1.12708e-13, 3.81e-16, 7.106e-15,
            -1.523e-15, -9.4e-17, 1.21e-16,
            -2.8e-17,
        );

        $isneg = !1;
        $d     = 0;
        $dd    = 0;

        if ($x < 0) {
            $x     = -$x;
            $isneg = !0;
        }

        $t  = 2 / (2 + $x);
        $ty = 4 * $t - 2;

        for ($j = count($cof) - 1; $j > 0; $j--) {
            $tmp = $d;
            $d   = $ty * $d - $dd + $cof[$j];
            $dd  = $tmp;
        }

        $res = $t * exp(-$x * $x + 0.5 * ($cof[0] + $ty * $d) - $dd);

        return ($isneg) ? $res - 1 : 1 - $res;
    }

    /**
     * Cumulative distribution function.
     *
     * @param float $zScore Z score
     * @param float $mean   mean
     * @param float $std    std
     *
     * @return float
     */
    protected function cdf(float $zScore, float $mean, float $std): float
    {
        return 0.5 * (1 + $this->erf(($zScore - $mean) / sqrt(2 * $std * $std)));
    }

    /**
     * Calculate result.
     *
     * @param int $controlViews         Control views
     * @param int $controlConversions   Control conversions
     * @param int $variationViews       Variation views
     * @param int $variationConversions Variation conversions
     *
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    private function calculate(int $controlViews, int $controlConversions, int $variationViews, int $variationConversions): void
    {
        $controlConversionRate = $controlConversions / $controlViews;
        $variationConversionRate = $variationConversions / $variationViews;

        if (($controlViews != 0 && $variationViews != 0) &&
            !($controlConversions == 0 && $variationConversions == 0) &&
            $controlConversionRate !== $variationConversionRate
        ) {
            $crA = $controlConversionRate;
            $crB = $variationConversionRate;

            // Check which variation is the winner.
            $winner = ($crA > $crB) ? 0 : 1;

            // If control is winner, flip experiment and control for the remaining calculations.
            if ($winner === 0) {
                list($controlViews, $variationViews) = array($variationViews, $controlViews);
                list($crA, $crB) = array($crB, $crA);
            }

            // Calculate standard error.
            $seA = sqrt(($crA * (1 - $crA)) / $controlViews);
            $seB = sqrt(($crB * (1 - $crB)) / $variationViews);

            $seDiff = sqrt(pow($seA, 2) + pow($seB, 2));

            // Avoid division by zero when calculating zScore and confidence.
            $confident  = false;
            if (!!($crB - $crA) && !!$seDiff) {
                $zScore     = ($crB - $crA) / $seDiff;
                $confidence = $this->cdf($zScore, 0, 1);
                $confident  = $confidence >= $this->minimumConfidence;
            }

            $this->confident = $confident;
        }
    }
}
