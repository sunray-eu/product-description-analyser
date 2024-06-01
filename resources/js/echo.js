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

// TODO: fix
function updateScores(scores) {
    const validScores = scores.filter(score => typeof score === 'number');
    if (validScores.length > 0) {
        const dasboardElement = document.getElementById('dashboard');

        const minScore = Math.min(...validScores);
        const maxScore = Math.max(...validScores);

        const minScoreDescription = scores.find(score => score === minScore);
        const maxScoreDescription = scores.find(score => score === maxScore);

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

        dasboardElement.append(minScore);
        dasboardElement.append(maxScore);

        document.getElementById('dashboard-loader').remove();

        // Update links with the corresponding description hash
    }
}

export default function updateList(product) {
    const description_row = document.getElementById(`description-${product.hash}`);
    let row_class;
    if (typeof product['score'] === 'number') {
        if (product['score'] > 0.5)
            row_class = 'table-success';
        else if (product['score'] < 0.5)
            row_class = 'table-danger';
        else
            row_class = 'table-warning';
    }

    description_row.className = row_class;
    description_row.getElementsByClassName('score').item(0).innerHTML = product['score'];

    // TODO: fix this
    // Update the scores in the list
    const scores = Array.from(document.getElementsByClassName('score')).map(scoreElement => {
        const score = parseFloat(scoreElement.textContent);
        return typeof (score) == 'number' ? score : null;
    });
    updateScores(scores);
}

window.Echo.channel('product.updates')
    .listen('.updated', (e) => {
        updateList(e.product);
    });
