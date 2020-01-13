import React, { Component } from 'react';

class Results extends Component {
    componentWillMount() {
        this.setState((state, props) => {
            let items = props.items || false;

            return {
                items: items
            };
        });
    }

    render() {
        const {
            props : {
                items
            }
        } = this;
        console.log(items);
        return (
            <div id="search_autocomplete" className="search-autocomplete" style={{display: items.length > 0 ? 'block' : 'none' }}>
                {items.map(function(result, index) {
                    return (
                        <dd className="{result.row_class}" role="option" key={index}>
                            <span className="qs-option-name">{result.title}</span>
                            <span aria-hidden="true" className="amount">{result.num_results}</span>
                        </dd>
                    );
                })}
            </div>
        );
    }
}

export default Results;
