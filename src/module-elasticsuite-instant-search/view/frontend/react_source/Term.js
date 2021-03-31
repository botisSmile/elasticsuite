import React, { Component } from 'react';

class Term extends Component {
    render() {
        const {
            props : {
                item
            }
        } = this;

        return (
            <a href={item.url} title={item.title} key={item.url} onMouseDown={(e) => e.preventDefault()} >
                <dd className={item.row_class} role="option">
                    <span className="qs-option-name">{item.title}</span>
                    <span aria-hidden="true" className="amount"> ({item.num_results})</span>
                </dd>
            </a>
        );
    }
}

export default Term;