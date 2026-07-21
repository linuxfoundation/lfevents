export const normalizeFilterValue = ( value = '' ) =>
	String( value ).trim().toLocaleLowerCase();

export const sponsorMatchesFilters = (
	sponsor,
	selectedCategory = '',
	selectedLevel = ''
) => {
	const category = normalizeFilterValue( selectedCategory );
	const level = normalizeFilterValue( selectedLevel );
	const sponsorLevel = normalizeFilterValue( sponsor.level );
	const sponsorCategories = Array.isArray( sponsor.categories )
		? sponsor.categories.map( normalizeFilterValue )
		: [];

	return (
		( ! category || sponsorCategories.includes( category ) ) &&
		( ! level || sponsorLevel === level )
	);
};
