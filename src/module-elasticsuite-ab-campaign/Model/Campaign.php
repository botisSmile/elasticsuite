<?php
/**
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Smile ElasticSuite to newer
 * versions in the future.
 *
 * @category  Smile
 * @package   Smile\ElasticsuiteAbCampaign
 * @author    Pierre Le Maguer <pierre.lemaguer@smile.fr>
 * @copyright 2021 Smile
 * @license   Licensed to Smile-SA. All rights reserved. No warranty, explicit or implicit, provided.
 *            Unauthorized copying of this file, via any medium, is strictly prohibited.
 */

namespace Smile\ElasticsuiteAbCampaign\Model;

use Exception;
use Magento\Framework\Data\Collection\AbstractDb;
use Magento\Framework\Model\AbstractModel;
use Magento\Framework\DataObject\IdentityInterface;
use Magento\Framework\Model\Context;
use Magento\Framework\Model\ResourceModel\AbstractResource;
use Magento\Framework\Registry;
use Magento\Framework\Stdlib\DateTime\Filter\Date;
use Smile\ElasticsuiteAbCampaign\Api\Data\CampaignInterface;
use Smile\ElasticsuiteAbCampaign\Api\Data\CampaignOptimizerInterface;
use Smile\ElasticsuiteAbCampaign\Model\Campaign\Limitation\IdentitiesFactory;

/**
 * Campaign model.
 *
 * @SuppressWarnings(CamelCasePropertyName)
 *
 * @category Smile
 * @package  Smile\ElasticsuiteAbCampaign
 * @author   Pierre Le Maguer <pierre.lemaguer@smile.fr>
 */
class Campaign extends AbstractModel implements CampaignInterface, IdentityInterface
{
    /**
     * @var string
     */
    const CACHE_TAG = 'smile_elasticsuite_campaign';

    /**
     * @var Date
     */
    private $dateFilter;

    /**
     * @var IdentitiesFactory
     */
    private $limitationIdentitiesFactory;

    /**
    /**
     * @var string
     */
    protected $_cacheTag = self::CACHE_TAG;

    /**
     * Class constructor
     *
     * @param Context               $context                     Context.
     * @param Registry              $registry                    Registry.
     * @param Date                  $dateFilter                  Date Filter.
     * @param IdentitiesFactory     $limitationIdentitiesFactory Limitation Identities.
     * @param AbstractResource|null $resource                    Resource.
     * @param AbstractDb|null       $resourceCollection          Resource collection.
     * @param array                 $data                        Data.
     */
    public function __construct(
        Context $context,
        Registry $registry,
        Date $dateFilter,
        IdentitiesFactory $limitationIdentitiesFactory,
        AbstractResource $resource = null,
        AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        parent::__construct($context, $registry, $resource, $resourceCollection, $data);
        $this->dateFilter                  = $dateFilter;
        $this->limitationIdentitiesFactory = $limitationIdentitiesFactory;
    }

    /**
     * {@inheritdoc}
     */
    public function getIdentities()
    {
        $limitationIdentities = $this->limitationIdentitiesFactory->create(['campaign' => $this]);
        $identities           = array_merge($this->getCacheTags(), $limitationIdentities->get());

        return $identities;
    }

    /**
     * {@inheritDoc}
     */
    public function getId()
    {
        return $this->getData(self::CAMPAIGN_ID);
    }

    /**
     * {@inheritDoc}
     */
    public function getStoreId()
    {
        return (int) $this->getData(self::STORE_ID);
    }

    /**
     * {@inheritDoc}
     */
    public function getAuthorId()
    {
        return (int) $this->getData(self::AUTHOR_ID);
    }

    /**
     * {@inheritDoc}
     */
    public function getAuthorName()
    {
        return (string) $this->getData(self::AUTHOR_NAME);
    }

    /**
     * {@inheritDoc}
     */
    public function getName()
    {
        return (string) $this->getData(self::NAME);
    }

    /**
     * {@inheritDoc}
     */
    public function getDescription()
    {
        return (string) $this->getData(self::DESCRIPTION);
    }

    /**
     * {@inheritDoc}
     */
    public function getCreatedAt()
    {
        return (string) $this->getData(self::CREATED_AT);
    }

    /**
     * {@inheritDoc}
     */
    public function getStartDate()
    {
        return (string) $this->getData(self::START_DATE);
    }

    /**
     * {@inheritDoc}
     */
    public function getEndDate()
    {
        return (string) $this->getData(self::END_DATE);
    }

    /**
     * {@inheritDoc}
     */
    public function getStatus()
    {
        return (string) $this->getData(self::STATUS);
    }

    /**
     * {@inheritDoc}
     */
    public function getSearchContainers()
    {
        return (array) $this->getData('search_containers');
    }

    /**
     * {@inheritDoc}
     */
    public function getScenarioAName()
    {
        return $this->getData(self::SCENARIO_A_NAME);
    }

    /**
     * {@inheritDoc}
     */
    public function getScenarioBName()
    {
        return $this->getData(self::SCENARIO_B_NAME);
    }

    /**
     * {@inheritDoc}
     */
    public function getScenarioAPercentage()
    {
        return $this->getData(self::SCENARIO_A_PERCENTAGE);
    }

    /**
     * Get scenario b percentage.
     *
     * @return flaot
     */
    public function getScenarioBPercentage()
    {
        return $this->getData(self::SCENARIO_B_PERCENTAGE);
    }

    /**
     * {@inheritDoc}
     */
    public function getScenarioAOptimizerIds()
    {
        return $this->getData(self::SCENARIO_A_OPTIMIZER_IDS);
    }

    /**
     * {@inheritDoc}
     */
    public function getScenarioBOptimizerIds()
    {
        return $this->getData(self::SCENARIO_B_OPTIMIZER_IDS);
    }

    /**
     * {@inheritDoc}
     */
    public function getOptimizerIds($scenarioType)
    {
        switch ($scenarioType) {
            case CampaignOptimizerInterface::SCENARIO_TYPE_A:
                return $this->getScenarioAOptimizerIds();
            case CampaignOptimizerInterface::SCENARIO_TYPE_B:
                return $this->getScenarioBOptimizerIds();
        }

        return [];
    }

    /**
     * {@inheritDoc}
     */
    public function setId($campaignId)
    {
        return $this->setData(self::CAMPAIGN_ID, $campaignId);
    }

    /**
     * {@inheritDoc}
     */
    public function setStoreId($storeId)
    {
        return $this->setData(self::STORE_ID, $storeId);
    }

    /**
     * {@inheritDoc}
     */
    public function setAuthorId($authorId)
    {
        return $this->setData(self::AUTHOR_ID, $authorId);
    }

    /**
     * {@inheritDoc}
     */
    public function setAuthorName($authorName)
    {
        return $this->setData(self::AUTHOR_NAME, $authorName);
    }

    /**
     * {@inheritDoc}
     */
    public function setName($name)
    {
        return $this->setData(self::NAME, $name);
    }

    /**
     * {@inheritDoc}
     */
    public function setDescription($description)
    {
        return $this->setData(self::DESCRIPTION, $description);
    }

    /**
     * {@inheritDoc}
     */
    public function setCreatedAt($createdAt)
    {
        return $this->setData(self::CREATED_AT, $createdAt);
    }

    /**
     * {@inheritDoc}
     */
    public function setStartDate($startDate)
    {
        return $this->setData(self::START_DATE, $startDate);
    }

    /**
     * {@inheritDoc}
     */
    public function setEndDate($endDate)
    {
        return $this->setData(self::END_DATE, $endDate);
    }

    /**
     * {@inheritDoc}
     */
    public function setStatus($status)
    {
        return $this->setData(self::STATUS, $status);
    }

    /**
     * {@inheritDoc}
     */
    public function setSearchContainers($searchContainer)
    {
        return $this->setData('search_containers', $searchContainer);
    }

    /**
     * {@inheritDoc}
     */
    public function setScenarioAName($scenarioAName)
    {
        return $this->setData(self::SCENARIO_A_NAME, $scenarioAName);
    }

    /**
     * {@inheritDoc}
     */
    public function setScenarioBName($scenarioBName)
    {
        return $this->setData(self::SCENARIO_B_NAME, $scenarioBName);
    }

    /**
     * {@inheritDoc}
     */
    public function setScenarioAPercentage($scenarioAPercentage)
    {
        return $this->setData(self::SCENARIO_A_PERCENTAGE, $scenarioAPercentage);
    }

    /**
     * Set scenario b percentage.
     *
     * @param float $scenarioBPercentage Scenario b percentage
     * @return Campaign
     */
    public function setScenarioBPercentage($scenarioBPercentage)
    {
        return $this->setData(self::SCENARIO_B_PERCENTAGE, $scenarioBPercentage);
    }

    /**
     * {@inheritDoc}
     */
    public function setScenarioAOptimizerIds($scenarioAOptimizerIds)
    {
        return $this->setData(self::SCENARIO_A_OPTIMIZER_IDS, $scenarioAOptimizerIds);
    }

    /**
     * {@inheritDoc}
     */
    public function setScenarioBOptimizerIds($scenarioBOptimizerIds)
    {
        return $this->setData(self::SCENARIO_B_OPTIMIZER_IDS, $scenarioBOptimizerIds);
    }

    /**
     * {@inheritDoc}
     */
    public function setOptimizerIds($optimizerIds, $scenarioType)
    {
        switch ($scenarioType) {
            case CampaignOptimizerInterface::SCENARIO_TYPE_A:
                return $this->setScenarioAOptimizerIds($optimizerIds);
            case CampaignOptimizerInterface::SCENARIO_TYPE_B:
                return $this->setScenarioBOptimizerIds($optimizerIds);
        }

        return $this;
    }

    /**
     * @SuppressWarnings(PHPMD.CamelCaseMethodName)
     *
     * {@inheritDoc}
     */
    protected function _construct()
    {
        $this->_init(\Smile\ElasticsuiteAbCampaign\Model\ResourceModel\Campaign::class);
    }
}
