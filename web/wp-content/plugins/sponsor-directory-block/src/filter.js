export const normalizeFilterValue = ( value = '' ) =>
	String( value ).trim().toLocaleLowerCase();

export const sponsorMatchesFilters = (
	sponsor,
	selectedCategory = '',
	selectedLevel = '',
	searchTerm = ''
) => {
	const category = normalizeFilterValue( selectedCategory );
	const level = normalizeFilterValue( selectedLevel );
	const search = normalizeFilterValue( searchTerm );
	const sponsorLevel = normalizeFilterValue( sponsor.level );
	const sponsorName = normalizeFilterValue( sponsor.name );
	const sponsorCategories = Array.isArray( sponsor.categories )
		? sponsor.categories.map( normalizeFilterValue )
		: [];

	return (
		( ! category || sponsorCategories.includes( category ) ) &&
		( ! level || sponsorLevel === level ) &&
		( ! search || sponsorName.includes( search ) )
	);
};
