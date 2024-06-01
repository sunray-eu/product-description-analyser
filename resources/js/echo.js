import Echo from 'laravel-echo';
import Pusher from 'pusher-js';

window.Pusher = Pusher;

window.Echo = new Echo({
    broadcaster: 'reverb',
    key: import.meta.env.VITE_REVERB_APP_KEY,
    wsHost: import.meta.env.VITE_REVERB_HOST,
    wsPort: import.meta.env.VITE_REVERB_PORT ?? 80,
    wssPort: import.meta.env.VITE_REVERB_PORT ?? 443,
    forceTLS: (import.meta.env.VITE_REVERB_SCHEME ?? 'https') === 'https',
    enabledTransports: ['ws', 'wss'],
});

// add global variable descriptions

// TODO: fix
function pushToDescriptions(product) {
    if (window.descriptions === undefined || typeof window.descriptions !== 'object') {
        window.descriptions = {};
    }

    window.descriptions[product.id] = product
    return window.descriptions;
}

function getDescriptions() {
    if (window.descriptions === undefined || typeof window.descriptions !== 'object') {
        window.descriptions = {};
    }

    return Object.values(window.descriptions);
}

function updateScores(descriptions) {
    const validScores = descriptions.length > 0 && descriptions.every(description => typeof description['score'] === 'number' && description['score'] !== undefined);

    if (validScores) {
        const allScores = descriptions.map((description) => description.score)
        const resultsContentElement = document.getElementById('dashboard');

        const minScore = Math.min(...allScores);
        const maxScore = Math.max(...allScores);

        const minScoreDescription = descriptions.find(description => description.score === minScore);
        const maxScoreDescription = descriptions.find(description => description.score === maxScore);

        const minScoreElmLink = document.createElement('a');
        const maxScoreElmLink = document.createElement('a');

        minScoreElmLink.href = `#description-${minScoreDescription.hash}`;
        maxScoreElmLink.href = `#description-${maxScoreDescription.hash}`;

        minScoreElmLink.textContent = minScore;
        maxScoreElmLink.textContent = maxScore;

        const minScoreElm = document.createElement('h2');
        const maxScoreElm = document.createElement('h2');

        minScoreElm.textContent = "Minimal score: ";
        maxScoreElm.textContent = "Maximal score: ";

        minScoreElm.append(minScoreElmLink);
        maxScoreElm.append(maxScoreElmLink);

        resultsContentElement.append(minScoreElm);
        resultsContentElement.append(maxScoreElm);

        document.getElementById('dashboard-loader')?.remove();

        // Update links with the corresponding description hash
    }
}

function updateList(product) {
    const description_row = document.getElementById(product.hash);

    let row_class;
    if (typeof product['score'] === 'number') {
        if (product['score'] > 0.5)
            row_class = 'table-success';
        else if (product['score'] < -0.5)
            row_class = 'table-danger';
        else
            row_class = 'table-warning';
    }

    description_row.className = row_class;
    description_row.getElementsByClassName('score').item(0).innerHTML = product['score'];

    // TODO: fix this
    // Update the scores in the list
    pushToDescriptions(product);
    updateScores(getDescriptions());
}

window.Echo.channel('product.updates')
    .listen('.updated', (e) => {
        console.log("product.updates received with:", e.product)
        updateList(e.product);
    });
