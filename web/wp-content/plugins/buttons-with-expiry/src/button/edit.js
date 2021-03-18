/**
 * External dependencies
 */
import classnames from 'classnames';

/**
 * WordPress dependencies
 */
import { __ } from '@wordpress/i18n';
import { useCallback, useState, useRef } from '@wordpress/element';
import { compose } from '@wordpress/compose';
import {
	KeyboardShortcuts,
	PanelBody,
	RangeControl,
	TextControl,
	ToolbarButton,
	ToolbarGroup,
	Popover,
	SelectControl,
	ToggleControl,
	DateTimePicker,
	withFallbackStyles,
} from '@wordpress/components';
import {
	BlockControls,
	InspectorControls,
	InspectorAdvancedControls,
	RichText,
	useBlockProps,
	__experimentalLinkControl as LinkControl,
	__experimentalPanelColorGradientSettings as PanelColorGradientSettings,
	__experimentalUseGradient,
	withColors,
} from '@wordpress/block-editor';
import { rawShortcut, displayShortcut } from '@wordpress/keycodes';
import { link, linkOff } from '@wordpress/icons';
import { createBlock } from '@wordpress/blocks';

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
	const initialBorderRadius = borderRadius;
	const setBorderRadius = useCallback(
		( newBorderRadius ) => {
			if ( newBorderRadius === undefined )
				setAttributes( {
					borderRadius: initialBorderRadius,
				} );
			else setAttributes( { borderRadius: newBorderRadius } );
		},
		[ setAttributes ]
	);
	return (
		<PanelBody title={ __( 'Border settings' ) } initialOpen={ false }>
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
	anchorRef,
} ) {
	const [ isURLPickerOpen, setIsURLPickerOpen ] = useState( false );
	const urlIsSet = !! url;
	const urlIsSetandSelected = urlIsSet && isSelected;
	const openLinkControl = () => {
		setIsURLPickerOpen( true );
		return false; // prevents default behaviour for event
	};
	const unlinkButton = () => {
		setAttributes( {
			url: undefined,
			linkTarget: undefined,
			rel: undefined,
		} );
		setIsURLPickerOpen( false );
	};
	const linkControl = ( isURLPickerOpen || urlIsSetandSelected ) && (
		<Popover
			position="bottom center"
			onClose={ () => setIsURLPickerOpen( false ) }
			anchorRef={ anchorRef?.current }
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
		<>
			<BlockControls>
				<ToolbarGroup>
					{ ! urlIsSet && (
						<ToolbarButton
							name="link"
							icon={ link }
							title={ __( 'Link' ) }
							shortcut={ displayShortcut.primary( 'k' ) }
							onClick={ openLinkControl }
						/>
					) }
					{ urlIsSetandSelected && (
						<ToolbarButton
							name="link"
							icon={ linkOff }
							title={ __( 'Unlink' ) }
							shortcut={ displayShortcut.primaryShift( 'k' ) }
							onClick={ unlinkButton }
							isActive={ true }
						/>
					) }
				</ToolbarGroup>
			</BlockControls>
			{ isSelected && (
				<KeyboardShortcuts
					bindGlobal
					shortcuts={ {
						[ rawShortcut.primary( 'k' ) ]: openLinkControl,
						[ rawShortcut.primaryShift( 'k' ) ]: unlinkButton,
					} }
				/>
			) }
			{ linkControl }
		</>
	);
}

function ButtonEdit( props ) {
	const {
		attributes,
		setAttributes,
		isSelected,
		onReplace,
		mergeBlocks,
		backgroundColor,
		textColor,
		setBackgroundColor,
		setTextColor,
	} = props;

	const {
		url,
		text,
		linkTarget,
		rel,
		placeholder,
		borderRadius,
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

	const setButtonText = ( newText ) => {
		// Remove anchor tags from button text content.
		setAttributes( { text: newText.replace( /<\/?a[^>]*>/g, '' ) } );
	};

	const buttonSize = [ size || 'button-large' ];

	const ref = useRef();
	const blockProps = useBlockProps( { ref } );

	return (
		<>
			<div
				{ ...blockProps }
				className={ classnames(
					'wp-block-lf-button-with-expiry',
					blockProps.className
				) }
			>
				<RichText
					placeholder={ placeholder || __( 'Add text…' ) }
					value={ text }
					onChange={ ( value ) => setButtonText( value ) }
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
					onSplit={ ( value ) =>
						createBlock( 'lf/button-with-expiry', {
							...attributes,
							text: value,
						} )
					}
					onReplace={ onReplace }
					onMerge={ mergeBlocks }
					identifier="text"
				/>
			</div>
			<URLPicker
				url={ url }
				setAttributes={ setAttributes }
				isSelected={ isSelected }
				opensInNewTab={ linkTarget === '_blank' }
				onToggleOpenInNewTab={ onToggleOpenInNewTab }
				anchorRef={ ref }
			/>
			<InspectorControls>
				<PanelColorGradientSettings
					initialOpen={ true }
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
				></PanelColorGradientSettings>
				<PanelBody title={ __( 'Button Size' ) } initialOpen={ false }>
					<SelectControl
						label="Size"
						value={ size || 'button-large' }
						options={ [
							{ label: 'Small', value: 'button-small' },
							{ label: 'Medium', value: 'button-medium' },
							{ label: 'Large', value: 'button-large' },
							{ label: 'X-Large', value: 'button-xlarge' },
						] }
						onChange={ ( value ) =>
							setAttributes( { size: value } )
						}
					/>
				</PanelBody>
				<PanelBody title={ __( 'Expiry' ) } initialOpen={ true }>
					<ToggleControl
						label={ __( 'Button will expire' ) }
						onChange={ ( value ) =>
							setAttributes( { willExpire: value } )
						}
						checked={ willExpire === true }
					/>
					<DateTimePicker
						currentDate={ expireAt * 1000 }
						onChange={ ( value ) => {
							setAttributes( {
								expireAt: Math.floor(
									Date.parse( value ) / 1000
								),
							} );
						} }
					/>
					<TextControl
						label={ __( 'Expiry text (optional)' ) }
						value={ expireText }
						onChange={ ( updatedText ) => {
							setAttributes( {
								expireText: updatedText,
							} );
						} }
					/>
				</PanelBody>
				<BorderPanel
					borderRadius={ borderRadius }
					setAttributes={ setAttributes }
				/>
			</InspectorControls>
			<InspectorAdvancedControls>
				<TextControl
					label={ __( 'Link rel' ) }
					value={ rel || '' }
					onChange={ onSetLinkRel }
				/>
			</InspectorAdvancedControls>
		</>
	);
}

export default compose( [
	withColors( 'backgroundColor', { textColor: 'color' } ),
	applyFallbackStyles,
] )( ButtonEdit );
