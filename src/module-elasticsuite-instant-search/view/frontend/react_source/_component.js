if (window.ES_REACT_AUTOCOMPLETE_PROPS && (window.ES_REACT_AUTOCOMPLETE_PROPS.formSelector !== undefined)) {
    const elementId = window.ES_REACT_AUTOCOMPLETE_PROPS.formSelector || 'search_mini_form';
    if (document.getElementById(elementId)) {
        const element             = document.getElementById(elementId);
        const ReactESAutocomplete = React.lazy(() => import('ReactESAutocomplete'));
        ReactDOM.render(<React.Suspense fallback={<div dangerouslySetInnerHTML={{__html: element.innerHTML}}/>}>
            <ReactESAutocomplete originalContent={element.innerHTML}/>
        </React.Suspense>, element);
    }
}
