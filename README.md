
## General behavior

The module aim is to help populate the related products blocks (upsells, cross-sells, related products) with similar and popular products according to the [GDPR compliant](https://github.com/Smile-SA/elasticsuite/wiki/GDPRCompliance) Elasticsuite tracking module.

For a target product, related products are determined based on past visitor sessions and according to a combination of recommendations rules on concurrent product views, search and add to cart actions, placed orders.
You can read more about the specific rules for [upsell products](#upsell-products-rules), [related products](#related-products-rules) and [cross-sell products](#cross-sell-products-rules).

Please note that on a **Magento Commerce** version, if enabled, the module **replaces** the [Related Product Rules](https://docs.magento.com/m2/2.0/ee/user_guide/marketing/product-related-rules.html) features.
You cannot at this stage combine this module's recommendations and those of the Magento **Target Rule** module.

**Which version should I use ?**

The module version patterns are identical to those of Elasticsuite.

Magento Version                                     | ElasticSuite Version    | Module version | Module composer install                                              | Supported Elasticsearch Version | Actively maintained
----------------------------------------------------|-------------------------|----------------|----------------------------------------------------------------------|---------------------------------|---------------------
Magento **2.2.x** Opensource (CE) / Commerce (EE)   | ElasticSuite **2.6.x**  | **2.6.x**      | ```composer require smile/module-elasticsuite-recommender ~2.6.0```  | 5.x & 6.x                       | No
Magento **<2.3.5** Opensource (CE) / Commerce (EE)  | ElasticSuite **2.8.x**  | **2.8.x**      | ```composer require smile/module-elasticsuite-recommender ~2.8.0```  | 5.x & 6.x                       | No
Magento **>-2.3.5** Opensource (CE) / Commerce (EE) | ElasticSuite **2.9.x**  | **2.9.x**      | ```composer require smile/module-elasticsuite-recommender ~2.9.0```  | 6.x & 7.x                       | **Yes**
Magento **>-2.4.0** Opensource (CE) / Commerce (EE) | ElasticSuite **2.10.x** | **2.10.x**     | ```composer require smile/module-elasticsuite-recommender ~2.10.0``` | 6.x & 7.x                       | **Yes**

## Upsell products rules

* **must**
    * belong to the same categories of the current product
    * have a higher price then the current product (*if enabled*)
* **should**
    * be found in the search results of the search terms used when the product is viewed
    * be one of or [similar](#similarity) to the products that are usually also viewed then the product is viewed
    * be one of or similar to the products manually selected in the admin product page
* **must not**
    * be one of the products manually selected in the admin product page
    * be one of the products already in the visitor's shopping cart
    * be one of the products already bought by the visitor (*if enabled*)

## Related products rules

* **should**
    * be one of or [similar](#similarity) to the products also added to cart when this product is added to cart
    * be one of or similar to the products also ordered when this product is ordered
    * be one of or similar to the products also added to cart along any product added to cart which is also viewed when this product is viewed
    * be one of or similar to the products also ordered along any product ordered which is also viewed when this product is viewed
* **must not**
    * be one of the products manually selected in the admin product page
    * be one of the products already in the visitor's shopping cart
    * be one of the products already bought by the visitor (*if enabled*)
    * be one a composite product (*if enabled*)

## Cross-sell products rules

The rules are similar to those for the Related Products.

* **should**
    * be one of or [similar](#similarity) to the products also added to cart when this product is added to cart
    * be one of or similar to the products also ordered when this product is ordered
    * be one of or similar to the products also added to cart along any product added to cart which is also viewed when this product is viewed
    * be one of or similar to the products also ordered along any product ordered which is also viewed when this product is viewed
* **must not**
    * be one of the products manually selected in the admin product page
    * be one of the products already in the visitor's shopping cart
    * be one of the products already bought by the visitor (*if enabled*)

## Similarity

Similarity is a [*more like this*](https://www.elastic.co/guide/en/elasticsearch/reference/current/query-dsl-mlt-query.html) request based on the `autocomplete` and `autocomplete.shingle` base fields as well as on all the attributes fields that are both **searchable** and either **used for filtering** or **sorting** products lists.

The Elasticsuite documentation has more about [fields properties](https://github.com/Smile-SA/elasticsuite/wiki/Indexing#defining-fields-property-via-configuration) and [attributes configuration](https://github.com/Smile-SA/elasticsuite/wiki/Attribute-configuration).

## Configurable behavior

A new section is available in the store configuration in **Stores > Configuration > ElasticSuite > Recommender** with **store view** level settings.

### Generic settings

For each type of recommendations (upsells, cross-sells, related products) you can configure independently
* the maximum number of products to show in the block
* whether to show
    * both manually selected products (at the product level) and products automatically recommended based on past events
    * or only manually selected products
    * or only products automatically recommended based on past events

#### Excluding products already bought by the visitor

Two settings common to all type of recommendations allows to exclude products that have already been bought by the visitor
* a `Yes/No` configuration setting to enable the exclusion
* an additional setting to configure the size of the **exclusion window** in days

##### Exclusion window

For performance reasons, the Elasticsuite Tracker indices are used instead of the Magento database to determine the products already bought by a returning visitor/customer.

This feature is thus highly dependent on the tracker data retention
* the size of the exclusion window cannot be bigger than the configured **Retention delay** in **Stores > Configuration > ElasticSuite > Tracking > Global Configuration**
* a returning visitor will not be identified as such past the configured unique returning [visitor cookie lifetime](https://github.com/Smile-SA/elasticsuite/wiki/GDPRCompliance#customer-information-and-user-consent-for-tracking) in **Stores > Configuration > ElasticSuite > Tracking > Session Configuration**.

Note that as all data past the **retention delay** are lost, products bought in a order placed beyond that delay cannot be excluded.

### Related products specific settings

A `Yes/No` settings allows to exclude composite (bundle, grouped, configurable) products from the related products lists so that they contain only products that can be added to the cart directly from the product page.

### Upsell products specific settings

#### Native complement by bundle products

A `Yes/No` setting allows the native behavior of automatically complementing the manual upsell products selection by bundle products the current product appears in.

#### Force a higher price

Another `Yes/No` setting allows to force the recommended upsell products to have a higher base price than the current product.

Note that using this setting might reduce the possibility of bundle or configured products (or any products whose price vary according to product options or configuration) being recommended for a given product.
