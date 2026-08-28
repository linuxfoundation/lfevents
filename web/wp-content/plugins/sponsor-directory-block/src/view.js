import { sponsorMatchesFilters } from './filter';

const initializeDirectory = ( directory ) => {
	const searchInput = directory.querySelector( '[data-search-filter]' );
	const categorySelect = directory.querySelector( '[data-category-filter]' );
	const levelSelect = directory.querySelector( '[data-level-filter]' );
	const resetButton = directory.querySelector( '[data-reset-filters]' );
	const empty = directory.querySelector( '[data-no-results]' );

	if (
		! searchInput ||
		! categorySelect ||
		! levelSelect ||
		! resetButton ||
		! empty
	) {
		return;
	}

	const cards = Array.from(
		directory.querySelectorAll( '[data-sponsor-card]' )
	);

	const update = () => {
		let visibleCount = 0;

		cards.forEach( ( card ) => {
			let categories = [];
			try {
				categories = JSON.parse( card.dataset.categories || '[]' );
			} catch ( error ) {
				categories = [];
			}

			const matches = sponsorMatchesFilters(
				{
					name: card.dataset.name || '',
					level: card.dataset.level || '',
					categories,
				},
				categorySelect.value,
				levelSelect.value,
				searchInput.value
			);

			card.hidden = ! matches;
			if ( matches ) {
				visibleCount += 1;
			}
		} );

		empty.hidden = visibleCount !== 0;
		resetButton.disabled =
			! categorySelect.value &&
			! levelSelect.value &&
			! searchInput.value.trim();
	};

	searchInput.addEventListener( 'input', update );
	categorySelect.addEventListener( 'change', update );
	levelSelect.addEventListener( 'change', update );
	resetButton.addEventListener( 'click', () => {
		searchInput.value = '';
		categorySelect.value = '';
		levelSelect.value = '';
		update();
	} );
	update();
};

const initializeDirectories = () => {
	document
		.querySelectorAll( '[data-sponsor-directory]' )
		.forEach( initializeDirectory );
};

if ( document.readyState === 'loading' ) {
	document.addEventListener( 'DOMContentLoaded', initializeDirectories );
} else {
	initializeDirectories();
}
