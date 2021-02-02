import React, { Component } from 'react';

class Term extends Component {
    render() {
        const {
            props : {
                item
            }
        } = this;

        return (
            <dd className={item.row_class} role="option">
                <span className="qs-option-name">{item.title}</span>
                <span aria-hidden="true" className="amount"> ({item.num_results})</span>
            </dd>
        );
    }
}

export default Term;
