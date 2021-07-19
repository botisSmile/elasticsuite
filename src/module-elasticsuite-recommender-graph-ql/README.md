## GraphQL implementation for Elasticsuite Recommender module

This module provides the graphQL implementation for related, upsell and crosssell products computed by Elasticsuite Recommender module.


## Example query : cross-sell, related and upsell

You can use the legacy Magento2 query to retrieve linked products like this : 

```graphql
{
  products(filter: { sku: { eq: "VT01" } }) {
    total_count
    items {
      id
      name
      sku
      url_key
      stock_status
      new_from_date
      new_to_date
      special_price
      special_from_date
      special_to_date
      __typename
      upsell_products {
        id
        name
        sku
        url_key
        __typename
      },
      crosssell_products {
        id
        name
        sku
        url_key
        __typename
      },
      related_products {
        id
        name
        sku
        url_key
        __typename
      }      
    }
  }
}
```
