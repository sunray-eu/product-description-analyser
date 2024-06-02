import $ from 'jquery';

$(document).ready(function() {
    // Initialize descriptions if not already defined
    if (typeof window.descriptions === 'undefined' || typeof window.descriptions !== 'object') {
        window.descriptions = {};
    }

    /**
     * Add or update a product in the global descriptions object.
     *
     * @param {Object} product - The product object to add or update.
     * @returns {Object} - The updated descriptions object.
     */
    function pushToDescriptions(product) {
        window.descriptions[product.id] = product;
        return window.descriptions;
    }

    /**
     * Get all product descriptions as an array.
     *
     * @returns {Array} - Array of product descriptions.
     */
    function getDescriptions() {
        return Object.values(window.descriptions);
    }

    /**
     * Update the minimal and maximal scores in the dashboard.
     *
     * @param {Array} descriptions - Array of product descriptions.
     */
    function updateScores(descriptions) {
        const validScores = descriptions.length > 0 && descriptions.every(description => typeof description['score'] === 'number' && description['score'] !== undefined);

        if (validScores) {
            const allScores = descriptions.map(description => description.score);
            const resultsContentElement = $('#dashboard');

            const minScore = Math.min(...allScores);
            const maxScore = Math.max(...allScores);

            const minScoreDescription = descriptions.find(description => description.score === minScore);
            const maxScoreDescription = descriptions.find(description => description.score === maxScore);

            const minScoreElmLink = $('<a>').attr('href', `#${minScoreDescription.hash}`).addClass('score-link').text(minScore);
            const maxScoreElmLink = $('<a>').attr('href', `#${maxScoreDescription.hash}`).addClass('score-link').text(maxScore);

            const minScoreElm = $('<h2>').text("Minimal score: ").append(minScoreElmLink);
            const maxScoreElm = $('<h2>').text("Maximal score: ").append(maxScoreElmLink);

            resultsContentElement.append(minScoreElm);
            resultsContentElement.append(maxScoreElm);

            $('#dashboard-loader').remove();
        }
    }

    /**
     * Update a product's row in the table and refresh the scores.
     *
     * @param {Object} product - The product object to update.
     */
    function updateList(product) {
        const descriptionRow = $(`#${product.hash}`);

        let rowClass;
        if (typeof product['score'] === 'number') {
            if (product['score'] > 0.5) {
                rowClass = 'table-success';
            } else if (product['score'] < -0.5) {
                rowClass = 'table-danger';
            } else {
                rowClass = 'table-warning';
            }
        }

        descriptionRow.removeClass().addClass(rowClass);
        descriptionRow.find('.score').html(product['score']);

        pushToDescriptions(product);
        updateScores(getDescriptions());
    }

    /**
     * Smooth scroll to the target element and apply zoom animation.
     *
     * @param {String} targetId - The ID of the target element.
     */
    function smoothScrollTo(targetId) {
        const targetElement = $(`#${targetId}`);
        const offset = targetElement.offset().top - ($(window).height() / 2) + (targetElement.height() / 2);

        $('html, body').animate({ scrollTop: offset }, function() {
            targetElement.addClass('zoomed');
            setTimeout(() => {
                targetElement.removeClass('zoomed');
            }, 1500);
        });
    }

    // Listen for click events on score links to trigger smooth scroll and zoom animation
    $(document).on('click', '.score-link', function(event) {
        event.preventDefault();
        const targetId = $(this).attr('href').substring(1);
        smoothScrollTo(targetId);
    });

    // Listen for product updates from the server via Echo
    window.Echo.channel('product.updates')
        .listen('.updated', function(e) {
            console.log("product.updates received with:", e);
            updateList(e.product);
        });
});
