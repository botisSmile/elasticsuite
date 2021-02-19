<?php
/**
 * DISCLAIMER
 * Do not edit or add to this file if you wish to upgrade Smile Elastic Suite to newer
 * versions in the future.
 *
 * @category  Smile
 * @package   Smile\ElasticSuite________
 * @author    Romain Ruaud <romain.ruaud@smile.fr>
 * @copyright 2017 Smile
 * @license   Open Software License ("OSL") v. 3.0
 */

namespace Smile\ElasticsuiteInstantSearch\Model\Autocomplete\Product;

use Magento\Catalog\Api\Data\ProductAttributeInterface;
use Magento\Catalog\Model\ResourceModel\Product\Attribute\Collection as AttributeCollection;
use Magento\Catalog\Model\ResourceModel\Product\Attribute\CollectionFactory as AttributeCollectionFactory;
use Magento\Framework\App\CacheInterface;
use Magento\Store\Model\StoreManagerInterface;
use Smile\ElasticsuiteCatalog\Helper\Autocomplete as AutocompleteHelper;

/**
 * _________________________________________________
 *
 * @category Smile
 * @package  Smile\ElasticSuite______________
 * @author   Romain Ruaud <romain.ruaud@smile.fr>
 */
class AttributeConfig
{
    /**
     * @var
     */
    private $attributeCollectionFactory;
    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var AutocompleteHelper
     */
    private $autocompleteHelper;

    /**
     * @var \Magento\Framework\App\CacheInterface
     */
    private $cache;

    /**
     * @var null
     */
    private $autocompleteAttributes = null;

    public function __construct(
        AttributeCollectionFactory $attributeCollectionFactory,
        StoreManagerInterface $storeManager,
        AutocompleteHelper $autocompleteHelper,
        CacheInterface $cache
    ) {
        $this->attributeCollectionFactory = $attributeCollectionFactory;
        $this->storeManager               = $storeManager;
        $this->autocompleteHelper         = $autocompleteHelper;
        $this->cache                      = $cache;
    }

    /**
     * List of attributes displayed in autocomplete.
     *
     * @return AttributeCollection
     */
    public function getAutocompleteAttributes()
    {
        if (null === $this->autocompleteAttributes) {
            $storeId  = $this->storeManager->getStore()->getId();
            $cacheKey = 'instant_search_attr' . $storeId;
            $attributesList = $this->cache->load($cacheKey);

            if (false === $attributesList) {
                $collection = $this->getAutocompleteAttributeCollection();
                foreach ($collection as $attribute) {
                    $this->autocompleteAttributes[] = [
                        'filter_field' => $this->autocompleteHelper->getAttributeAutocompleteField($attribute),
                        'code'         => $attribute->getAttributeCode(),
                        'store_label'  => $attribute->getStoreLabel()
                    ];
                }
                $this->cache->save(serialize($this->autocompleteAttributes), $cacheKey);
            } else {
                $this->autocompleteAttributes = unserialize($attributesList);
            }
        }

        return $this->autocompleteAttributes;
    }

    /**
     * Get filter field for an attribute.
     *
     * @param ProductAttributeInterface $attribute Product attribute.
     *
     * @return string
     */
    public function getFilterField(ProductAttributeInterface $attribute)
    {
        return $this->autocompleteHelper->getAttributeAutocompleteField($attribute);
    }

    /**
     * Init the list of attribute displayed in autocomplete.
     *
     * @return \Magento\Catalog\Model\ResourceModel\Product\Attribute\Collection
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    private function getAutocompleteAttributeCollection()
    {
        $storeId = $this->storeManager->getStore()->getId();
        $collection = $this->attributeCollectionFactory->create();

        $collection->addStoreLabel($storeId)
            ->addFieldToFilter('is_displayed_in_autocomplete', true)
            ->load();

        return $collection;
    }
}
