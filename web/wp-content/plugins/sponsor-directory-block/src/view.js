import { sponsorMatchesFilters } from './filter';

const initializeDirectory = ( directory ) => {
	const categorySelect = directory.querySelector( '[data-category-filter]' );
	const levelSelect = directory.querySelector( '[data-level-filter]' );
	const count = directory.querySelector( '[data-result-count]' );
	const empty = directory.querySelector( '[data-no-results]' );

	if ( ! categorySelect || ! levelSelect || ! count || ! empty ) {
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
					level: card.dataset.level || '',
					categories,
				},
				categorySelect.value,
				levelSelect.value
			);

			card.hidden = ! matches;
			if ( matches ) {
				visibleCount += 1;
			}
		} );

		count.textContent =
			visibleCount === 1 ? '1 sponsor' : `${ visibleCount } sponsors`;
		empty.hidden = visibleCount !== 0;
	};

	categorySelect.addEventListener( 'change', update );
	levelSelect.addEventListener( 'change', update );
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
