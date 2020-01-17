import React, {Component, useState, useEffect} from 'react';
import Results from './Results';

class ReactESAutocomplete extends Component {
    constructor(props) {
        super(props);

        if (window && window.ES_REACT_AUTOCOMPLETE_PROPS) {
            props = {...props, ...window.ES_REACT_AUTOCOMPLETE_PROPS};
        }

        this.resultsElement = React.createRef();

        this.name = props.name || '';
        this.placeholder = props.placeholder || '';
        this.maxLength = props.maxLength || 255;
        this.formSelector = props.name || "#search_mini_form";
        this.url = props.url || '';
        this.destinationSelector = props.name || "#search_autocomplete";
        this.templates = props.templates ||  [];
        this.priceFormat = props.priceFormat || '';
        this.minSearchLength = props.minSearchLength || 2;
        this.storeCode = props.storeCode || null;

        this.state = {
            value : props.value || '',
            results : [],
            resultsBuffer : {},
            loading: false,
        };
    }

    onChange(event) {
        const { url, minSearchLength, storeCode, state : {loading, resultsBuffer} } = this;

        let data = {q: event.target.value};
        if (storeCode !== null) {
            data.__store = storeCode;
        }
        let queryString = Object.keys(data).map(key => key + '=' + data[key]).join('&');

        this.setState((state) => { return {value: data.q}; });

        // If search is too short, do nothing.
        if (data.q.length < minSearchLength) {
            this.setState((state) => { return { results: []}});

            return;
        }

        // Compute a cache key for the current (trimmed) input text.
        let hash = window.btoa(data.q.trim());
        // Same search has been already processed, serve the results from the cache.
        if (resultsBuffer[hash] !== undefined) {
            this.setState((state) => {return {results: resultsBuffer[hash], loading: false};});

            return;
        }

        fetch(url + '?' + queryString, {
            method: "GET",
            headers: {"Content-Type": "application/json"}
        })
            .then(response => {
                if (!response.ok) {
                    throw Error(response.statusText);
                }
                this.setState((state) => { return {loading: true}; });
                return response;
            })
            .then(response => response.json())
            .then(responseJson => {
                // Append results to the cache of previous requests.
                resultsBuffer[hash] = responseJson;
                this.setState((state) => {
                    return {results: responseJson, resultsBuffer: resultsBuffer, loading: false};
                });
                console.log(this.state);
            })
        //.catch(error => setError(error));

    }

    render() {
        const {
            name,
            value,
            placeholder,
            maxLength,
            state : {
                results
            }
        } = this;

        return (
            <div className="control">
            <input id="search"
                   type="text"
                   name={name}
                   defaultValue={value}
                   placeholder={placeholder}
                   className="input-text"
                   maxLength={maxLength}
                   role="combobox"
                   aria-haspopup="false"
                   aria-autocomplete="both"
                   autoComplete="off"
                   onChange={this.onChange.bind(this)}
                   data-block="autocomplete-form" data-rorua="react"/>
                <Results ref={this.resultsElement} items={results}/>
            </div>
        );
    }
};

export default ReactESAutocomplete;
