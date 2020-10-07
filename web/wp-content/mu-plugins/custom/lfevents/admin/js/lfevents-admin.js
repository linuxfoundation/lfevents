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
	wp.blocks.unregisterBlockStyle(
		'core/separator',
		[ 'dots' ]
	);
	wp.blocks.registerBlockStyle( 'core/separator', [ 
		{
			name: '50-percent',
			label: '50%',
		},
		{
			name: '33-percent',
			label: '33%',
		}
	]);
	wp.blocks.registerBlockStyle( 'core/columns', [ 
		{
			name: 'feature-grid',
			label: 'Feature Grid',
		}
	]);
} );