/**
 * BLOCK: sponsors-dynamic-block
 *
 * Registering a basic block with Gutenberg.
 * Simple block, renders and saves the same content without any interactivity.
 */

//  Import CSS.
import './styles/style.scss';
import './styles/editor.scss';

const { __ } = wp.i18n; // Import __() from wp.i18n
const { registerBlockType } = wp.blocks; // Import registerBlockType() from wp.blocks
const { InspectorControls } = wp.blockEditor;
const { TextControl, PanelBody, SelectControl } = wp.components; //Import Button from wp.components
const { apiFetch } = wp;

import AsyncSelect from 'react-select/async';
import { components } from 'react-select';
import { DragDropContext, Droppable, Draggable } from 'react-beautiful-dnd';
import debounce from 'debounce-promise';

/**
 * Register: aa Gutenberg Block.
 *
 * Registers a new block provided a unique name and an object defining its
 * behavior. Once registered, the block is made editor as an option to any
 * editor interface where blocks are implemented.
 *
 * @link https://wordpress.org/gutenberg/handbook/block-api/
 * @param  {string}   name     Block name.
 * @param  {Object}   settings Block settings.
 * @return {?WPBlock}          The block, if it has been successfully
 *                             registered; otherwise `undefined`.
 */
registerBlockType( 'cgb/block-sponsors-dynamic-block', {
	// Block name. Block names must be string that contains a namespace prefix. Example: my-plugin/my-custom-block.
	title: __( 'Sponsors Dynamic' ), // Block title.
	icon: 'editor-kitchensink', // Block icon from Dashicons → https://developer.wordpress.org/resource/dashicons/.
	category: 'common', // Block category — Group blocks together based on common traits E.g. common, formatting, layout widgets, embed.
	keywords: [
		__( 'Sponsors' ),
	],
	attributes: {
		sponsors: {
			type: 'array',
			default: [],
		},
		tierName: {
			type: 'string',
			default: '',
		},
		tierSize: {
			type: 'string',
			default: 'medium',
		},
	},

	edit: ( props ) => {
		const { setAttributes, attributes: { sponsors, tierName, tierSize } } = props;

		function onTierNameChange(changes) {
			setAttributes({
			  tierName: changes
			})
		}
	  
		function onTierSizeChange(changes) {
			setAttributes({
			  tierSize: changes
			})
		}
	  
		const onDragEnd = ( result ) => {
			// dropped outside the list
			if ( ! result.destination ) {
				return;
			}

			const items = reorderList(
				sponsors,
				result.source.index,
				result.destination.index
			);

			setAttributes( {
				sponsors: items,
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
				return items.findIndex( ( item ) => item.value === comparativeValue );
			};

			return (
				<Draggable
					key={ `item-${ data.value }` }
					index={ getIndex( sponsors, data.value ) }
					draggableId={ `item-${ data.value }` }>
					{ ( provided ) => (
						<div
							ref={ provided.innerRef }
							{ ...provided.draggableProps }
							{ ...provided.dragHandleProps }>
							<components.MultiValueContainer { ...restProps } data={ data } />
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

		const searchSponsors = inputValue => {
			return apiFetch( {
				path: '/wp/v2/lfe_sponsor/?per_page=100&search=' + inputValue.replace( /\W/g, '' ),
			} ).then( posts => {
				return prepareOptions( posts );
			} );
		};

		const loadOptions = debounce( ( inputValue ) => searchSponsors( inputValue ), 1000, {
			leading: false,
		} );

		return [
			<InspectorControls key="sponsors-dynamic-block-panel">
				<PanelBody>
					<TextControl
					label='Sponsor Tier Name:'
					value={ tierName }
					onChange={ onTierNameChange }
					placeholder='DIAMOND'
					/>
					<SelectControl
					label={ __( 'Logo Size:' ) }
					value={ tierSize }
					onChange={ onTierSizeChange }
					options={ [
						{ value: 'jumbo', label: 'Jumbo' },
						{ value: 'largest', label: 'Largest' },
						{ value: 'larger', label: 'Larger' },
						{ value: 'large', label: 'Large' },
						{ value: 'medium', label: 'Medium' },
						{ value: 'small', label: 'Small' },
						{ value: 'smaller', label: 'Smaller' },
						{ value: 'smallest', label: 'Smallest' },
					] }
					/>
				</PanelBody>
			</InspectorControls>,
			<div key="sponsors-dynamic-block-edit" className={ props.className }>
				<p><strong>{ tierName } Sponsors</strong>
					<DragDropContext onDragEnd={ onDragEnd }>
						<Droppable direction="horizontal" droppableId="droppable">
							{ ( provided ) => (
								<div
									{ ...provided.droppableProps }
									ref={ provided.innerRef }>
									<AsyncSelect
										styles={ { menu: ( styles ) => ( { ...styles, zIndex: 99 } ) } }
										isMulti
										value={ sponsors }
										defaultOptions
										loadOptions={ loadOptions }
										components={ { MultiValueContainer: DragAndDropContainer } }
										onChange={ ( value ) => setAttributes( { sponsors: value } ) }
									/>
									{ provided.placeholder }
								</div>
							) }
						</Droppable>
					</DragDropContext>
					<em>Note: Sponsors will appear alphabetical on the site.</em>
				</p>
			</div>,
		];
	},
} );
