import { __ } from '@wordpress/i18n';
// import { createBlock } from '@wordpress/blocks';
import {
	RichText,
	InspectorControls,
} from '@wordpress/block-editor';
import {
	PanelBody,
	SelectControl,
	RangeControl,
} from '@wordpress/components';

import './editor.scss';

export default function Edit( {
	attributes,
	setAttributes,
	mergeBlocks,
	className,
} ) {
	const { values, type, selectedIcon, iconSize, columnCount, listGap } = attributes;

const style = {
	'--list-gap': listGap ? `${ listGap }px` : '10',
	'--icon-size': iconSize ? `${ iconSize }px` : '20px',
	'--column-count': columnCount ? `${ columnCount }` : '1',
}

	return (
		<>
		<InspectorControls key="icon-list-block-panel">
		<PanelBody title={ __( 'Settings' ) } initialOpen={ true }>
					<RangeControl
						label={ __( 'No. of columns' ) }
						min={ 1 }
						max={ 4 }
						value={ columnCount }
						onChange={ value => setAttributes( { columnCount: value } ) }
					/>
					<RangeControl
						label={ __( 'List item gap' ) }
						min={ 0 }
						max={ 40 }
						value={ listGap }
						onChange={ value => setAttributes( { listGap: value } ) }
					/>
					<SelectControl
						label={ __( 'List icon' ) }
						value={ selectedIcon }
						options={ [
							{
								label: __( 'Default' ),
								value: ''
							},
							{
								label: __( 'Angle Right' ),
								value: 'is-style-list-angle-right'
							},
							{
								label: __( 'Check' ),
								value: 'is-style-list-check'
							},
							{
								label: __( 'Plus' ),
								value: 'is-style-list-plus'
							}
						] }
						onChange={ value =>
							setAttributes( { selectedIcon: '' !== value ? value : '' } ) }
					/>
					 {selectedIcon
				? <RangeControl
				label={ __( 'Icon size' ) }
				min={ 8 }
				max={ 40 }
				value={ iconSize }
				onChange={ value => setAttributes( { iconSize: value } ) }
			/>
				: ''
					 }
				</PanelBody>
	</InspectorControls>
	<div style={ style } className={ `wp-block-lf-icon-list ${ selectedIcon }`}>
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
				type={ type }
			>
			</RichText>
			</div>
		</>
	);
}
