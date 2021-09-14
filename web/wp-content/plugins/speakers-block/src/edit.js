import {
	useBlockProps,
	InspectorControls,
	PanelColorSettings,
} from '@wordpress/block-editor';

import {
	RadioControl,
	PanelBody,
	PanelRow,
	SelectControl,
} from '@wordpress/components';

const { apiFetch } = wp;

import AsyncSelect from 'react-select/async';
import { components } from 'react-select';
import { DragDropContext, Droppable, Draggable } from 'react-beautiful-dnd';
import debounce from 'debounce-promise';

import './editor.scss';

export default function Edit( { attributes, setAttributes } ) {
	const { speakers, colorMode, color1, color2, textColor } = attributes;

	const onDragEnd = ( result ) => {
		// dropped outside the list
		if ( ! result.destination ) {
			return;
		}

		const items = reorderList(
			speakers,
			result.source.index,
			result.destination.index
		);

		setAttributes( {
			speakers: items,
		} );
	};

	const reorderList = ( list, startIndex, endIndex ) => {
		const newList = list.slice();
		const result = Array.from( newList );
		const [ removed ] = result.splice( startIndex, 1 );

		result.splice( endIndex, 0, removed );

		return result;
	};

	const DragAndDropContainer = ( { data, ...restProps } ) => {
		const getIndex = ( items, comparativeValue ) => {
			return items.findIndex(
				( item ) => item.value === comparativeValue
			);
		};

		return (
			<Draggable
				key={ `item-${ data.value }` }
				index={ getIndex( speakers, data.value ) }
				draggableId={ `item-${ data.value }` }
			>
				{ ( provided ) => (
					<div
						ref={ provided.innerRef }
						{ ...provided.draggableProps }
						{ ...provided.dragHandleProps }
					>
						<components.MultiValueContainer
							{ ...restProps }
							data={ data }
						/>
					</div>
				) }
			</Draggable>
		);
	};

	const prepareOptions = ( list ) => {
		return list.map( ( item ) => {
			return {
				value: item.id,
				label: item.title.rendered,
			};
		} );
	};

	const searchSpeakers = ( inputValue ) => {
		const value = inputValue.replace( /\W/g, '' );
		return apiFetch( {
			path: `/wp/v2/lfe_speaker/?per_page=100&search=${ value }`,
		} ).then( ( posts ) => {
			return prepareOptions( posts );
		} );
	};

	const loadOptions = debounce(
		( inputValue ) => searchSpeakers( inputValue ),
		1000,
		{
			leading: false,
		}
	);

	return [
		<InspectorControls key="speakers-block-panel">
			<PanelBody title="Settings" initialOpen={ true }>
				<SelectControl
					label="Color options"
					help="Chose background colors"
					value={ colorMode }
					options={ [
						{
							label: 'Event Gradient',
							value: 'is-style-event-gradient',
						},
						{
							label: 'Custom Colors',
							value: 'is-style-custom-colors',
						},
					] }
					onChange={ ( value ) =>
						setAttributes( {
							colorMode: '' !== value ? value : '',
						} )
					}
				/>
				<PanelRow>
					<RadioControl
						label="Text color:"
						help="Chose text color"
						selected={ textColor }
						options={ [
							{ label: 'White', value: '#FFFFFF' },
							{ label: 'Black', value: '#000000' },
						] }
						onChange={ ( value ) => {
							setAttributes( {
								textColor: value,
							} );
						} }
					/>
				</PanelRow>
				{ colorMode === 'is-style-custom-colors' && (
					<PanelColorSettings
						title="Color Settings"
						initialOpen={ true }
						colorSettings={ [
							{
								value: color1,
								onChange: ( colorValue ) =>
									setAttributes( {
										color1: colorValue,
									} ),
								label: 'Color 1',
							},
							{
								value: color2,
								onChange: ( colorValue ) =>
									setAttributes( {
										color2: colorValue,
									} ),
								label: 'Color 2',
							},
						] }
					></PanelColorSettings>
				) }
			</PanelBody>
		</InspectorControls>,
		<div { ...useBlockProps() } key="speakers-block-edit">
			<p>
				<strong>Featured Speakers:</strong>
			</p>
			<DragDropContext onDragEnd={ onDragEnd }>
				<Droppable direction="horizontal" droppableId="droppable">
					{ ( provided ) => (
						<div
							{ ...provided.droppableProps }
							ref={ provided.innerRef }
						>
							<AsyncSelect
								styles={ {
									menu: ( styles ) => ( {
										...styles,
										zIndex: 99,
									} ),
								} }
								isMulti
								value={ speakers }
								defaultOptions
								loadOptions={ loadOptions }
								components={ {
									MultiValueContainer: DragAndDropContainer,
								} }
								onChange={ ( value ) =>
									setAttributes( { speakers: value } )
								}
							/>
							{ provided.placeholder }
						</div>
					) }
				</Droppable>
			</DragDropContext>
		</div>,
	];
}
