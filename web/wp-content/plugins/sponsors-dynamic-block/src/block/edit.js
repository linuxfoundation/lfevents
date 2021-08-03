const { __ } = wp.i18n;
const { Component } = wp.element;
const { InspectorControls } = wp.blockEditor;
const { TextControl, PanelBody, SelectControl } = wp.components;
const { apiFetch } = wp;

import AsyncSelect from 'react-select/async';
import debounce from 'debounce-promise';

export default class Edit extends Component {
	construct( props ) {
		this.props = props;
	}

	componentDidMount() {
		const { attributes, setAttributes } = this.props;

		setAttributes( {
			sponsors: this.sortList( attributes.sponsors ),
		} );
	}

	sortList( list ) {
		if ( ! list ) {
			return list;
		}

		const newList = list.slice();
		newList.sort( ( a, b ) => a.label.localeCompare( b.label ) );

		return newList;
	}

	render() {
		const { setAttributes, attributes: { sponsors, tierName, tierSize } } = this.props;

		const onTierNameChange = ( changes ) => {
			setAttributes( {
				tierName: changes,
			} );
		};

		const onTierSizeChange = ( changes ) => {
			setAttributes( {
				tierSize: changes,
			} );
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
			const value = inputValue.replace( /\W/g, '' );
			return apiFetch( {
				path: `/wp/v2/lfe_sponsor/?per_page=100&search=${ value }`,
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
						label="Sponsor Tier Name:"
						value={ tierName }
						onChange={ onTierNameChange }
						placeholder="DIAMOND"
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
			<div key="sponsors-dynamic-block-edit" className={ this.props.className }>
				<p><strong>{ tierName } Sponsors</strong></p>
				<AsyncSelect
					styles={ { menu: ( styles ) => ( { ...styles, zIndex: 99 } ) } }
					isMulti
					value={ sponsors }
					defaultOptions
					loadOptions={ loadOptions }
					onChange={ ( value ) => setAttributes( { sponsors: this.sortList( value ) } ) }
					placeholder="Type to search"
				/>
				<p><em>Note: Sponsors will appear alphabetical on the site.</em></p>
			</div>,
		];
	}
}
