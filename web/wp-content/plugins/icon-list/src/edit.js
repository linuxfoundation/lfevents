import { __, _x } from '@wordpress/i18n';

import {
	RichText,
	BlockControls,
	RichTextShortcut,
	InspectorControls,
} from '@wordpress/block-editor';
import { createBlock } from '@wordpress/blocks';
import {
	PanelBody,
	SelectControl,
	RangeControl,
	ToolbarGroup,
} from '@wordpress/components';
import {
	__unstableCanIndentListItems as canIndentListItems,
	__unstableCanOutdentListItems as canOutdentListItems,
	__unstableIndentListItems as indentListItems,
	__unstableOutdentListItems as outdentListItems,
} from '@wordpress/rich-text';
import { formatIndent, formatOutdent } from '@wordpress/icons';

import './editor.scss';

export default function Edit( {
	attributes,
	setAttributes,
	mergeBlocks,
	onReplace,
	className,
	isSelected,
} ) {
	const {
		values,
		type,
		selectedIcon,
		iconSize,
		columnCount,
		listGap,
	} = attributes;

	const style = {
		'--list-gap': listGap ? `${ listGap }px` : '10',
		'--icon-size': iconSize ? `${ iconSize }px` : '20px',
		'--column-count': columnCount ? `${ columnCount }` : '1',
	};

	const controls = ( { value, onChange, onFocus } ) => (
		<>
			{ isSelected && (
				<>
					<RichTextShortcut
						type="primary"
						character="["
						onUse={ () => {
							onChange( outdentListItems( value ) );
						} }
					/>
					<RichTextShortcut
						type="primary"
						character="]"
						onUse={ () => {
							onChange(
								indentListItems( value, { type: 'ul' } )
							);
						} }
					/>
					<RichTextShortcut
						type="primary"
						character="m"
						onUse={ () => {
							onChange(
								indentListItems( value, { type: 'ul' } )
							);
						} }
					/>
					<RichTextShortcut
						type="primaryShift"
						character="m"
						onUse={ () => {
							onChange( outdentListItems( value ) );
						} }
					/>
				</>
			) }
			<BlockControls>
				<ToolbarGroup
					controls={ [
						{
							icon: formatOutdent,
							title: __( 'Outdent list item' ),
							shortcut: _x( 'Backspace', 'keyboard key' ),
							isDisabled: ! canOutdentListItems( value ),
							onClick() {
								onChange( outdentListItems( value ) );
								onFocus();
							},
						},
						{
							icon: formatIndent,
							title: __( 'Indent list item' ),
							shortcut: _x( 'Space', 'keyboard key' ),
							isDisabled: ! canIndentListItems( value ),
							onClick() {
								onChange(
									indentListItems( value, { type: 'ul' } )
								);
								onFocus();
							},
						},
					] }
				/>
			</BlockControls>
		</>
	);

	return (
		<>
			<InspectorControls key="icon-list-block-panel">
				<PanelBody title={ __( 'Settings' ) } initialOpen={ true }>
					<RangeControl
						label={ __( 'No. of columns' ) }
						min={ 1 }
						max={ 4 }
						value={ columnCount }
						onChange={ ( value ) =>
							setAttributes( { columnCount: value } )
						}
					/>
					<RangeControl
						label={ __( 'List item gap' ) }
						min={ 0 }
						max={ 40 }
						value={ listGap }
						onChange={ ( value ) =>
							setAttributes( { listGap: value } )
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
								value: 'is-style-list-angle-right',
							},
							{
								label: __( 'Check' ),
								value: 'is-style-list-check',
							},
							{
								label: __( 'Plus' ),
								value: 'is-style-list-plus',
							},
						] }
						onChange={ ( value ) =>
							setAttributes( {
								selectedIcon: '' !== value ? value : '',
							} )
						}
					/>
					{ selectedIcon && (
						<RangeControl
							label={ __( 'Icon size' ) }
							min={ 8 }
							max={ 40 }
							value={ iconSize }
							onChange={ ( value ) =>
								setAttributes( { iconSize: value } )
							}
						/>
					) }
				</PanelBody>
			</InspectorControls>
			<div
				style={ style }
				className={ `wp-block-lf-icon-list ${
					selectedIcon ? selectedIcon : ''
				}` }
			>
				<RichText
					identifier="values"
					multiline="li"
					tagName="ul"
					onChange={ ( nextValues ) =>
						setAttributes( { values: nextValues } )
					}
					value={ values }
					className={ className }
					placeholder={ __( 'Write list…' ) }
					onMerge={ mergeBlocks }
					onSplit={ ( value ) =>
						createBlock( 'lf/icon-list', {
							...attributes,
							values: value,
						} )
					}
					onReplace={ onReplace }
					onRemove={ () => onReplace( [] ) }
					type={ type }
				>
					{ controls }
				</RichText>
			</div>
		</>
	);
}
