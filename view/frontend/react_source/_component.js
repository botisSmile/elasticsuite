if (document.getElementById('search_mini_form')) {
    const element = document.getElementById('search_mini_form');
    const ReactESAutocomplete = React.lazy(() => import('ReactESAutocomplete'));
    ReactDOM.render(<React.Suspense fallback={<div dangerouslySetInnerHTML={{__html: element.innerHTML}}/>}>
        <ReactESAutocomplete originalContent={element.innerHTML} />
    </React.Suspense>, element);
}

