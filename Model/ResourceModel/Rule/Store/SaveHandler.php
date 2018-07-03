<?php
/**
 * DISCLAIMER
 * Do not edit or add to this file if you wish to upgrade Smile Elastic Suite to newer
 * versions in the future.
 *
 * @category  Smile
 * @package   Smile\ElasticsuiteVirtualAttribute
 * @author    Romain Ruaud <romain.ruaud@smile.fr>
 * @copyright 2018 Smile
 * @license   Open Software License ("OSL") v. 3.0
 */
namespace Smile\ElasticsuiteVirtualAttribute\Model\ResourceModel\Rule\Store;

/**
 * Virtual Attribute store relation read handler.
 *
 * @category Smile
 * @package  Smile\ElasticsuiteVirtualAttribute
 * @author   Romain Ruaud <romain.ruaud@smile.fr>
 */
class SaveHandler implements \Magento\Framework\EntityManager\Operation\ExtensionInterface
{
    /**
     * {@inheritdoc}
     */
    public function execute($entity, $arguments = [])
    {
        /** @var \Smile\ElasticsuiteVirtualAttribute\Model\ResourceModel\Rule $resource */
        $resource = $entity->getResource();

        $resource->saveStoreRelation($entity);

        return $entity;
    }
}
