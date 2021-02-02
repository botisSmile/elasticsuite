import React, { Component } from 'react';

import Product from './Product';
import Category from './Category';
import Term from './Term';

class Results extends Component {
    componentWillMount() {
        this.setState((state, props) => {
            let items = props.items || false;

            return {
                items: items
            };
        });
    }

    groupBy(arr, property) {
        return arr.reduce(function(memo, x) {
            if (!memo[x[property]]) { memo[x[property]] = []; }
            memo[x[property]].push(x);
            return memo;
        }, {});
    }

    render() {
        const {
            props : {
                items,
                expanded
            }
        } = this;

        let groupedResults = this.groupBy(items, 'type');

        return (
            <div id="search_autocomplete" className="instant-search-result-box" style={{display: ((items.length > 0) && (expanded === true)) ? 'flex' : 'none' }}>
                <div className="col-3">
                    <dl id="search_autocomplete_term" className="term">
                        <dt>Search terms</dt>
                        {(groupedResults.term === undefined || groupedResults.term.length === 0) &&
                            <span className="no-results">No results</span>
                        }
                        {groupedResults.term !== undefined && groupedResults.term.length > 0 &&
                            groupedResults.term.map(function(result, index) {
                                return (
                                    <Term item={result} key={"term" + index}/>
                                );
                            })
                        }
                    </dl>
                <dl id="search_autocomplete_category" className="category">
                    <dt>Categories</dt>
                        {(groupedResults.category === undefined || groupedResults.category.length === 0) &&
                            <span className="no-results">No results</span>
                        }
                        {groupedResults.category !== undefined && groupedResults.category.length > 0 &&
                            groupedResults.category.map(function(result, index) {
                                return (
                                    <Category item={result} key={result.entity_id || "category" + index}/>
                                );
                            })
                        }
                    </dl>
                </div>
                <div className="col-7">
                    <dl id="search_autocomplete_product" className="product">
                        <dt>Products</dt>
                            {(groupedResults.product === undefined || groupedResults.product.length === 0) &&
                                <span className="no-results">No results</span>
                            }
                            {groupedResults.product !== undefined && groupedResults.product.length > 0 &&
                                groupedResults.product.map(function(result, index) {
                                    return (
                                        <Product item={result} key={result.entity_id || "product" + index} />
                                    );
                                })
                            }
                    </dl>
                </div>
            </div>
        );
    }
}

export default Results;
