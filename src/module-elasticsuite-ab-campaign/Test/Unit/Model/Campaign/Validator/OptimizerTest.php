<?php
/**
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Smile ElasticSuite to newer
 * versions in the future.
 *
 * @category  Smile
 * @package   Smile\ElasticsuiteAbCampaign
 * @author    Pierre LE MAGUER <pierre.lemaguer@smile.fr>
 * @copyright 2021 Smile
 * @license   Licensed to Smile-SA. All rights reserved. No warranty, explicit or implicit, provided.
 *            Unauthorized copying of this file, via any medium, is strictly prohibited.
 */

namespace Smile\ElasticsuiteAbCampaign\Test\Unit\Model\Campaign\Validator;

use PHPUnit\Framework\MockObject\MockObject;
use Smile\ElasticsuiteAbCampaign\Api\Data\CampaignInterface;
use Smile\ElasticsuiteAbCampaign\Exception\ValidatorException;
use Smile\ElasticsuiteAbCampaign\Model\Campaign;
use Smile\ElasticsuiteAbCampaign\Model\Campaign\Validator\Date;
use Smile\ElasticsuiteAbCampaign\Model\Campaign\Validator\Optimizer;

/**
 * Date validator test.
 *
 * @category Smile
 * @package  Smile\ElasticsuiteCatalog
 * @author   Pierre Le Maguer <pierre.lemaguer@smile.fr>
 */
class OptimizerTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var CampaignInterface|MockObject
     */
    private $campaign;

    /**
     * @var Date
     */
    private $optimizerValidator;

    /**
     * {@inheritDoc}
     */
    protected function setUp(): void
    {
        $this->campaign           = $this->getCampaignMock();
        $this->optimizerValidator = new Optimizer();
    }

    /**
     * Test optimizer validator.
     *
     * @dataProvider validateDataProvider
     *
     * @param array $optimizerIdsA  Optimizer ids A
     * @param array $optimizerIdsB  Optimizer ids B
     * @param float $percentageA    Percentage A
     * @param bool  $throwException Should throw an exception or not
     * @return void
     */
    public function testValidate($optimizerIdsA, $optimizerIdsB, $percentageA, bool $throwException)
    {
        $this->campaign->method('getScenarioAPercentage')->willReturn($percentageA);
        $this->campaign->method('getScenarioAOptimizerIds')->willReturn($optimizerIdsA);
        $this->campaign->method('getScenarioBOptimizerIds')->willReturn($optimizerIdsB);
        if ($throwException) {
            $this->expectException(ValidatorException::class);
            $this->optimizerValidator->validate($this->campaign);

            return;
        }

        $this->assertNull($this->optimizerValidator->validate($this->campaign));
    }



    /**
     * List of tested combination for the getFieldType method.
     *
     * @return array
     */
    public function validateDataProvider()
    {
        return [
            [[1], [1], 50.0, true],
            [[1], [2], 100.5, true],
            [[1], ["1"], 50.0, true],
            [[1], [2], 50, false],
            [[1], [2], "50.0", false],
            [[1, 2, 3, 4], [5, 6], "50.0", false],
            [[1, 2, 3, 4], [5, 6], -10, true],
            [[1, 2, 3, 4], [5, 6, 8, 1, 9], "50.0", true],
        ];
    }

    /**
     * Generate applier list factory mock.
     *
     * @return MockObject
     */
    private function getCampaignMock(): MockObject
    {
        return $this
            ->getMockBuilder(Campaign::class)
            ->disableOriginalConstructor()
            ->getMock();
    }
}
