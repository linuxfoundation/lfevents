/**
 * BLOCK: pricing-block
 *
 * Registering a basic block with Gutenberg.
 * Simple block, renders and saves the same content without any interactivity.
 */

//  Import CSS.
import './style.scss';
import './editor.scss';

const { __ } = wp.i18n;
const { registerBlockType } = wp.blocks;
const { PlainText, InspectorControls, PanelColorSettings } = wp.blockEditor;
const { PanelBody, PanelRow, TextControl, SelectControl } = wp.components;

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
registerBlockType( 'cgb/block-pricing-block', {
	title: __( 'Pricing Block' ),
	icon: 'calendar',
	category: 'common',
	supports: {},
	example: {},
	attributes: {
		topLabels: {
			type: 'array',
			default: [ '', '', '', '' ],
		},
		dates: {
			type: 'array',
			default: [ '', '', '', '', '' ],
		},
		leftLabels: {
			type: 'array',
			default: [ '', '', '', '' ],
		},
		prices: {
			type: 'array',
			default: [ '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', ],
		},
		align: {
			type: 'string',
			default: 'full',
		},
		color1: {
			type: 'string',
		},
		color2: {
			type: 'string',
		},
		color3: {
			type: 'string',
		},
		color4: {
			type: 'string',
		},
		colorText: {
			type: 'string',
		},
		expireText: {
			type: 'string',
			default: 'Expired',
		},
		timeZone: {
			type: 'string',
			default: '-0700',
		},
		language: {
			type: 'string',
			default: 'ENG',
		},
	},
	edit: ( function( props ) {
		const { setAttributes, attributes: { topLabels, dates, leftLabels, color1, color2, color3, color4, colorText, prices, expireText, timeZone, language } } = props;

		function updateTopLabels( value, index ) {
			const newTopLabels = [ ...topLabels ];
			newTopLabels[ index ] = value;
			setAttributes( { topLabels: newTopLabels } );
		}
		function updateLeftLabels( value, index ) {
			const newLeftLabels = [ ...leftLabels ];
			newLeftLabels[ index ] = value;
			setAttributes( { leftLabels: newLeftLabels } );
		}
		function updateDates( value, index ) {
			const newDates = [ ...dates ];
			newDates[ index ] = value;
			setAttributes( { dates: newDates } );
		}
		function updatePrices( value, indexX ) {
			const newPrices = [ ...prices ];
			newPrices[ indexX ] = value;
			setAttributes( { prices: newPrices } );
		}

		return [
			<InspectorControls key="wp-block-cgb-block-pricing-block-panel">
				<PanelColorSettings
					title="Color Settings"
					initialOpen={ true }
					colorSettings={ [
						{
							value: color1,
							onChange: colorValue =>
								setAttributes( {
									color1: colorValue,
								} ),
							label: 'Color 1',
						},
						{
							value: color2,
							onChange: colorValue =>
								setAttributes( {
									color2: colorValue,
								} ),
							label: 'Color 2',
						},
						{
							value: color3,
							onChange: colorValue =>
								setAttributes( {
									color3: colorValue,
								} ),
							label: 'Color 3',
						},
						{
							value: color4,
							onChange: colorValue =>
								setAttributes( {
									color4: colorValue,
								} ),
							label: 'Color 4',
						},
						{
							value: colorText,
							onChange: colorValue =>
								setAttributes( {
									colorText: colorValue,
								} ),
							label: 'Text Color',
						},
					] }
				>
				</PanelColorSettings>
				<PanelBody><PanelRow>
					<div>
						<TextControl
							label="Text to show when the price level has expired:"
							value={ expireText }
							onChange={ value => setAttributes( { expireText: value } ) }
						/>
						<SelectControl
							label="Local timezone of the Event:"
							value={ timeZone }
							onChange={ value => setAttributes( { timeZone: value } ) }
							options={ [
								{ label: 'UTC-12', value: '-1200' },
								{ label: 'UTC-11:30', value: '-1130' },
								{ label: 'UTC-11', value: '-1100' },
								{ label: 'UTC-10:30', value: '-1030' },
								{ label: 'UTC-10', value: '-1000' },
								{ label: 'UTC-9:30', value: '-0930' },
								{ label: 'UTC-9', value: '-0900' },
								{ label: 'UTC-8:30', value: '-0830' },
								{ label: 'UTC-8', value: '-0800' },
								{ label: 'UTC-7:30', value: '-0730' },
								{ label: 'UTC-7', value: '-0700' },
								{ label: 'UTC-6:30', value: '-0630' },
								{ label: 'UTC-6', value: '-0600' },
								{ label: 'UTC-5:30', value: '-0530' },
								{ label: 'UTC-5', value: '-0500' },
								{ label: 'UTC-4:30', value: '-0430' },
								{ label: 'UTC-4', value: '-0400' },
								{ label: 'UTC-3:30', value: '-0330' },
								{ label: 'UTC-3', value: '-0300' },
								{ label: 'UTC-2:30', value: '-0230' },
								{ label: 'UTC-2', value: '-0200' },
								{ label: 'UTC-1:30', value: '-0130' },
								{ label: 'UTC-1', value: '-0100' },
								{ label: 'UTC-0:30', value: '-0030' },
								{ label: 'UTC+0', value: '+0000' },
								{ label: 'UTC+0:30', value: '+0030' },
								{ label: 'UTC+1', value: '+0100' },
								{ label: 'UTC+1:30', value: '+0130' },
								{ label: 'UTC+2', value: '+0200' },
								{ label: 'UTC+2:30', value: '+0230' },
								{ label: 'UTC+3', value: '+0300' },
								{ label: 'UTC+3:30', value: '+0330' },
								{ label: 'UTC+4', value: '+0400' },
								{ label: 'UTC+4:30', value: '+0430' },
								{ label: 'UTC+5', value: '+0500' },
								{ label: 'UTC+5:30', value: '+0530' },
								{ label: 'UTC+5:45', value: '+0545' },
								{ label: 'UTC+6', value: '+0600' },
								{ label: 'UTC+6:30', value: '+0630' },
								{ label: 'UTC+7', value: '+0700' },
								{ label: 'UTC+7:30', value: '+0730' },
								{ label: 'UTC+8', value: '+0800' },
								{ label: 'UTC+8:30', value: '+0830' },
								{ label: 'UTC+8:45', value: '+0845' },
								{ label: 'UTC+9', value: '+0900' },
								{ label: 'UTC+9:30', value: '+0930' },
								{ label: 'UTC+10', value: '+1000' },
								{ label: 'UTC+10:30', value: '+1030' },
								{ label: 'UTC+11', value: '+1100' },
								{ label: 'UTC+11:30', value: '+1130' },
								{ label: 'UTC+12', value: '+1200' },
								{ label: 'UTC+12:45', value: '+1245' },
								{ label: 'UTC+13', value: '+1300' },
								{ label: 'UTC+13:45', value: '+1345' },
								{ label: 'UTC+14', value: '+1400' },
							] }
						/>
						<SelectControl
							label="Language for the pricing dates:"
							value={ language }
							onChange={ value => setAttributes( { language: value } ) }
							options={ [
								{ label: 'English', value: 'ENG' },
								{ label: 'Chinese', value: 'CHI' },
								{ label: 'Both', value: 'BOTH' },
							] }
						/>
					</div>
				</PanelRow></PanelBody>
			</InspectorControls>,
			<div className={ props.className } key="wp-block-cgb-block-pricing-block-out">
				<h3>Event Pricing Table</h3>
				<p>Fill in the rows and columns as appropriate for the Event and leave the remaining ones empty. Make sure your dates are of the format yyyy/mm/dd.</p>
				<table className="wp-block-cgb-block-pricing-block">
					<thead>
						<tr>
							<td></td>
							<td>
								<PlainText
									value={ topLabels[ 0 ] }
									onChange={ value => updateTopLabels( value, 0 ) }
									placeholder="Early Bird"
								/>
							</td>
							<td>
								<PlainText
									value={ topLabels[ 1 ] }
									onChange={ value => updateTopLabels( value, 1 ) }
									placeholder="Standard"
								/>
							</td>
							<td>
								<PlainText
									value={ topLabels[ 2 ] }
									onChange={ value => updateTopLabels( value, 2 ) }
									placeholder="Late"
								/>
							</td>
							<td>
								<PlainText
									value={ topLabels[ 3 ] }
									onChange={ value => updateTopLabels( value, 3 ) }
									placeholder="Onsite"
								/>
							</td>
						</tr>
					</thead>
					<tbody>
						<tr>
							<td>Ticket sales open
								<PlainText
									value={ dates[ 0 ] }
									onChange={ value => updateDates( value, 0 ) }
									placeholder="2020/05/01"
								/>
							</td>
							<td>End of first round
								<PlainText
									value={ dates[ 1 ] }
									onChange={ value => updateDates( value, 1 ) }
									placeholder="2020/06/01"
								/>
							</td>
							<td>End of second round
								<PlainText
									value={ dates[ 2 ] }
									onChange={ value => updateDates( value, 2 ) }
									placeholder="2020/07/01"
								/>
							</td>
							<td>End of third round
								<PlainText
									value={ dates[ 3 ] }
									onChange={ value => updateDates( value, 3 ) }
									placeholder="2020/08/01"
								/>
							</td>
							<td>End of fourth round
								<PlainText
									value={ dates[ 4 ] }
									onChange={ value => updateDates( value, 4 ) }
									placeholder="2020/09/01"
								/>
							</td>
						</tr>
						<tr>
							<td>
								<PlainText
									value={ leftLabels[ 0 ] }
									onChange={ value => updateLeftLabels( value, 0 ) }
									placeholder="Corporate"
								/>
							</td>
							<td>
								<PlainText
									value={ prices[ 0 ] }
									onChange={ value => updatePrices( value, 0 ) }
									placeholder="$ 1,050"
								/>
							</td>
							<td>
								<PlainText
									value={ prices[ 1 ] }
									onChange={ value => updatePrices( value, 1 ) }
									placeholder="$ 1,250"
								/>
							</td>
							<td>
								<PlainText
									value={ prices[ 2 ] }
									onChange={ value => updatePrices( value, 2 ) }
									placeholder="$ 1,450"
								/>
							</td>
							<td>
								<PlainText
									value={ prices[ 3 ] }
									onChange={ value => updatePrices( value, 3 ) }
									placeholder="$ 1,550"
								/>
							</td>
						</tr>
						<tr>
							<td>
								<PlainText
									value={ leftLabels[ 1 ] }
									onChange={ value => updateLeftLabels( value, 1 ) }
									placeholder="Individual"
								/>
							</td>
							<td>
								<PlainText
									value={ prices[ 4 ] }
									onChange={ value => updatePrices( value, 4 ) }
									placeholder="$ 500"
								/>
							</td>
							<td>
								<PlainText
									value={ prices[ 5 ] }
									onChange={ value => updatePrices( value, 5 ) }
									placeholder="$ 600"
								/>
							</td>
							<td>
								<PlainText
									value={ prices[ 6 ] }
									onChange={ value => updatePrices( value, 6 ) }
									placeholder="$ 700"
								/>
							</td>
							<td>
								<PlainText
									value={ prices[ 7 ] }
									onChange={ value => updatePrices( value, 7 ) }
									placeholder="$ 800"
								/>
							</td>
						</tr>
						<tr>
							<td>
								<PlainText
									value={ leftLabels[ 2 ] }
									onChange={ value => updateLeftLabels( value, 2 ) }
									placeholder="Academic"
								/>
							</td>
							<td>
								<PlainText
									value={ prices[ 8 ] }
									onChange={ value => updatePrices( value, 8 ) }
									placeholder="$ 150"
								/>
							</td>
							<td>
								<PlainText
									value={ prices[ 9 ] }
									onChange={ value => updatePrices( value, 9 ) }
									placeholder="$ 150"
								/>
							</td>
							<td>
								<PlainText
									value={ prices[ 10 ] }
									onChange={ value => updatePrices( value, 10 ) }
									placeholder="$ 150"
								/>
							</td>
							<td>
								<PlainText
									value={ prices[ 11 ] }
									onChange={ value => updatePrices( value, 11 ) }
									placeholder="$ 150"
								/>
							</td>
						</tr>
						<tr>
							<td>
								<PlainText
									value={ leftLabels[ 3 ] }
									onChange={ value => updateLeftLabels( value, 3 ) }
								/>
							</td>
							<td>
								<PlainText
									value={ prices[ 12 ] }
									onChange={ value => updatePrices( value, 12 ) }
								/>
							</td>
							<td>
								<PlainText
									value={ prices[ 13 ] }
									onChange={ value => updatePrices( value, 13 ) }
								/>
							</td>
							<td>
								<PlainText
									value={ prices[ 14 ] }
									onChange={ value => updatePrices( value, 14 ) }
								/>
							</td>
							<td>
								<PlainText
									value={ prices[ 15 ] }
									onChange={ value => updatePrices( value, 15 ) }
								/>
							</td>
						</tr>
						<tr>
							<td>
								<PlainText
									value={ leftLabels[ 4 ] }
									onChange={ value => updateLeftLabels( value, 4 ) }
								/>
							</td>
							<td>
								<PlainText
									value={ prices[ 16 ] }
									onChange={ value => updatePrices( value, 16 ) }
								/>
							</td>
							<td>
								<PlainText
									value={ prices[ 17 ] }
									onChange={ value => updatePrices( value, 17 ) }
								/>
							</td>
							<td>
								<PlainText
									value={ prices[ 18 ] }
									onChange={ value => updatePrices( value, 18 ) }
								/>
							</td>
							<td>
								<PlainText
									value={ prices[ 19 ] }
									onChange={ value => updatePrices( value, 19 ) }
								/>
							</td>
						</tr>
						<tr>
							<td>
								<PlainText
									value={ leftLabels[ 5 ] }
									onChange={ value => updateLeftLabels( value, 5 ) }
								/>
							</td>
							<td>
								<PlainText
									value={ prices[ 20 ] }
									onChange={ value => updatePrices( value, 20 ) }
								/>
							</td>
							<td>
								<PlainText
									value={ prices[ 21 ] }
									onChange={ value => updatePrices( value, 21 ) }
								/>
							</td>
							<td>
								<PlainText
									value={ prices[ 22 ] }
									onChange={ value => updatePrices( value, 22 ) }
								/>
							</td>
							<td>
								<PlainText
									value={ prices[ 23 ] }
									onChange={ value => updatePrices( value, 23 ) }
								/>
							</td>
						</tr>
						<tr>
							<td>
								<PlainText
									value={ leftLabels[ 6 ] }
									onChange={ value => updateLeftLabels( value, 6 ) }
								/>
							</td>
							<td>
								<PlainText
									value={ prices[ 24 ] }
									onChange={ value => updatePrices( value, 24 ) }
								/>
							</td>
							<td>
								<PlainText
									value={ prices[ 25 ] }
									onChange={ value => updatePrices( value, 25 ) }
								/>
							</td>
							<td>
								<PlainText
									value={ prices[ 26 ] }
									onChange={ value => updatePrices( value, 26 ) }
								/>
							</td>
							<td>
								<PlainText
									value={ prices[ 27 ] }
									onChange={ value => updatePrices( value, 27 ) }
								/>
							</td>
						</tr>
						<tr>
							<td>
								<PlainText
									value={ leftLabels[ 7 ] }
									onChange={ value => updateLeftLabels( value, 7 ) }
								/>
							</td>
							<td>
								<PlainText
									value={ prices[ 28 ] }
									onChange={ value => updatePrices( value, 28 ) }
								/>
							</td>
							<td>
								<PlainText
									value={ prices[ 29 ] }
									onChange={ value => updatePrices( value, 29 ) }
								/>
							</td>
							<td>
								<PlainText
									value={ prices[ 30 ] }
									onChange={ value => updatePrices( value, 30 ) }
								/>
							</td>
							<td>
								<PlainText
									value={ prices[ 31 ] }
									onChange={ value => updatePrices( value, 31 ) }
								/>
							</td>
						</tr>						
					</tbody>
				</table>
			</div>,
		];
	} ),
	save: function( props ) {
		return null; // See PHP side. This block is rendered on PHP.
	},
} );
