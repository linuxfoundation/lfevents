import { normalizeFilterValue, sponsorMatchesFilters } from './filter';

const sponsor = {
	level: ' Gold ',
	categories: [ 'Cloud Native', 'AI' ],
};

describe( 'sponsor directory filtering', () => {
	test( 'normalizes whitespace and case', () => {
		expect( normalizeFilterValue( '  Cloud Native ' ) ).toBe(
			'cloud native'
		);
	} );

	test( 'matches all sponsors when both filters are empty', () => {
		expect( sponsorMatchesFilters( sponsor ) ).toBe( true );
	} );

	test( 'matches category and level independently', () => {
		expect( sponsorMatchesFilters( sponsor, 'ai', '' ) ).toBe( true );
		expect( sponsorMatchesFilters( sponsor, '', 'gold' ) ).toBe( true );
	} );

	test( 'uses AND semantics when both filters are selected', () => {
		expect( sponsorMatchesFilters( sponsor, 'cloud native', 'gold' ) ).toBe(
			true
		);
		expect(
			sponsorMatchesFilters( sponsor, 'cloud native', 'silver' )
		).toBe( false );
		expect( sponsorMatchesFilters( sponsor, 'security', 'gold' ) ).toBe(
			false
		);
	} );

	test( 'does not match specific filters when metadata is empty', () => {
		expect(
			sponsorMatchesFilters( { level: '', categories: [] }, 'ai', '' )
		).toBe( false );
		expect(
			sponsorMatchesFilters( { level: '', categories: [] }, '', 'gold' )
		).toBe( false );
	} );
} );
