import React, { Component } from 'react';
import Price from "@magento/peregrine/lib/Price";

class Product extends Component {

    render() {
        const {
            props : {
              item
            }
        } = this;

        let currencyCode = 'EUR';

        return (
            <dd className={item.row_class} role="option">
                <a className="instant-search-result" href={'//' + window.location.hostname + '/' + item.url} alt={item.name}>
                    <div className="thumbnail"><img src={'//' + window.location.hostname + '/' + item.thumbnail}/></div>
                    <div className="info">{item.name}
                        <div className="autocomplete-category">in {item.highlightCategory}</div>
                        <div className="price">
                            <Price currencyCode={currencyCode} value={item.price[0].price} />
                        </div>
                    </div>
                </a>
            </dd>
        );
    }
}

export default Product;
