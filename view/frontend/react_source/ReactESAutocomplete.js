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
            loading: false,
        };
    }

    onChange(event) {
        const { url, minSearchLength, storeCode, state : {loading} } = this;

        let data = {q: event.target.value};
        if (storeCode !== null) {
            data.__store = storeCode;
        }

        let queryString = Object.keys(data).map(key => key + '=' + data[key]).join('&');
        this.setState((state) => { return {value: data.q}; });

        if (data.q.length < minSearchLength) {
            this.setState((state) => { return { results: []}});

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
                this.setState((state) => {
                    return {results: responseJson, loading: false};
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
