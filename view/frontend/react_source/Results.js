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
                items
            }
        } = this;

        let groupedResults = this.groupBy(items, 'type');

        return (
            <div id="search_autocomplete" className="instant-search-result-box" style={{display: items.length > 0 ? 'flex' : 'none' }}>
                <div className="col-3">
                    <dl id="search_autocomplete_term" className="term">
                        <dt>Search terms</dt>
                        {(groupedResults.term === undefined || groupedResults.term.length === 0) &&
                            <span className="no-results">No results</span>
                        }
                        {groupedResults.term !== undefined && groupedResults.term.length > 0 &&
                            groupedResults.term.map(function(result, index) {
                                    return (
                                        <dd className={result.row_class} role="option" key={index}>
                                        <span className="qs-option-name">{result.title}</span>
                                        <span aria-hidden="true" className="amount"> ({result.num_results})</span>
                                        </dd>
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
                                    <a href={'//' + window.location.hostname + '/' + result.url} alt={result.name}>
                                        <dd className={result.row_class} role="option" key={index}>
                                            <span className="qs-option-name">{result.name}</span>
                                            <span aria-hidden="true" className="amount">{result.num_results}</span>
                                        </dd>
                                    </a>
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
                                        <dd className={result.row_class} role="option" key={index}>
                                        <a className="instant-search-result" href={result.url_path}>
                                            <div className="thumbnail"><img src={'//' + window.location.hostname + '/' + result.thumbnail}/></div>
                                                <div className="info">{result.name}
                                                    <div className="autocomplete-category">in Hoodies &amp; Sweatshirts</div>
                                                <div className="price">
                                                    <span className="autocomplete-price">$ {result.price[0].price}</span>
                                                </div>
                                            </div>
                                        </a>
                                        </dd>
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
