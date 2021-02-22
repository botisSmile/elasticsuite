<?php
/**
 * Autocomplete terms item factory.
 *
 * @category  Smile
 * @package   Smile\ElasticsuiteInstantSearch
 * @author    Richard Bayet <richard.bayet@smile.fr>
 * @copyright 2020 Smile
 * @license   Open Software License ("OSL") v. 3.0
 */


namespace Smile\ElasticsuiteInstantSearch\Model\Autocomplete\Terms;

use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\UrlInterface;

/**
 * * Autocomplete terms item factory.
 *
 * @category  Smile
 * @package   Smile\Elasticsuite
 * @author    Richard Bayet <richard.bayet@smile.fr>
 */
class ItemFactory extends \Magento\Search\Model\Autocomplete\ItemFactory
{
    /**
     * @var UrlInterface
     */
    private $urlBuilder;

    /**
     * Constructor.
     *
     * @param ObjectManagerInterface $objectManager Object manager used to instantiate new item.
     * @param UrlInterface           $urlBuilder    URL Builder
     */
    public function __construct(ObjectManagerInterface $objectManager, UrlInterface $urlBuilder)
    {
        parent::__construct($objectManager);
        $this->urlBuilder = $urlBuilder;
    }

    /**
     * {@inheritDoc}
     */
    public function create(array $data)
    {
        $data['url'] = $this->getUrl($data);

        return parent::create($data);
    }

    /**
     * Returns autocompelete result URL.
     *
     * @param array $data Autocomplete data.
     *
     * @return string
     */
    private function getUrl(array $data)
    {
        $urlParams = ['q' => $data['title']];

        return $this->urlBuilder->getUrl('catalogsearch/result', ['_query' => $urlParams]);
    }
}