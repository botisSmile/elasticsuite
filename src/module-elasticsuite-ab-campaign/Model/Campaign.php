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

use Magento\Framework\Model\AbstractModel;
use Magento\Framework\DataObject\IdentityInterface;
use Smile\ElasticsuiteAbCampaign\Api\Data\CampaignInterface;

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
     * @var string
     */
    protected $_cacheTag = self::CACHE_TAG;

    /**
     * {@inheritDoc}
     */
    public function getIdentities()
    {
        return [self::CACHE_TAG . '_' . $this->getId()];
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
    public function getAuthor()
    {
        return (string) $this->getData(self::AUTHOR);
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
    public function setAuthor($author)
    {
        return $this->setData(self::AUTHOR, $author);
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
}
