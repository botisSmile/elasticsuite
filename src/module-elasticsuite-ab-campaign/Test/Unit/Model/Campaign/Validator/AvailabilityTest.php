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
use Smile\ElasticsuiteAbCampaign\Api\CampaignManagerInterface;
use Smile\ElasticsuiteAbCampaign\Api\Data\CampaignInterface;
use Smile\ElasticsuiteAbCampaign\Exception\ValidatorException;
use Smile\ElasticsuiteAbCampaign\Model\Campaign;
use Smile\ElasticsuiteAbCampaign\Model\Campaign\Validator\Availability as AvailabilityValidator;
use Smile\ElasticsuiteAbCampaign\Model\CampaignManager;

/**
 * Availability validator test.
 *
 * @category Smile
 * @package  Smile\ElasticsuiteCatalog
 * @author   Pierre Le Maguer <pierre.lemaguer@smile.fr>
 */
class AvailabilityTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var CampaignManagerInterface|MockObject
     */
    private $campaignManager;

    /**
     * @var DateFilter|MockObject
     */
    private $dateFilter;

    /**
     * @var CampaignInterface|MockObject
     */
    private $campaign;

    /**
     * @var AvailabilityValidator
     */
    private $availabilityValidator;

    /**
     * {@inheritDoc}
     */
    protected function setUp(): void
    {
        $this->campaignManager       = $this->getCampaignManagerMock();
        $this->dateFilter            = $this->getDateFilterMock();
        $this->campaign              = $this->getCampaignMock();
        $this->availabilityValidator = new AvailabilityValidator(
            $this->dateFilter,
            $this->campaignManager
        );
    }

    /**
     * Test availability validator.
     *
     * @dataProvider validateDataProvider
     *
     * @param string $startDate        Start date
     * @param string $endDate          End date
     * @param array  $unavailabilities Array of unavailabilities
     * @param bool   $throwException   Should throw an exception or not
     *
     * @return void
     */
    public function testValidate($startDate, $endDate, $unavailabilities, $throwException)
    {
        $this->campaign->method('getStartDate')->willReturn($startDate);
        $this->campaign->method('getEndDate')->willReturn($endDate);
        $this->campaignManager->method('getUnavailabilities')->willReturn($unavailabilities);
        if ($throwException) {
            $this->expectException(ValidatorException::class);
            $this->availabilityValidator->validate($this->campaign);

            return;
        }

        $this->assertNull($this->availabilityValidator->validate($this->campaign));
    }



    /**
     * List of tested combination for the getFieldType method.
     *
     * @return array
     */
    public function validateDataProvider()
    {
        return [
            ['2021-03-15', '', [], true],
            ['', '2021-03-30', [], true],
            // Case of new campaign end date is the same than the start date of another published campaign.
            [
                '2021-03-15',
                '2021-03-30',
                [
                    ['start_date' => '2021-03-30', 'end_date' => '2021-04-01'],
                ],
                true,
            ],
            // Case of new campaign end date is the day before the start date of another published campaign.
            [
                '2021-03-15',
                '2021-03-30',
                [
                    ['start_date' => '2021-03-31', 'end_date' => '2021-04-01'],
                ],
                false,
            ],
            // Case of new campaign start date is the same than the end date of another published campaign.
            [
                '2021-03-21',
                '2021-03-30',
                [
                    ['start_date' => '2021-03-31', 'end_date' => '2021-04-01'],
                    ['start_date' => '2021-03-15', 'end_date' => '2021-03-21'],
                ],
                true,
            ],
            // Case of new campaign start defined between two campaigns.
            [
                '2021-03-22',
                '2021-03-30',
                [
                    ['start_date' => '2021-03-31', 'end_date' => '2021-04-01'],
                    ['start_date' => '2021-03-15', 'end_date' => '2021-03-21'],
                ],
                false,
            ],
        ];
    }

    /**
     * Get campaign manager mock.
     *
     * @return MockObject
     */
    private function getCampaignManagerMock(): MockObject
    {
        return $this
            ->getMockBuilder(CampaignManager::class)
            ->disableOriginalConstructor()
            ->getMock();
    }

    /**
     * Get campaign mock
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
     * Generate date filter mock
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
