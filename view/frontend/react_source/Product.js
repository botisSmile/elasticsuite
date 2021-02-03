import React, { Component } from 'react';
import Price from "@magento/peregrine/lib/Price";

class Product extends Component {
    constructor(props) {
        super(props);

        if (window && window.ES_REACT_AUTOCOMPLETE_PROPS) {
            props = {...props, ...window.ES_REACT_AUTOCOMPLETE_PROPS};
        }

        this.currencyCode = props.currencyCode || 'USD';
    }

    render() {
        const {
            props : {
              item
            },
            currencyCode
        } = this;

        return (
            <dd className={item.row_class} role="option">
                <a className="instant-search-result" href={'//' + window.location.hostname + '/' + item.url} alt={item.name} onMouseDown={(e) => e.preventDefault()}>
                    <div className="thumbnail"><img src={'//' + window.location.hostname + '/' + item.thumbnail}/></div>
                    <div className="info">{item.name}
                        <div className="autocomplete-category">in {item.highlightCategory}</div>
                        <div className="price">
                            <Price currencyCode={this.currencyCode} value={item.price[0].price} />
                        </div>
                    </div>
                </a>
            </dd>
        );
    }
}

export default Product;
