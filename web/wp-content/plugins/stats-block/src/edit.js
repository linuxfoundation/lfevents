import { __ } from '@wordpress/i18n';
import {
	useBlockProps,
	RichText,
	InspectorControls,
	BlockControls,
} from '@wordpress/block-editor';
import {
	PanelBody,
	RangeControl,
	ToggleControl,
	Button,
	ToolbarGroup,
	ToolbarButton,
} from '@wordpress/components';

const EMPTY_STAT = { number: '', label: '' };

export default function Edit( { attributes, setAttributes } ) {
	const {
		stats = [],
		columns = 4,
		animate = true,
		duration = 2000,
	} = attributes;

	const blockProps = useBlockProps( {
		className: 'lf-stats lf-stats--editor',
	} );

	const updateStat = ( index, key, value ) => {
		const next = stats.map( ( stat, i ) =>
			i === index ? { ...stat, [ key ]: value } : stat
		);
		setAttributes( { stats: next } );
	};

	const addStat = () => {
		setAttributes( { stats: [ ...stats, { ...EMPTY_STAT } ] } );
	};

	const removeStat = ( index ) => {
		setAttributes( { stats: stats.filter( ( _, i ) => i !== index ) } );
	};

	const moveStat = ( index, offset ) => {
		const target = index + offset;

		if ( target < 0 || target >= stats.length ) {
			return;
		}

		const next = [ ...stats ];
		[ next[ index ], next[ target ] ] = [ next[ target ], next[ index ] ];
		setAttributes( { stats: next } );
	};

	return (
		<>
			<BlockControls>
				<ToolbarGroup>
					<ToolbarButton
						icon="plus"
						label={ __( 'Add stat', 'stats-block' ) }
						onClick={ addStat }
					/>
				</ToolbarGroup>
			</BlockControls>

			<InspectorControls>
				<PanelBody title={ __( 'Layout', 'stats-block' ) }>
					<RangeControl
						label={ __( 'Columns', 'stats-block' ) }
						help={ __(
							'Applies to desktop widths. Cards drop to two across on tablet and stack on mobile.',
							'stats-block'
						) }
						value={ columns }
						onChange={ ( value ) =>
							setAttributes( { columns: value || 1 } )
						}
						min={ 1 }
						max={ 6 }
						__nextHasNoMarginBottom
					/>
				</PanelBody>
				<PanelBody title={ __( 'Animation', 'stats-block' ) }>
					<ToggleControl
						label={ __( 'Count up from zero', 'stats-block' ) }
						checked={ animate }
						onChange={ ( value ) =>
							setAttributes( { animate: value } )
						}
						__nextHasNoMarginBottom
					/>
					{ animate && (
						<RangeControl
							label={ __( 'Duration (ms)', 'stats-block' ) }
							value={ duration }
							onChange={ ( value ) =>
								setAttributes( { duration: value || 2000 } )
							}
							min={ 200 }
							max={ 6000 }
							step={ 100 }
							__nextHasNoMarginBottom
						/>
					) }
				</PanelBody>
			</InspectorControls>

			<div { ...blockProps }>
				<div
					className="lf-stats__grid"
					style={ { '--lf-stats-columns': String( columns ) } }
				>
					{ stats.map( ( stat, index ) => (
						// eslint-disable-next-line react/no-array-index-key
						<div className="lf-stats__item" key={ index }>
							<RichText
								tagName="span"
								className="lf-stats__number"
								allowedFormats={ [] }
								value={ stat.number }
								onChange={ ( value ) =>
									updateStat( index, 'number', value )
								}
								placeholder={ __( '10,000+', 'stats-block' ) }
							/>
							<RichText
								tagName="span"
								className="lf-stats__label"
								allowedFormats={ [] }
								value={ stat.label }
								onChange={ ( value ) =>
									updateStat( index, 'label', value )
								}
								placeholder={ __( 'Builders', 'stats-block' ) }
							/>
							<div className="lf-stats__item-actions">
								<Button
									size="small"
									icon="arrow-left-alt2"
									label={ __( 'Move left', 'stats-block' ) }
									disabled={ index === 0 }
									onClick={ () => moveStat( index, -1 ) }
								/>
								<Button
									size="small"
									icon="arrow-right-alt2"
									label={ __( 'Move right', 'stats-block' ) }
									disabled={ index === stats.length - 1 }
									onClick={ () => moveStat( index, 1 ) }
								/>
								<Button
									size="small"
									icon="trash"
									isDestructive
									label={ __( 'Remove stat', 'stats-block' ) }
									onClick={ () => removeStat( index ) }
								/>
							</div>
						</div>
					) ) }
				</div>

				<Button
					variant="secondary"
					className="lf-stats__add"
					onClick={ addStat }
				>
					{ __( 'Add stat', 'stats-block' ) }
				</Button>
			</div>
		</>
	);
}
