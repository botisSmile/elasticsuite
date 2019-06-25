<?php
/**
 * DISCLAIMER
 * Do not edit or add to this file if you wish to upgrade Smile Elastic Suite to newer
 * versions in the future.
 *
 * @category  Smile
 * @package   Smile\ElasticsuiteBehavioralData
 * @author    Romain Ruaud <romain.ruaud@smile.fr>
 * @copyright 2017 Smile
 * @license   Open Software License ("OSL") v. 3.0
 */

namespace Smile\ElasticsuiteBehavioralData\Search\Request\Aggregation\Pipeline;

use Smile\ElasticsuiteCore\Search\Request\Aggregation\Pipeline\AbstractPipeline;

/**
 * "Moving Function" pipeline :
 * https://www.elastic.co/guide/en/elasticsearch/reference/current/search-aggregations-pipeline-movfn-aggregation.html
 *
 * @category Smile
 * @package  Smile\ElasticsuiteBehavioralData
 * @author   Romain Ruaud <romain.ruaud@smile.fr>
 */
class MovingFunction extends AbstractPipeline
{
    /**
     * @var string
     */
    private $script;

    /**
     * @var string
     */
    private $gapPolicy;

    /**
     * @var int
     */
    private $window;

    /**
     * BucketSelector constructor.
     *
     * @param string       $name        Pipeline name.
     * @param array|string $bucketsPath Pipeline buckets path.
     * @param string       $script      Pipeline script.
     * @param int          $window      Pipeline window.
     * @param string       $gapPolicy   Pipeline gap policy.
     */
    public function __construct(
        $name,
        $bucketsPath,
        $script,
        $window = 10,
        $gapPolicy = self::GAP_POLICY_SKIP
    ) {
        parent::__construct($name, $bucketsPath);
        $this->script    = $script;
        $this->gapPolicy = $gapPolicy;
        $this->window    = $window;
    }

    /**
     * Get pipeline script.
     *
     * @return string
     */
    public function getScript()
    {
        return $this->script;
    }

    /**
     * Get pipeline gap policy.
     *
     * @return string
     */
    public function getGapPolicy()
    {
        return $this->gapPolicy;
    }

    /**
     * @return int
     */
    public function getWindow()
    {
        return $this->window;
    }

    /**
     * Get pipeline type.
     *
     * @return string
     */
    public function getType()
    {
        return 'movingFunctionPipeline';
    }
}
