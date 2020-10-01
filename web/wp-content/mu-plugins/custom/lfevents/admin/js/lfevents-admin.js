/**
 * Summary. (use period)
 *
 * Description. (use period)
 *
 * @link   URL
 * @file   This files defines the MyClass class.
 * @author AuthorName.
 * @since  x.x.x
 * @package xxx
 */

wp.domReady( () => {

	wp.blocks.registerBlockStyle( 'core/group', [ 
		{
			name: 'default',
			label: 'Default',
			isDefault: true,
		},
		{
			name: 'event-gradient',
			label: 'Event Gradient',
		}
	]);

	wp.blocks.registerBlockStyle( 'core/heading', [ 
		{
			name: 'default',
			label: 'Default',
			isDefault: true,
		},
		{
			name: 'section-heading',
			label: 'Section Heading',
		}
	]);
} );