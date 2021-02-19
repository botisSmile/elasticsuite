import React, { Component } from 'react';

class Category extends Component {

    render() {
        const {
            props : {
                item
            }
        } = this;

        let currencyCode = 'EUR';

        return (
            <a href={'//' + window.location.hostname + '/' + item.url} title={item.name} onMouseDown={(e) => e.preventDefault()}>
                <dd className={item.row_class} role="option">
                    {item.tree !== undefined && item.tree.length > 0 &&
                    <span className="qs-option-name">{item.tree.join(' > ')}</span>
                    }
                    <span aria-hidden="true" className="amount">{item.num_results}</span>
                </dd>
            </a>
        );
    }
}

export default Category;
