/**
* This file is a dummy file that doesn't do anything except
* does a fake registration of every Stackable block so that
* the WordPress plugin directory detects them and lists them
* in the Stackable plugin page.
*
* This file is auto-generated from the build process.
*/

registerBlockType( 'ugb/accordion', {
	title: __( 'Accordion', i18n ),
	description: __( 'A title that your visitors can toggle to view more text. Use as FAQs or multiple ones for an Accordion.', i18n ),
	icon: AccordionIcon,
	category: 'stackable',
	keywords: [
		__( 'Accordion', i18n ),
		__( 'Toggle', i18n ),
		__( 'Stackable', i18n ),
	],
	attributes: schema,

	deprecated,
	edit,
	save,

	supports: {
		inserter: ! disabledBlocks.includes( name ), // Hide if disabled.
	},

	// Stackable modules.
	modules: {
		'advanced-general': true,
		'advanced-block-spacing': true,
		// 'advanced-column-spacing': {
		// 	columnGap: false,
		// },
		'advanced-responsive': true,
		// 'block-background': true,
		// 'block-separators': true,
		// 'block-title': true,
		'content-align': true,
		'block-designs': true,
		'custom-css': {
			default: applyFilters( 'stackable.cta.custom-css.default', '' ),
		},
	},
} )
registerBlockType( 'ugb/blockquote', {
	title: __( 'Blockquote', i18n ),
	description: __( 'Display a quote in style.', i18n ),
	icon: BlockquoteIcon,
	category: 'stackable',
	keywords: [
		__( 'Blockquote', i18n ),
		__( 'Stackable', i18n ),
	],
	supports: {
		align: [ 'center', 'wide', 'full' ],
		inserter: ! disabledBlocks.includes( name ), // Hide if disabled.
	},
	attributes: schema,

	deprecated,
	edit,
	save,

	// Stackable modules.
	modules: {
		'advanced-general': true,
		'advanced-block-spacing': true,
		'advanced-column-spacing': {
			columnGap: false,
		},
		'advanced-responsive': true,
		'block-background': true,
		'block-separators': true,
		// 'block-title': true,
		'content-align': true,
		'block-designs': true,
		'custom-css': {
			default: applyFilters( 'stackable.blockquote.custom-css.default', '' ),
		},
	},
} )
registerBlockType( 'ugb/blog-posts', {
	title: __( 'Posts', i18n ),
	description: __( 'Your latest blog posts. Use this to showcase a few of your posts in your landing pages.', i18n ),
	icon: BlogPostsIcon,
	category: 'stackable',
	keywords: [
		__( 'Blog Posts', i18n ),
		__( 'Stackable', i18n ),
	],
	supports: {
		align: [ 'center', 'wide', 'full' ],
		inserter: ! disabledBlocks.includes( name ), // Hide if disabled.
	},
	attributes: schema,

	save,
	edit,
	deprecated,

	// Stackable modules.
	modules: {
		'advanced-general': true,
		'advanced-block-spacing': true,
		'advanced-column-spacing': {
			verticalColumnAlign: true,
			verticalContentAlign: false,
		},
		'advanced-responsive': true,
		'block-background': true,
		'block-separators': true,
		'block-title': true,
		'content-align': true,
		'block-designs': true,
		'custom-css': {
			default: applyFilters( 'stackable.blog-posts.custom-css.default', '' ),
		},
	},
} )
registerBlockType( 'ugb/button', {
	title: __( 'Button', i18n ),
	description: __( 'Add a customizable button.', i18n ),
	icon: ButtonIcon,
	category: 'stackable',
	keywords: [
		__( 'Button', i18n ),
		__( 'Stackable', i18n ),
	],
	attributes: schema,
	supports: {
		inserter: ! disabledBlocks.includes( name ), // Hide if disabled.
	},

	deprecated,
	edit,
	save,

	// Stackable modules.
	modules: {
		'advanced-general': true,
		'advanced-block-spacing': true,
		'advanced-responsive': true,
		'block-background': true,
		'block-separators': true,
		// 'block-title': true,
		'content-align': true,
		'block-designs': true,
		'custom-css': {
			default: applyFilters( 'stackable.button.custom-css.default', '' ),
		},
	},
}

// Change the main class name since we're using `ugb-button` for the button element.
addFilter( 'stackable.button.mainClassName', 'stackable/button', () => {
	return 'ugb-button-wrapper'
} )

// If the alignment was changed, but the design doesn't support it, go back to the basic design to allow the alignment change.
addFilter( 'stackable.button.setAttributes', 'stackable/button/contentAlign', ( attributes, blockProps ) => {
	if ( typeof attributes.contentAlign === 'undefined' ) {
		return attributes
	}

	if ( ! [ '', 'basic' ].includes( blockProps.attributes.design ) ) {
		attributes.design = 'basic'
	}

	return attributes
} )

// If the design was changed, but the design doesn't support alignment, reset the alignment attribute.
addFilter( 'stackable.button.setAttributes', 'stackable/button/design', attributes => {
	if ( typeof attributes.design === 'undefined' ) {
		return attributes
	}

	if ( ! [ '', 'basic' ].includes( attributes.design ) ) {
		attributes.contentAlign = ''
	}

	return attributes
} ) )
registerBlockType( 'ugb/cta', {
	title: __( 'Call to Action', i18n ),
	description: __( 'A small section you can use to call the attention of your visitors. Great for calling attention to your products or deals.', i18n ),
	icon: CTAIcon,
	category: 'stackable',
	keywords: [
		__( 'Call to Action', i18n ),
		__( 'Stackable', i18n ),
		__( 'CTA', i18n ),
	],
	attributes: schema,
	supports: {
		align: [ 'center', 'wide', 'full' ],
		inserter: ! disabledBlocks.includes( name ), // Hide if disabled.
	},

	deprecated,
	edit,
	save,

	// Stackable modules.
	modules: {
		'advanced-general': true,
		'advanced-block-spacing': true,
		'advanced-column-spacing': {
			columnGap: false,
		},
		'advanced-responsive': true,
		'block-background': true,
		'block-separators': true,
		// 'block-title': true,
		'content-align': true,
		'block-designs': true,
		'custom-css': {
			default: applyFilters( 'stackable.cta.custom-css.default', '' ),
		},
	},
} )
registerBlockType( 'ugb/card', {
	title: __( 'Card', i18n ),
	description: __( 'Describe a single subject in a small card. You can use this to describe your product, service or a person.', i18n ),
	icon: CardIcon,
	category: 'stackable',
	keywords: [
		__( 'Card', i18n ),
		__( 'Stackable', i18n ),
	],
	attributes: schema,
	supports: {
		inserter: ! disabledBlocks.includes( name ), // Hide if disabled.
	},

	deprecated,
	edit,
	save,

	// Stackable modules.
	modules: {
		'advanced-general': true,
		'advanced-block-spacing': true,
		'advanced-column-spacing': {
			verticalColumnAlign: true,
		},
		'advanced-responsive': true,
		'block-background': true,
		'block-separators': true,
		'block-title': true,
		'content-align': true,
		'block-designs': true,
		'custom-css': {
			default: applyFilters( 'stackable.card.custom-css.default', '' ),
		},
	},
}

// For column spacings, use advanced paddings & vertical align on the content area only.
addFilter( 'stackable.card.advanced-column-spacing.paddings.selector', 'stackable/card', () => '.ugb-card__content' )
addFilter( 'stackable.card.advanced-column-spacing.vertical-align.selector', 'stackable/card', () => '.ugb-card__content' ) )
registerBlockType( 'ugb/container', {
	title: __( 'Container', i18n ),
	description: __( 'A styled container that you can add other blocks inside. Use this to create unique layouts.', i18n ),
	icon: ContainerIcon,
	category: 'stackable',
	keywords: [
		__( 'Container Layout', i18n ),
		__( 'Row', i18n ),
		__( 'Stackable', i18n ),
	],
	supports: {
		anchor: true,
		align: [ 'center', 'wide', 'full' ],
		inserter: ! disabledBlocks.includes( name ), // Hide if disabled.
		// Add EditorsKit block navigator toolbar.
		editorsKitBlockNavigator: true,
	},
	deprecated,
	edit,
	save,
	attributes: schema,

	// Stackable modules.
	modules: {
		'advanced-general': true,
		'advanced-block-spacing': true,
		'advanced-column-spacing': {
			columnGap: false,
		},
		'advanced-responsive': true,
		'block-background': true,
		'block-separators': true,
		// 'block-title': true,
		'content-align': true,
		'custom-css': {
			default: applyFilters( 'stackable.container.custom-css.default', '' ),
		},
	},

	/**
	 * For grouping & ungrouping blocks into Container blocks.
	 * Based on the Group block.
	 *
	 * @see https://github.com/WordPress/gutenberg/blob/a78fddd06e016ef43eb420b2c82b2cdebbdb0c3c/packages/block-library/src/group/index.js
	 */
	transforms: {
		from: [
			{
				type: 'block',
				isMultiBlock: true,
				blocks: [ '*' ],
				__experimentalConvert( blocks ) {
					// Avoid transforming a single `ugb/container` Block
					if ( blocks.length === 1 && blocks[ 0 ].name === 'ugb/container' ) {
						return
					}

					const alignments = [ 'wide', 'full' ]

					// Determine the widest setting of all the blocks to be grouped
					const widestAlignment = blocks.reduce( ( result, block ) => {
						const { align } = block.attributes
						return alignments.indexOf( align ) > alignments.indexOf( result ) ? align : result
					}, undefined )

					// Clone the Blocks to be Grouped
					// Failing to create new block references causes the original blocks
					// to be replaced in the switchToBlockType call thereby meaning they
					// are removed both from their original location and within the
					// new group block.
					const groupInnerBlocks = blocks.map( block => {
						return createBlock( block.name, block.attributes, block.innerBlocks )
					} )

					return createBlock( 'ugb/container', {
						align: widestAlignment,
					}, groupInnerBlocks )
				},
			},

		],
	},
}

// Remove the default way of how the column spacing -> vertical align works since we are using another method in `style.js`
addFilter( 'stackable.container.advanced-column-spacing.vertical-align', 'stackable/container', () => ( {} ) ) )
registerBlockType( 'ugb/count-up', {
	title: __( 'Count Up', i18n ),
	description: __( 'Showcase your stats. Display how many customers you have or the number of downloads of your app.', i18n ),
	icon: CountUpIcon,
	category: 'stackable',
	keywords: [
		__( 'Statistics', i18n ),
		__( 'Count Up', i18n ),
		__( 'Stackable', i18n ),
	],
	attributes: schema,

	supports: {
		align: [ 'center', 'wide', 'full' ],
		inserter: ! disabledBlocks.includes( name ), // Hide if disabled.
	},

	deprecated,
	edit,
	save,

	// Stackable modules.
	modules: {
		'advanced-general': true,
		'advanced-block-spacing': true,
		'advanced-column-spacing': {
			verticalColumnAlign: true,
		},
		'advanced-responsive': true,
		'block-background': true,
		'block-separators': true,
		'block-title': true,
		'content-align': true,
		'block-designs': true,
		'custom-css': {
			default: applyFilters( 'stackable.count-up.custom-css.default', '' ),
		},
	},
} )
registerBlockType( 'ugb/divider', {
	title: __( 'Divider', i18n ),
	description: __( 'Add a pause between your content.', i18n ),
	icon: DividerIcon,
	category: 'stackable',
	keywords: [
		__( 'Divider', i18n ),
		__( 'Stackable', i18n ),
	],
	attributes: schema,
	supports: {
		align: [ 'center', 'wide', 'full' ],
		inserter: ! disabledBlocks.includes( name ), // Hide if disabled.
	},

	edit,
	save,
	deprecated,

	// Stackable modules.
	modules: {
		'advanced-block-spacing': true,
		'advanced-responsive': true,
		'content-align': true,
		'custom-css': {
			default: applyFilters( 'stackable.divider.custom-css.default', '' ),
		},
	},
}

addFilter( 'stackable.divider.setAttributes', 'stackable/divider/defaults', ( attributes, blockProps ) => {
	const blockAttributes = blockProps.attributes

	if ( typeof attributes.design !== 'undefined' && attributes.design !== 'basic' ) {
		return {
			...attributes,
			height: attributes.design === 'asterisks' ? 14 : 7,
			width: attributes.design === 'dots' || attributes.design === 'asterisks' ? 10 : blockAttributes.width,
		}
	} else if ( attributes.design === 'basic' ) {
		return {
			...attributes,
			height: 1,
			width: 50,
		}
	}

	return attributes
} ) )
registerBlockType( 'ugb/expand', {
	title: __( 'Expand / Show More', i18n ),
	description: __( 'Display a small snippet of text. Your readers can toggle it to show more information.', i18n ),
	icon: ExpandIcon,
	category: 'stackable',
	keywords: [
		__( 'Expand', i18n ),
		__( 'Show more/less', i18n ),
		__( 'Stackable', i18n ),
	],
	attributes: schema,
	supports: {
		inserter: ! disabledBlocks.includes( name ), // Hide if disabled.
	},

	deprecated,
	edit,
	save,

	// Stackable modules.
	modules: {
		'advanced-general': true,
		'advanced-block-spacing': true,
		// 'advanced-column-spacing': {
		// 	columnGap: false,
		// 	height: false,
		// 	verticalContentAlign: false,
		// 	paddingSelector: '.ugb-block-content',
		// },
		'advanced-responsive': true,
		// 'block-background': true,
		// 'block-separators': true,
		// 'block-title': true,
		'content-align': true,
		'custom-css': {
			default: applyFilters( 'stackable.expand.custom-css.default', '' ),
		},
	},
} )
registerBlockType( 'ugb/feature-grid', {
	title: __( 'Feature Grid', i18n ),
	description: __( 'Display multiple product features or services. You can use Feature Grids one after another.', i18n ),
	icon: FeatureGridIcon,
	category: 'stackable',
	keywords: [
		__( 'Feature Grid', i18n ),
		__( 'Stackable', i18n ),
	],
	attributes: schema,
	supports: {
		align: [ 'wide' ],
		inserter: ! disabledBlocks.includes( name ), // Hide if disabled.
	},

	deprecated,
	edit,
	save,

	// Stackable modules.
	modules: {
		'advanced-general': true,
		'advanced-block-spacing': true,
		'advanced-column-spacing': {
			verticalColumnAlign: true,
		},
		'advanced-responsive': true,
		'block-background': true,
		'block-separators': true,
		'block-title': true,
		'content-align': true,
		'block-designs': true,
		'custom-css': {
			default: applyFilters( 'stackable.feature-grid.custom-css.default', '' ),
		},
	},
}

// If the alignment was changed, but the design doesn't support it, go back to the basic design to allow the alignment change.
addFilter( 'stackable.feature-grid.setAttributes', 'stackable/feature-grid/imageShape', attributes => {
	if ( typeof attributes.imageShape !== 'undefined' ) {
		return {
			...attributes,
			image1Shape: '',
			image2Shape: '',
			image3Shape: '',
			image4Shape: '',
			image1ShapeFlipX: '',
			image1ShapeFlipY: '',
			image1ShapeStretch: '',
			image2ShapeFlipX: '',
			image2ShapeFlipY: '',
			image2ShapeStretch: '',
			image3ShapeFlipX: '',
			image3ShapeFlipY: '',
			image3ShapeStretch: '',
			image4ShapeFlipX: '',
			image4ShapeFlipY: '',
			image4ShapeStretch: '',
		}
	}

	if ( typeof attributes.imageShapeFlipX !== 'undefined' ) {
		return {
			...attributes,
			image1ShapeFlipX: '',
			image2ShapeFlipX: '',
			image3ShapeFlipX: '',
			image4ShapeFlipX: '',
		}
	}

	if ( typeof attributes.imageShapeFlipY !== 'undefined' ) {
		return {
			...attributes,
			image1ShapeFlipY: '',
			image2ShapeFlipY: '',
			image3ShapeFlipY: '',
			image4ShapeFlipY: '',
		}
	}

	if ( typeof attributes.imageShapeStretch !== 'undefined' ) {
		return {
			...attributes,
			image1ShapeStretch: '',
			image2ShapeStretch: '',
			image3ShapeStretch: '',
			image4ShapeStretch: '',
		}
	}

	if ( typeof attributes.columnBackgroundColor !== 'undefined' || typeof attributes.columnBackgroundColorType !== 'undefined' ) {
		return {
			...attributes,
			column1BackgroundColor: '',
			column2BackgroundColor: '',
			column3BackgroundColor: '',
			column4BackgroundColor: '',
		}
	}

	return attributes
} ) )
registerBlockType( 'ugb/feature', {
	title: __( 'Feature', i18n ),
	description: __( 'Display a product feature or a service in a large area.', i18n ),
	icon: FeatureIcon,
	category: 'stackable',
	keywords: [
		__( 'Feature', i18n ),
		__( 'Stackable', i18n ),
	],
	supports: {
		align: [ 'center', 'wide', 'full' ],
		inserter: ! disabledBlocks.includes( name ), // Hide if disabled.
	},

	deprecated,
	edit,
	save,

	attributes: schema,

	// Stackable modules.
	modules: {
		'advanced-general': true,
		'advanced-block-spacing': true,
		// 'advanced-column-spacing': {
		// 	columnGap: false,
		// },
		'advanced-responsive': true,
		'block-background': true,
		'block-separators': true,
		// 'block-title': true,
		'content-align': true,
		'block-designs': true,
		'custom-css': {
			default: applyFilters( 'stackable.feature.custom-css.default', '' ),
		},
	},
} )
registerBlockType( 'ugb/header', {
	title: __( 'Header', i18n ),
	description: __( 'A large header title area. Typically used at the very top of a page.', i18n ),
	icon: HeaderIcon,
	category: 'stackable',
	keywords: [
		__( 'Header', i18n ),
		__( 'Stackable', i18n ),
	],
	supports: {
		inserter: ! disabledBlocks.includes( name ), // Hide if disabled.
	},
	attributes: schema,

	deprecated,
	edit,
	save,

	// Stackable modules.
	modules: {
		'advanced-general': true,
		'advanced-block-spacing': true,
		'advanced-column-spacing': { columnGap: false },
		'advanced-responsive': true,
		'block-background': true,
		'block-separators': true,
		// 'block-title': true,
		'content-align': true,
		'block-designs': true,
		'custom-css': {
			default: applyFilters( 'stackable.header.custom-css.default', '' ),
		},
	},
} )
registerBlockType( 'ugb/icon-list', {
	title: __( 'Icon List', i18n ),
	description: __( 'An unordered list with icons. You can use this as a list of features or benefits.', i18n ),
	icon: IconListIcon,
	category: 'stackable',
	keywords: [
		__( 'Icon List', i18n ),
		__( 'Stackable', i18n ),
	],
	attributes: schema,
	supports: {
		inserter: ! disabledBlocks.includes( name ), // Hide if disabled.
	},

	deprecated,
	edit,
	save,

	// Stackable modules.
	modules: {
		'advanced-general': true,
		'advanced-block-spacing': true,
		// 'advanced-column-spacing': true,
		'advanced-responsive': true,
		'block-background': true,
		'block-separators': true,
		'block-title': true,
		'content-align': true,
		'block-designs': true,
		'custom-css': {
			default: applyFilters( 'stackable.icon-list.custom-css.default', '' ),
		},
	},
} )
registerBlockType( 'ugb/image-box', {
	title: __( 'Image Box', i18n ),
	description: __( 'Display an image that shows more information when hovered on. Can be used as a fancy link to other pages.', i18n ),
	icon: ImageBoxIcon,
	category: 'stackable',
	keywords: [
		__( 'Image Box', i18n ),
		__( 'Stackable', i18n ),
	],
	supports: {
		align: [ 'center', 'wide', 'full' ],
		inserter: ! disabledBlocks.includes( name ), // Hide if disabled.
	},
	attributes: schema,

	deprecated,
	edit,
	save,

	// Stackable modules.
	modules: {
		'advanced-general': true,
		'advanced-block-spacing': true,
		'advanced-column-spacing': true,
		'advanced-responsive': true,
		'block-background': true,
		'block-separators': true,
		'block-title': true,
		'content-align': true,
		'block-designs': true,
		'custom-css': {
			default: applyFilters( 'stackable.image-box.custom-css.default', '' ),
		},
	},
}

// The "height" option is really the "columnHeight" option. @see edit.js
// Disable the default column height.
addFilter( 'stackable.image-box.advanced-column-spacing.styles', 'stackable/image-box/column-height', styles => {
	styles[ '> .ugb-inner-block > .ugb-block-content > *' ] = {
		minHeight: undefined,
	}
	styles.tablet[ '> .ugb-inner-block > .ugb-block-content > *' ] = {
		minHeight: undefined,
	}
	styles.mobile[ '> .ugb-inner-block > .ugb-block-content > *' ] = {
		minHeight: undefined,
	}

	return styles
} ) )
registerBlockType( 'ugb/notification', {
	title: __( 'Notification', i18n ),
	description: __( 'Show a notice to your readers. People can dismiss the notice to permanently hide it.', i18n ),
	icon: NotificationIcon,
	category: 'stackable',
	keywords: [
		__( 'Notification', i18n ),
		__( 'Stackable', i18n ),
	],
	attributes: schema,
	supports: {
		inserter: ! disabledBlocks.includes( name ), // Hide if disabled.
	},

	deprecated,
	edit,
	save,

	// Stackable modules.
	modules: {
		'advanced-general': true,
		'advanced-block-spacing': true,
		'advanced-column-spacing': { columnGap: false },
		'advanced-responsive': true,
		'block-background': true,
		// 'block-separators': true,
		// 'block-title': true,
		'content-align': true,
		'block-designs': true,
		'custom-css': {
			default: applyFilters( 'stackable.notification.custom-css.default', '' ),
		},
	},
}

addFilter( 'stackable.notification.setAttributes', 'stackable/notification/notifType', attributes => {
	if ( typeof attributes.notifType === 'undefined' ) {
		return attributes
	}

	return {
		...attributes,
		columnBackgroundColor: '',
		columnBackgroundColorOpacity: '',
		iconColor: '',
		titleColor: '',
		descriptionColor: '',
		buttonBackgroundColor: '',
		columnBorderColor: '',
	}
} )

// When background opacity is set or when the background color is reset, revert background color to notification color.
addFilter( 'stackable.notification.setAttributes', 'stackable/notification/opacity', ( attributes, blockProps ) => {
	const setColumnBackgroundColor = attributes.hasOwnProperty( 'columnBackgroundColor' )
	if ( typeof attributes.columnBackgroundColorOpacity === 'undefined' && ! setColumnBackgroundColor ) {
		return attributes
	}

	// If a new background color is set, do not revert to notification color.
	if ( setColumnBackgroundColor && typeof attributes.columnBackgroundColor !== 'undefined' ) {
		return attributes
	}

	const {
		notifType = 'success',
		columnBackgroundColor = '',
	} = blockProps.attributes

	const NOTIFY_BACKGROUND_COLORS = {
		success: '#40ba7b',
		error: '#d9534f',
		info: '#2091e1',
		warning: '#ffdd57',
	}

	return {
		...attributes,
		columnBackgroundColor: columnBackgroundColor && ! setColumnBackgroundColor ? columnBackgroundColor : NOTIFY_BACKGROUND_COLORS[ notifType ],
	}
} ) )
registerBlockType( 'ugb/number-box', {
	title: __( 'Number Box', i18n ),
	description: __( 'Display steps or methods that your users will do in your service. For example, "Get started in just 3 easy steps: 1, 2 and 3!"', i18n ),
	icon: NumberBoxIcon,
	category: 'stackable',
	keywords: [
		__( 'Number Box', i18n ),
		__( 'Stackable', i18n ),
	],
	attributes: schema,
	supports: {
		align: [ 'center', 'wide', 'full' ],
		inserter: ! disabledBlocks.includes( name ), // Hide if disabled.
	},
	deprecated,
	save,
	edit,

	// Stackable modules.
	modules: {
		'advanced-general': true,
		'advanced-block-spacing': true,
		'advanced-column-spacing': {
			verticalColumnAlign: true,
		},
		'advanced-responsive': true,
		'block-background': true,
		'block-separators': true,
		'block-title': true,
		'content-align': true,
		'block-designs': true,
		'custom-css': {
			default: applyFilters( 'stackable.number-box.custom-css.default', '' ),
		},
	},
} )
registerBlockType( 'ugb/pricing-box', {
	title: __( 'Pricing Box', i18n ),
	description: __( 'Display the different pricing tiers of your business.', i18n ),
	icon: PricingBoxIcon,
	category: 'stackable',
	keywords: [
		__( 'Pricing Box', i18n ),
		__( 'Stackable', i18n ),
	],
	attributes: schema,
	supports: {
		align: [ 'wide' ],
		inserter: ! disabledBlocks.includes( name ), // Hide if disabled.
	},

	deprecated,
	edit,
	save,

	// Stackable modules.
	modules: {
		'advanced-general': true,
		'advanced-block-spacing': true,
		'advanced-column-spacing': {
			verticalColumnAlign: true,
		},
		'advanced-responsive': true,
		'block-background': true,
		'block-separators': true,
		'block-title': true,
		'content-align': true,
		'block-designs': true,
		'custom-css': {
			default: applyFilters( 'stackable.pricing-box.custom-css.default', '' ),
		},
	},
}

// Reset some attributes if some global attributes are set.
addFilter( 'stackable.pricing-box.setAttributes', 'stackable/pricing-box/imageShape', attributes => {
	if ( typeof attributes.imageShape !== 'undefined' ) {
		return {
			...attributes,
			image1Shape: '',
			image2Shape: '',
			image3Shape: '',
			image1ShapeFlipX: '',
			image1ShapeFlipY: '',
			image1ShapeStretch: '',
			image2ShapeFlipX: '',
			image2ShapeFlipY: '',
			image2ShapeStretch: '',
			image3ShapeFlipX: '',
			image3ShapeFlipY: '',
			image3ShapeStretch: '',
		}
	}

	if ( typeof attributes.imageShapeFlipX !== 'undefined' ) {
		return {
			...attributes,
			image1ShapeFlipX: '',
			image2ShapeFlipX: '',
			image3ShapeFlipX: '',
		}
	}

	if ( typeof attributes.imageShapeFlipY !== 'undefined' ) {
		return {
			...attributes,
			image1ShapeFlipY: '',
			image2ShapeFlipY: '',
			image3ShapeFlipY: '',
		}
	}

	if ( typeof attributes.imageShapeStretch !== 'undefined' ) {
		return {
			...attributes,
			image1ShapeStretch: '',
			image2ShapeStretch: '',
			image3ShapeStretch: '',
		}
	}

	if ( typeof attributes.columnBackgroundColor !== 'undefined' || typeof attributes.columnBackgroundColorType !== 'undefined' ) {
		return {
			...attributes,
			column1BackgroundColor: '',
			column2BackgroundColor: '',
			column3BackgroundColor: '',
		}
	}

	return attributes
} ) )
registerBlockType( 'ugb/separator', {
	title: __( 'Separator', i18n ),
	description: __( 'A fancy separator to be placed between containers and content.', i18n ),
	icon: SeparatorIcon,
	category: 'stackable',
	keywords: [
		__( 'Separator', i18n ),
		__( 'SVG Divider', i18n ),
		__( 'Stackable', i18n ),
	],
	attributes: schema,
	supports: {
		align: [ 'full' ],
		inserter: ! disabledBlocks.includes( name ), // Hide if disabled.
	},
	deprecated,
	save,
	edit,

	// Stackable modules.
	modules: {
		'advanced-general': true,
		'advanced-responsive': true,
		'advanced-block-spacing': {
			enableMarginRight: false,
			enableMarginLeft: false,
			enablePaddingRight: false,
			enablePaddingLeft: false,
			height: false,
			width: false,
			horizontalContentAlign: false,
			verticalContentAlign: false,
			modifyStyles: false,
		},
		'custom-css': {
			default: applyFilters( 'stackable.separator.custom-css.default', '' ),
		},
	},
} )
registerBlockType( 'ugb/spacer', {
	title: __( 'Spacer', i18n ),
	description: __( 'Sometimes you just need some space.', i18n ),
	icon: SpacerIcon,
	category: 'stackable',
	keywords: [
		__( 'Spacer', i18n ),
		__( 'Stackable', i18n ),
	],
	attributes: schema,
	supports: {
		align: [ 'center', 'full' ],
		inserter: ! disabledBlocks.includes( name ), // Hide if disabled.
	},

	deprecated,
	edit,
	save,

	// Stackable modules.
	modules: {
		'block-separators': {
			enableBringToFront: false,
		},
		'advanced-responsive': true,
		'custom-css': {
			default: applyFilters( 'stackable.spacer.custom-css.default', '' ),
		},
	},
}

// Change the spacer height if a separator is turned on and the height is small.
addFilter( 'stackable.spacer.setAttributes', 'stackable/spacer/separator-padding', ( attributes, blockProps ) => {
	const {
		showTopSeparator = false,
		showBottomSeparator = false,
		height = '',
	} = blockProps.attributes
	const numSeparatorsBefore = ( showTopSeparator ? 1 : 0 ) + ( showBottomSeparator ? 1 : 0 )

	let turnedOnSeparator = false
	if ( typeof attributes.showTopSeparator !== 'undefined' ) {
		if ( attributes.showTopSeparator ) {
			turnedOnSeparator = true
		}
	}
	if ( typeof attributes.showBottomSeparator !== 'undefined' ) {
		if ( attributes.showBottomSeparator ) {
			turnedOnSeparator = true
		}
	}
	if ( turnedOnSeparator ) {
		const currentHeight = ! height ? 0 : height
		if ( numSeparatorsBefore === 0 && currentHeight < 200 ) {
			attributes.height = 200
			attributes.heightUnit = 'px'
		} else if ( numSeparatorsBefore === 1 && currentHeight < 400 ) {
			attributes.height = 400
			attributes.heightUnit = 'px'
		}
	}
	return attributes
} ) )
registerBlockType( 'ugb/team-member', {
	title: __( 'Team Member', i18n ),
	description: __( 'Display members of your team or your office. Use multiple Team Member blocks if you have a large team.', i18n ),
	icon: TeamMemberIcon,
	category: 'stackable',
	keywords: [
		__( 'Team Member', i18n ),
		__( 'Stackable', i18n ),
	],
	attributes: schema,
	supports: {
		align: [ 'wide' ],
		inserter: ! disabledBlocks.includes( name ), // Hide if disabled.
	},

	deprecated,
	edit,
	save,

	// Stackable modules.
	modules: {
		'advanced-general': true,
		'advanced-block-spacing': true,
		'advanced-column-spacing': true,
		'advanced-responsive': true,
		'block-background': true,
		'block-separators': true,
		'block-title': true,
		'content-align': true,
		'block-designs': true,
		'custom-css': {
			default: applyFilters( 'stackable.team-member.custom-css.default', '' ),
		},
	},
}

// Reset some attributes if some global attributes are set.
addFilter( 'stackable.team-member.setAttributes', 'stackable/team-member/imageShape', attributes => {
	if ( typeof attributes.imageShape !== 'undefined' ) {
		return {
			...attributes,
			image1Shape: '',
			image2Shape: '',
			image3Shape: '',
			image1ShapeFlipX: '',
			image1ShapeFlipY: '',
			image1ShapeStretch: '',
			image2ShapeFlipX: '',
			image2ShapeFlipY: '',
			image2ShapeStretch: '',
			image3ShapeFlipX: '',
			image3ShapeFlipY: '',
			image3ShapeStretch: '',
		}
	}

	if ( typeof attributes.imageShapeFlipX !== 'undefined' ) {
		return {
			...attributes,
			image1ShapeFlipX: '',
			image2ShapeFlipX: '',
			image3ShapeFlipX: '',
		}
	}

	if ( typeof attributes.imageShapeFlipY !== 'undefined' ) {
		return {
			...attributes,
			image1ShapeFlipY: '',
			image2ShapeFlipY: '',
			image3ShapeFlipY: '',
		}
	}

	if ( typeof attributes.imageShapeStretch !== 'undefined' ) {
		return {
			...attributes,
			image1ShapeStretch: '',
			image2ShapeStretch: '',
			image3ShapeStretch: '',
		}
	}

	if ( typeof attributes.columnBackgroundColor !== 'undefined' || typeof attributes.columnBackgroundColorType !== 'undefined' ) {
		return {
			...attributes,
			column1BackgroundColor: '',
			column2BackgroundColor: '',
			column3BackgroundColor: '',
		}
	}

	return attributes
} ) )
registerBlockType( 'ugb/testimonial', {
	title: __( 'Testimonial', i18n ),
	description: __( 'Showcase what your users say about your product or service.', i18n ),
	icon: TestimonialIcon,
	category: 'stackable',
	keywords: [
		__( 'Testimonial', i18n ),
		__( 'Stackable', i18n ),
	],
	attributes: schema,
	supports: {
		align: [ 'wide' ],
		inserter: ! disabledBlocks.includes( name ), // Hide if disabled.
	},

	deprecated,
	edit,
	save,

	// Stackable modules.
	modules: {
		'advanced-general': true,
		'advanced-block-spacing': true,
		'advanced-column-spacing': {
			verticalColumnAlign: true,
		},
		'advanced-responsive': true,
		'block-background': true,
		'block-separators': true,
		'block-title': true,
		'content-align': true,
		'block-designs': true,
		'custom-css': {
			default: applyFilters( 'stackable.testimonial.custom-css.default', '' ),
		},
	},
}

// Reset some attributes if some global attributes are set.
addFilter( 'stackable.testimonial.setAttributes', 'stackable/testimonial/imageShape', attributes => {
	if ( typeof attributes.imageShape !== 'undefined' ) {
		return {
			...attributes,
			image1Shape: '',
			image2Shape: '',
			image3Shape: '',
			image1ShapeFlipX: '',
			image1ShapeFlipY: '',
			image1ShapeStretch: '',
			image2ShapeFlipX: '',
			image2ShapeFlipY: '',
			image2ShapeStretch: '',
			image3ShapeFlipX: '',
			image3ShapeFlipY: '',
			image3ShapeStretch: '',
		}
	}

	if ( typeof attributes.imageShapeFlipX !== 'undefined' ) {
		return {
			...attributes,
			image1ShapeFlipX: '',
			image2ShapeFlipX: '',
			image3ShapeFlipX: '',
		}
	}

	if ( typeof attributes.imageShapeFlipY !== 'undefined' ) {
		return {
			...attributes,
			image1ShapeFlipY: '',
			image2ShapeFlipY: '',
			image3ShapeFlipY: '',
		}
	}

	if ( typeof attributes.imageShapeStretch !== 'undefined' ) {
		return {
			...attributes,
			image1ShapeStretch: '',
			image2ShapeStretch: '',
			image3ShapeStretch: '',
		}
	}

	if ( typeof attributes.columnBackgroundColor !== 'undefined' || typeof attributes.columnBackgroundColorType !== 'undefined' ) {
		return {
			...attributes,
			column1BackgroundColor: '',
			column2BackgroundColor: '',
			column3BackgroundColor: '',
		}
	}

	return attributes
} ) )
registerBlockType( 'ugb/video-popup', {
	title: __( 'Video Popup', i18n ),
	description: __( 'Display a large thumbnail that your users can click to play a video full-screen. Great for introductory or tutorial videos.', i18n ),
	icon: VideoPopupIcon,
	category: 'stackable',
	keywords: [
		__( 'Video Popup', i18n ),
		__( 'Stackable', i18n ),
		__( 'YouTube Vimeo mp4', i18n ),
	],
	attributes: schema,
	supports: {
		align: [ 'center', 'wide', 'full' ],
		inserter: ! disabledBlocks.includes( name ), // Hide if disabled.
	},

	deprecated,
	edit,
	save,

	// Stackable modules.
	modules: {
		'advanced-general': true,
		'advanced-block-spacing': true,
		// 'advanced-column-spacing': true,
		'advanced-responsive': true,
		'block-background': true,
		'block-separators': true,
		'block-title': true,
		// 'content-align': true,
		'block-designs': true,
		'custom-css': {
			default: applyFilters( 'stackable.video-popup.custom-css.default', '' ),
		},
	},
} )