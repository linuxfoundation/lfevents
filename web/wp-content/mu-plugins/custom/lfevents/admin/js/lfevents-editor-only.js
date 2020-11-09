/**
 * JS to load in the WordPress Block Editor only.
 *
 * @package WordPress
 * @since 1.0.0
 */

// @phpcs:disable WordPress.WhiteSpace.OperatorSpacing.NoSpaceAfter
// @phpcs:disable WordPress.WhiteSpace.OperatorSpacing.NoSpaceBefore
// @phpcs:disable Generic.WhiteSpace.ScopeIndent.IncorrectExact
// @phpcs:disable Generic.WhiteSpace.ScopeIndent.Incorrect
// @phpcs:disable PEAR.Functions.FunctionCallSignature.Indent

wp.domReady(
	() => {
	wp.blocks.unregisterBlockType( 'core/buttons' );

	wp.blocks.registerBlockStyle(
		 'core/group',
		[
		{
				name: 'default',
				label: 'Default',
				isDefault: true,
		},
		{
				name: 'border',
				label: 'Border',
		},
		{
				name: 'event-gradient',
				label: 'Event Gradient',
		}
		]
	);
	wp.blocks.registerBlockStyle(
		 'core/heading',
		[
		{
			name: 'default',
			label: 'Default',
			isDefault: true,
		},
		{
			name: 'section-heading',
			label: 'Section Heading',
		},
		{
			name: 'one-line-section-heading',
			label: 'One Line Section Heading',
		}
		]
	);
	wp.blocks.unregisterBlockStyle(
		'core/separator',
		[ 'dots' ]
	);
	wp.blocks.unregisterBlockStyle(
		'core/table',
		[ 'stripes' ]
	);
	wp.blocks.registerBlockStyle(
		 'core/separator',
		[
		{
			name: '50-percent',
			label: '50%',
		},
		{
			name: '33-percent',
			label: '33%',
		}
		]
	);
	wp.blocks.registerBlockStyle(
		 'core/columns',
		[
		{
			name: 'feature-grid',
			label: 'Feature Grid',
		},
		{
			name: 'feature-grid-with-bounce',
			label: 'Feature Grid w Bounce',
		}
		]
	);
	wp.blocks.registerBlockStyle(
		 'core/spacer',
		[
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
		]
	);
	// Responsive Table with Column Headers.
	wp.blocks.registerBlockVariation(
		 'core/table',
		{
		name: 'table-with-column-headers',
		title: 'Table with column headers',
		description: 'A multi column table which will responsively shrink to be easily readable on smaller screens.',
		attributes: {
				className: 'is-style-table-with-column-headers',
				hasFixedLayout: true,
				head: [
				{
					cells: [
						{
							content: 'Time',
							tag: 'th',
						},
						{
							content: 'Day 1',
							tag: 'th',
						},
						{
							content: 'Day 2',
							tag: 'th',
						},
						{
							content: 'Day 3',
							tag: 'th',
						},
						{
							content: 'Day 4',
							tag: 'th',
						},
					]
					}
				],
				body: [
				{
					cells: [
						{
							content: '14:00-15:00',
							tag: 'td',
						},
						{
							content: 'Talk 1',
							tag: 'td',
						},
						{
							content: 'Talk 2',
							tag: 'td',
						},
						{
							content: 'Talk 3',
							tag: 'td',
						},
						{
							content: 'Talk 4',
							tag: 'td',
						},
					]
					},
				{
					cells: [
						{
							content: '15:00-16:00',
							tag: 'td',
						},
						{
							content: 'Talk 1',
							tag: 'td',
						},
						{
							content: 'Talk 2',
							tag: 'td',
						},
						{
							content: 'Talk 3',
							tag: 'td',
						},
						{
							content: 'Talk 4',
							tag: 'td',
						},
					],
					},
				],
		},
		isDefault: false,
		icon: 'editor-table',
		scope: [ 'inserter' ],
	}
		);
	} // end of DOMready.
);
