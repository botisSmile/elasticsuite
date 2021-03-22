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

use Magento\Framework\Stdlib\DateTime\Filter\Date as DateFilter;
use PHPUnit\Framework\MockObject\MockObject;
use Smile\ElasticsuiteAbCampaign\Api\Data\CampaignInterface;
use Smile\ElasticsuiteAbCampaign\Exception\ValidatorException;
use Smile\ElasticsuiteAbCampaign\Model\Campaign;
use Smile\ElasticsuiteAbCampaign\Model\Campaign\Validator\Date;

/**
 * Date validator test.
 *
 * @category Smile
 * @package  Smile\ElasticsuiteCatalog
 * @author   Pierre Le Maguer <pierre.lemaguer@smile.fr>
 */
class DateTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var DateFilter|MockObject
     */
    private $dateFilter;

    /**
     * @var CampaignInterface|MockObject
     */
    private $campaign;

    /**
     * @var Date
     */
    private $dateValidator;

    /**
     * {@inheritDoc}
     */
    protected function setUp(): void
    {
        $this->dateFilter    = $this->getDateFilterMock();
        $this->campaign      = $this->getCampaignMock();
        $this->dateValidator = new Date($this->dateFilter);
    }

    /**
     * Test date validator.
     *
     * @dataProvider validateDataProvider
     *
     * @param string $startDate      Start date
     * @param string $endDate        End Date
     * @param string $today          Today
     * @param bool   $throwException Should throw an exception or not
     *
     * @return void
     */
    public function testValidate($startDate, $endDate, $today, $throwException)
    {
        $this->dateFilter->method('filter')->willReturn($today);
        $this->campaign->method('getStartDate')->willReturn($startDate);
        $this->campaign->method('getEndDate')->willReturn($endDate);
        if ($throwException) {
            $this->expectException(ValidatorException::class);
            $this->dateValidator->validate($this->campaign);

            return;
        }

        $this->assertNull($this->dateValidator->validate($this->campaign));
    }



    /**
     * List of tested combination for the getFieldType method.
     *
     * @return array
     */
    public function validateDataProvider()
    {
        return [
            ['2021-03-15', '', '2021-03-23', true],
            ['', '2021-03-30', '2021-03-23', true],
            ['2021-04-01', '2021-03-30', '2021-03-23', true],
            ['2021-02-02', '2021-03-12', '2021-03-23', true],
            ['2021-05-02', '2021-12-31', '2021-03-23', false],
            ['2022-05-02', '2035-12-31', '2021-03-23', false],
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

    /**
     * Generate applier list factory mock.
     *
     * @return MockObject
     */
    private function getDateFilterMock(): MockObject
    {
        return $this
            ->getMockBuilder(DateFilter::class)
            ->disableOriginalConstructor()
            ->getMock();
    }
}
