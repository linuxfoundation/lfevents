/* eslint-disable react/jsx-key */
import icon, {
	RegularCountdownIcon,
	CircularCountdownIcon,
	TickingCountdownIcon,
} from './components/Icons';

import Timer from './components/Timer';

import './styles/editor.scss';

const { __ } = wp.i18n;
const { registerBlockType } = wp.blocks;
const { Fragment } = wp.element;
const { InspectorControls, RichText, PanelColorSettings, BlockControls } =
	wp.blockEditor || wp.editor;
const { DateTimePicker, IconButton, PanelBody, PanelRow, TextControl, Toolbar } = wp.components;
const { withSelect } = wp.data;

registerBlockType( 'cgb/countdown-block', {
	title: __( 'Countdown' ),
	icon: icon,
	category: 'common',
	keywords: [ __( 'Countdown' ), __( 'Timer' ), __( 'Ultimate Blocks' ) ],
	attributes: {
		blockID: {
			type: 'string',
			default: '',
		},
		endDate: {
			type: 'number',
			default: 60 * ( 1440 + Math.ceil( Date.now() / 60000 ) ), // 24 hours from Date.now
		},
		style: {
			type: 'string',
			default: 'Odometer', //available types: Regular, Circular, Odometer
		},
		expiryMessage: {
			type: 'string',
			default: '',
		},
		messageAlign: {
			type: 'string',
			default: 'left',
		},
		circleColor: {
			type: 'string',
			default: '#2DB7F5',
		},
		labelWeeks: {
			type: 'string',
			default: 'Weeks',
		},
		labelDays: {
			type: 'string',
			default: 'Days',
		},
		labelHours: {
			type: 'string',
			default: 'Hours',
		},
		labelMinutes: {
			type: 'string',
			default: 'Minutes',
		},
		labelSeconds: {
			type: 'string',
			default: 'Seconds',
		},
	},

	edit: withSelect( ( select, ownProps ) => ( {
		block: ( select( 'core/block-editor' ) || select( 'core/editor' ) ).getBlock(
			ownProps.clientId
		),
	} ) )( function( props ) {
		const { isSelected, setAttributes, block, attributes } = props;
		const {
			blockID,
			style,
			endDate,
			expiryMessage,
			circleColor,
			messageAlign,
			labelWeeks,
			labelDays,
			labelHours,
			labelMinutes,
			labelSeconds,
		} = attributes;

		if ( blockID !== block.clientId ) {
			setAttributes( { blockID: block.clientId } );
		}

		return [
			isSelected && (
				<InspectorControls>
					{ style === 'Circular' && (
						<PanelColorSettings
							title={ __( 'Circle Color' ) }
							initialOpen={ true }
							colorSettings={ [
								{
									value: circleColor,
									onChange: colorValue =>
										setAttributes( {
											circleColor: colorValue,
										} ),
									label: '',
								},
							] }
						/>
					) }
					<PanelBody title={ __( 'Timer expiration' ) }>
						<DateTimePicker
							currentDate={ endDate * 1000 }
							onChange={ value => {
								setAttributes( {
									endDate: Math.floor( Date.parse( value ) / 1000 ),
								} );
							} }
						/>
					</PanelBody>
					<PanelBody title={ __( 'Labels' ) }>
						<PanelRow>
							<TextControl
								label="Weeks:"
								value={ labelWeeks }
								onChange={ value => {
									setAttributes( { labelWeeks: value } );
								} }
							/>
						</PanelRow>
						<PanelRow>
							<TextControl
								label="Days:"
								value={ labelDays }
								onChange={ value => {
									setAttributes( { labelDays: value } );
								} }
							/>
						</PanelRow>
						<PanelRow>
							<TextControl
								label="Hours:"
								value={ labelHours }
								onChange={ value => {
									setAttributes( { labelHours: value } );
								} }
							/>
						</PanelRow>
						<PanelRow>
							<TextControl
								label="Minutes:"
								value={ labelMinutes }
								onChange={ value => {
									setAttributes( { labelMinutes: value } );
								} }
							/>
						</PanelRow>
						<PanelRow>
							<TextControl
								label="Seconds:"
								value={ labelSeconds }
								onChange={ value => {
									setAttributes( { labelSeconds: value } );
								} }
							/>
						</PanelRow>
					</PanelBody>
				</InspectorControls>
			),
			isSelected && (
				<BlockControls>
					<Toolbar>
						<IconButton
							isPrimary={ style === 'Regular' }
							icon={ RegularCountdownIcon }
							label={ __( 'Regular' ) }
							onClick={ () => setAttributes( { style: 'Regular' } ) }
						/>
						<IconButton
							isPrimary={ style === 'Circular' }
							icon={ CircularCountdownIcon }
							label={ __( 'Circular' ) }
							onClick={ () => setAttributes( { style: 'Circular' } ) }
						/>
						<IconButton
							isPrimary={ style === 'Odometer' }
							icon={ TickingCountdownIcon }
							label={ __( 'Odometer' ) }
							onClick={ () => setAttributes( { style: 'Odometer' } ) }
						/>
					</Toolbar>
					<Toolbar>
						{ [ 'left', 'center', 'right', 'justify' ].map( a => (
							<IconButton
								icon={ `editor-${ a === 'justify' ? a : 'align' + a }` }
								label={ __(
									( a !== 'justify' ? 'Align ' : '' ) +
									a[ 0 ].toUpperCase() +
									a.slice( 1 )
								) }
								isActive={ a }
								onClick={ () => {
									setAttributes( { messageAlign: a } );
								} }
							/>
						) ) }
					</Toolbar>
				</BlockControls>
			),
			<Fragment>
				<Timer
					labels={ {
						weeks: labelWeeks,
						days: labelDays,
						hours: labelHours,
						minutes: labelMinutes,
						seconds: labelSeconds,
					} }
					timerStyle={ style }
					deadline={ endDate }
					color={ circleColor }
				/>
				<RichText
					tagName="div"
					placeholder={ __( 'Text to show after the countdown is over' ) }
					style={ { textAlign: messageAlign } }
					value={ expiryMessage }
					onChange={ text => setAttributes( { expiryMessage: text } ) }
					keepPlaceholderOnFocus={ true }
				/>
			</Fragment>,
		];
	} ),

	save: () => null,
} );
