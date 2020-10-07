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

	wp.blocks.registerBlockStyle( 'core/spacer', [
		{
			name: '40-responsive',
			label: '40px Responsive',
		},
		{
			name: '60-responsive',
			label: '60px Responsive',
		},
		{
			name: '80-responsive',
			label: '80px Responsive',
		},
		{
			name: '100-responsive',
			label: '100px Responsive',
		}
	]);

	wp.blocks.registerBlockStyle( 'core/table', [
		{
			name: 'compact',
			label: 'Compact',
		},
		{
			name: 'schedule',
			label: 'Schedule',
		}
	]);

	// end.
} );