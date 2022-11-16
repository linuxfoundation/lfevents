// phpcs:IgnoreFile.

/* Add custom attribute to sidebar of list block */
const { __ } = wp.i18n;

// Enable custom attributes on List block.
const enableSidebarSelectOnBlocks = [ 'core/list' ];

const { createBlock } = wp.blocks;
const { createHigherOrderComponent } = wp.compose;
const { Fragment } = wp.element;
const { InspectorControls } = wp.blockEditor;
const { PanelBody, SelectControl } = wp.components;

import classnames from 'classnames';

function closest( array, num ) {
	let i = 0;
	let minDiff = 1000;
	let ans;
	for ( i in array ) {
		const m = Math.abs( num - array[ i ] );
		if ( m < minDiff ) {
			minDiff = m;
			ans = array[ i ];
		}
	}
	return ans;
}

/**
 * Declare our custom attribute
 *
 * @param {*} settings
 * @param {*} name
 * @return {*} value
 */
const setListAttributes = ( settings, name ) => {
	// Do nothing if it's another block than our defined ones.
	if ( ! enableSidebarSelectOnBlocks.includes( name ) ) {
		return settings;
	}

	return Object.assign( {}, settings, {
		attributes: Object.assign( {}, settings.attributes, {
			selectedIcon: { type: 'string' },
			listGap: { type: 'string' },
			iconSize: { type: 'string' },
			columnCount: { type: 'string' },
		} ),
	} );
};
wp.hooks.addFilter(
	'blocks.registerBlockType',
	'core-block-customisations/set-list-attributes',
	setListAttributes
);

function addListTransforms( settings, name ) {
	// Do nothing if it's another block than our defined ones.
	if ( ! enableSidebarSelectOnBlocks.includes( name ) ) {
		return settings;
	}
	const transforms = settings.transforms;

	if ( typeof transforms.from === 'undefined' ) {
		transforms.from = [];
	}

	const customConversion = {
		type: 'block',
		blocks: [ 'lf/icon-list' ],
		transform: ( attributes ) => {
			// attributes is an object.
			// Clone attributes object.
			const listAttributes = { ...attributes };
			// need to add wrapping ul.
			const values = '<ul>' + attributes.values + '</ul>';
			// process HTML in to blocks.
			const gutblock = wp.blocks.rawHandler( {
				HTML: values,
			} );
			// Pick the first sub array.
			const block = gutblock[ 0 ];

			// Process selectedIcon
			const icon = listAttributes.selectedIcon;
			const iconUpdated = icon.replace( 'is-style-list-', 'has-icon-' );
			listAttributes.selectedIcon = iconUpdated;

			// Process listGap.
			const gap = listAttributes.listGap;
			const gapOptions = [ 0, 10, 15, 20, 30, 40 ];
			const gapClosest = closest( gapOptions, gap );
			const gapUpdated = `has-list-gap-${ gapClosest }`;
			listAttributes.listGap = gapUpdated;

			// Process columnCount
			const column = listAttributes.columnCount;
			let columnUpdated = '';
			if ( column === 2 ) {
				columnUpdated = 'has-two-columns';
			}
			if ( column === 3 ) {
				columnUpdated = 'has-three-columns';
			}
			listAttributes.columnCount = columnUpdated;

			// Process iconSize.
			const size = listAttributes.iconSize;
			const sizeOptions = [ 0, 10, 15, 20, 30, 40 ];
			const sizeClosest = closest( sizeOptions, size );
			const sizeUpdated = `has-icon-size-${ sizeClosest }`;
			listAttributes.iconSize = sizeUpdated;

			delete listAttributes.values;
			delete listAttributes.type;

			// Process the innerblocks to pass the main list attributes down.
			// Probably a better/easier way to do this in Gutenberg but I didnt find it.
			const inner = block.innerBlocks;
			inner.forEach( ( blocks ) => {
				if ( blocks.innerBlocks.length > 0 ) {
					blocks.innerBlocks.forEach( ( innerblock ) => {
						return innerblock.attributes = {
							...listAttributes,
						};
					}
					);
				}
			}, inner );

			return createBlock(
				block.name,
				{
					...listAttributes,
				},
				inner
			);
		},
	};
	transforms.from.push( customConversion );
	settings.transforms = transforms;
	return settings;
}
wp.hooks.addFilter(
	'blocks.registerBlockType',
	'core-block-customisations/add-list-transforms',
	addListTransforms
);

/**
 * Add Custom Settings to List Sidebar
 */
const addListSidebar = createHigherOrderComponent( ( BlockEdit ) => {
	return ( props ) => {
		// If current block is not allowed
		if ( ! enableSidebarSelectOnBlocks.includes( props.name ) ) {
			return <BlockEdit { ...props } />;
		}

		const { attributes, setAttributes } = props;
		const { selectedIcon, columnCount, iconSize, listGap } = attributes;

		return (
			<Fragment>
				<BlockEdit { ...props } />
				<InspectorControls>
					<PanelBody title={ __( 'Additional Options' ) }>
						<SelectControl
							label={ __( 'No. of columns' ) }
							value={ columnCount }
							options={ [
								{
									label: __( '1 Column' ),
									value: '',
								},
								{
									label: __( '2 Columns' ),
									value: 'has-two-columns',
								},
								{
									label: __( '3 Columns' ),
									value: 'has-three-columns',
								},
							] }
							onChange={ ( value ) =>
								setAttributes( {
									columnCount: '' !== value ? value : '',
								} )
							}
						/>
						<SelectControl
							label={ __( 'List item gap' ) }
							value={ listGap }
							options={ [
								{
									label: __( 'Default' ),
									value: '',
								},
								{
									label: __( '10px' ),
									value: 'has-list-gap-10',
								},
								{
									label: __( '15px' ),
									value: 'has-list-gap-15',
								},
								{
									label: __( '20px' ),
									value: 'has-list-gap-20',
								},
								{
									label: __( '30px' ),
									value: 'has-list-gap-30',
								},
								{
									label: __( '40px' ),
									value: 'has-list-gap-40',
								},
							] }
							onChange={ ( value ) =>
								setAttributes( {
									listGap: '' !== value ? value : '',
								} )
							}
						/>
						<SelectControl
							label={ __( 'List icon' ) }
							value={ selectedIcon }
							options={ [
								{
									label: __( 'Default' ),
									value: '',
								},
								{
									label: __( 'Angle Right' ),
									value: 'has-icon-angle-right',
								},
								{
									label: __( 'Check' ),
									value: 'has-icon-check',
								},
								{
									label: __( 'Plus' ),
									value: 'has-icon-plus',
								},
							] }
							onChange={ ( value ) =>
								setAttributes( {
									selectedIcon: '' !== value ? value : '',
								} )
							}
						/>
						{ selectedIcon && (
							<SelectControl
								label={ __( 'Icon Size' ) }
								value={ iconSize }
								options={ [
									{
										label: __( '10px' ),
										value: 'has-icon-size-10',
									},
									{
										label: __( '15px' ),
										value: 'has-icon-size-15',
									},
									{
										label: __( '20px' ),
										value: 'has-icon-size-20',
									},
									{
										label: __( '30px' ),
										value: 'has-icon-size-30',
									},
									{
										label: __( '40px' ),
										value: 'has-icon-size-40',
									},
								] }
								onChange={ ( value ) =>
									setAttributes( {
										iconSize: '' !== value ? value : '',
									} )
								}
							/>
						) }
					</PanelBody>
				</InspectorControls>
			</Fragment>
		);
	};
}, 'addListSidebar' );

wp.hooks.addFilter(
	'editor.BlockEdit',
	'core-block-customisations/add-list-sidebar',
	addListSidebar
);

/**
 * Add custom class to block in Edit
 */
const addListSidebarProp = createHigherOrderComponent( ( BlockListBlock ) => {
	return ( props ) => {
		// If current block is not allowed.
		if ( ! enableSidebarSelectOnBlocks.includes( props.name ) ) {
			return <BlockListBlock { ...props } />;
		}

		const { attributes } = props;
		const { selectedIcon, columnCount, iconSize, listGap } = attributes;

		let classes;
		if ( selectedIcon || columnCount || iconSize || listGap ) {
			classes = classnames(
				selectedIcon,
				columnCount,
				iconSize,
				listGap
			);
		}

		return (
			<BlockListBlock
				{ ...props }
				className={ classes }
			/>
		);
	};
}, 'addListSidebarProp' );

wp.hooks.addFilter(
	'editor.BlockListBlock',
	'core-block-customisations/add-list-sidebar-prop',
	addListSidebarProp
);

/**
 * Save our custom attribute
 *
 * @param {*} extraProps
 * @param {*} blockType
 * @param {*} attributes
 * @return {*} value
 */
const saveListAttributes = ( extraProps, blockType, attributes ) => {
	// Do nothing if it's another block than our defined ones.
	if ( enableSidebarSelectOnBlocks.includes( blockType.name ) ) {
		const { selectedIcon, columnCount, iconSize, listGap } = attributes;

		if ( selectedIcon || columnCount || iconSize || listGap ) {
			extraProps.className = classnames(
				extraProps.className,
				selectedIcon,
				columnCount,
				iconSize,
				listGap
			);
		}
		// Object.assign( extraProps );
	}
	return extraProps;
};
wp.hooks.addFilter(
	'blocks.getSaveContent.extraProps',
	'core-block-customisations/save-list-attributes',
	saveListAttributes
);
