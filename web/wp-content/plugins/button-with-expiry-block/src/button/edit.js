/* eslint-disable */
/**
 * External dependencies
 */
import classnames from 'classnames';

/**
 * WordPress dependencies
 */
import { __ } from '@wordpress/i18n';
import { useCallback, useState, Fragment } from '@wordpress/element';
import { compose as wpCompose } from 'wp';
import {
	PanelBody,
	RangeControl,
	TextControl,
	SelectControl,
	ToggleControl,
	withFallbackStyles,
	ToolbarButton,
	ToolbarGroup,
	Popover,
	DateTimePicker
} from '@wordpress/components';
import {
	BlockControls,
	__experimentalUseGradient,
	ContrastChecker,
	InspectorControls,
	__experimentalPanelColorGradientSettings as PanelColorGradientSettings,
	RichText,
	withColors,
	__experimentalLinkControl as LinkControl,
} from '@wordpress/block-editor';

const { getComputedStyle } = window;

const applyFallbackStyles = withFallbackStyles( ( node, ownProps ) => {
	const { textColor, backgroundColor } = ownProps;
	const backgroundColorValue = backgroundColor && backgroundColor.color;
	const textColorValue = textColor && textColor.color;
	//avoid the use of querySelector if textColor color is known and verify if node is available.
	const textNode =
		! textColorValue && node
			? node.querySelector( '[contenteditable="true"]' )
			: null;
	return {
		fallbackBackgroundColor:
			backgroundColorValue || ! node
				? undefined
				: getComputedStyle( node ).backgroundColor,
		fallbackTextColor:
			textColorValue || ! textNode
				? undefined
				: getComputedStyle( textNode ).color,
	};
} );

const NEW_TAB_REL = 'noopener';
const MIN_BORDER_RADIUS_VALUE = 0;
const MAX_BORDER_RADIUS_VALUE = 50;
const INITIAL_BORDER_RADIUS_POSITION = 5;

function BorderPanel( { borderRadius = '', setAttributes } ) {
	const setBorderRadius = useCallback(
		( newBorderRadius ) => {
			setAttributes( { borderRadius: newBorderRadius } );
		},
		[ setAttributes ]
	);
	return (
		<PanelBody title={ __( 'Border Settings' ) }>
			<RangeControl
				value={ borderRadius }
				label={ __( 'Border radius' ) }
				min={ MIN_BORDER_RADIUS_VALUE }
				max={ MAX_BORDER_RADIUS_VALUE }
				initialPosition={ INITIAL_BORDER_RADIUS_POSITION }
				allowReset
				onChange={ setBorderRadius }
			/>
		</PanelBody>
	);
}

function URLPicker( {
	isSelected,
	url,
	setAttributes,
	opensInNewTab,
	onToggleOpenInNewTab,
} ) {
	const [ isURLPickerOpen, setIsURLPickerOpen ] = useState( false );
	const openLinkControl = () => {
		setIsURLPickerOpen( true );
	};
	const linkControl = isURLPickerOpen && (
		<Popover
			position="bottom center"
			onClose={ () => setIsURLPickerOpen( false ) }
		>
			<LinkControl
				className="wp-block-navigation-link__inline-link-input"
				value={ { url, opensInNewTab } }
				onChange={ ( {
					url: newURL = '',
					opensInNewTab: newOpensInNewTab,
				} ) => {
					setAttributes( { url: newURL } );

					if ( opensInNewTab !== newOpensInNewTab ) {
						onToggleOpenInNewTab( newOpensInNewTab );
					}
				} }
			/>
		</Popover>
	);
	return (
		<Fragment>
			<BlockControls>
				<ToolbarGroup>
					<ToolbarButton
						name="link"
						icon="admin-links"
						title={ __( 'Link' ) }
						onClick={ openLinkControl }
					/>
				</ToolbarGroup>
			</BlockControls>
			{ linkControl }
		</Fragment>
	);
}

function ButtonEdit( {
	attributes,
	backgroundColor,
	textColor,
	setBackgroundColor,
	setTextColor,
	fallbackBackgroundColor,
	fallbackTextColor,
	setAttributes,
	className,
	isSelected,
} ) {
	const {
		borderRadius,
		linkTarget,
		placeholder,
		rel,
		text,
		url,
		willExpire,
		expireAt,
		expireText,
		size,
	} = attributes;
	const onSetLinkRel = useCallback(
		( value ) => {
			setAttributes( { rel: value } );
		},
		[ setAttributes ]
	);

	const onToggleOpenInNewTab = useCallback(
		( value ) => {
			const newLinkTarget = value ? '_blank' : undefined;

			let updatedRel = rel;
			if ( newLinkTarget && ! rel ) {
				updatedRel = NEW_TAB_REL;
			} else if ( ! newLinkTarget && rel === NEW_TAB_REL ) {
				updatedRel = undefined;
			}

			setAttributes( {
				linkTarget: newLinkTarget,
				rel: updatedRel,
			} );
		},
		[ rel, setAttributes ]
	);
	const {
		gradientClass,
		gradientValue,
		setGradient,
	} = __experimentalUseGradient();

	const buttonSize = [
		size || 'button-large',
	];

	return (
		<div className={ className }>
			<RichText
				placeholder={ placeholder || __( 'Add text…' ) }
				value={ text }
				onChange={ ( value ) => setAttributes( { text: value } ) }
				withoutInteractiveFormatting
				className={ classnames( 'wp-block-button__link', {
					'has-background': backgroundColor.color,
					[ backgroundColor.class ]:
						! gradientValue && backgroundColor.class,
					'has-text-color': textColor.color,
					[ textColor.class ]: textColor.class,
					[ gradientClass ]: gradientClass,
					'no-border-radius': borderRadius === 0,
					[ buttonSize ]: true,
				} ) }
				style={ {
					...( ! backgroundColor.color && gradientValue
						? { background: gradientValue }
						: { backgroundColor: backgroundColor.color } ),
					color: textColor.color,
					borderRadius: borderRadius
						? borderRadius + 'px'
						: undefined,
				} }
			/>
			<URLPicker
				url={ url }
				setAttributes={ setAttributes }
				isSelected={ isSelected }
				opensInNewTab={ linkTarget === '_blank' }
				onToggleOpenInNewTab={ onToggleOpenInNewTab }
			/>
			<InspectorControls>
				<PanelColorGradientSettings
					title={ __( 'Background & Text Color' ) }
					settings={ [
						{
							colorValue: textColor.color,
							onColorChange: setTextColor,
							label: __( 'Text color' ),
						},
						{
							colorValue: backgroundColor.color,
							onColorChange: setBackgroundColor,
							gradientValue,
							onGradientChange: setGradient,
							label: __( 'Background' ),
						},
					] }
				>
					<ContrastChecker
						{ ...{
							// Text is considered large if font size is greater or equal to 18pt or 24px,
							// currently that's not the case for button.
							isLargeText: false,
							textColor: textColor.color,
							backgroundColor: backgroundColor.color,
							fallbackBackgroundColor,
							fallbackTextColor,
						} }
					/>
				</PanelColorGradientSettings>
				<BorderPanel
					borderRadius={ borderRadius }
					setAttributes={ setAttributes }
				/>
			<PanelBody title={ __( 'Button Size' ) }>
				<SelectControl
					label="Size"
					value={ size || 'button-large' }
					options={ [
						{ label: 'Small', value: 'button-small' },
						{ label: 'Medium', value: 'button-medium' },
						{ label: 'Large', value: 'button-large' },
						{ label: 'X-Large', value: 'button-xlarge' },
					] }
					onChange={ ( value ) => setAttributes( { size: value } ) }
				/>
			</PanelBody>

				<PanelBody title={ __( 'Expiry' ) }>
					<ToggleControl
						label={ __( 'Button will expire' ) }
						onChange={ ( value ) => setAttributes( { willExpire: value } ) }
						checked={ willExpire === true }
					/>
					<DateTimePicker
						currentDate={ expireAt * 1000 }
						onChange={ value => {
							setAttributes( {
								expireAt: Math.floor( Date.parse( value ) / 1000 ),
							} );
						} }
					/>
					<TextControl
						label={ __( 'Expiry text (optional)' ) }
						value={ expireText }
						onChange={ text => {
							setAttributes( {
								expireText: text,
							} );
						} }
					/>
				</PanelBody>
				<PanelBody title={ __( 'Link settings' ) } initialOpen={ false }>
					<ToggleControl
						label={ __( 'Open in new tab' ) }
						onChange={ onToggleOpenInNewTab }
						checked={ linkTarget === '_blank' }
					/>
					<TextControl
						label={ __( 'Link rel attribute (not the link url)' ) }
						value={ rel || '' }
						onChange={ onSetLinkRel }
					/>
				</PanelBody>
			</InspectorControls>
		</div>
	);
}

export default wpCompose.compose( [
	withColors( 'backgroundColor', { textColor: 'color' } ),
	applyFallbackStyles,
] )( ButtonEdit );
