/* eslint-disable react/jsx-key */
import icon, {
	RegularCountdownIcon,
	CircularCountdownIcon,
	TickingCountdownIcon,
} from './components/Icons';

import './styles/editor.scss';

const { __ } = wp.i18n;
const { registerBlockType } = wp.blocks;
const { Fragment } = wp.element;
const { InspectorControls, RichText, PanelColorSettings, BlockControls } =
	wp.blockEditor || wp.editor;
const { DateTimePicker, Button, PanelBody, PanelRow, TextControl, ToolbarGroup, ToolbarButton } = wp.components;
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
			type: 'string',
			default: new Date(),
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
				<InspectorControls key="Countdown">
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
							currentDate={ endDate }
							onChange={ ( newDate ) => {
								setAttributes( { endDate: newDate } );
							} }
							is12Hour={ true }
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
					<ToolbarGroup>
						<ToolbarButton
							key="regular"
							isPrimary={ style === 'Regular' }
							icon={ RegularCountdownIcon }
							label={ __( 'Regular' ) }
							onClick={ () => setAttributes( { style: 'Regular' } ) }
						/>
						<ToolbarButton
							key="circular"
							isPrimary={ style === 'Circular' }
							icon={ CircularCountdownIcon }
							label={ __( 'Circular' ) }
							onClick={ () => setAttributes( { style: 'Circular' } ) }
						/>
						<ToolbarButton
							key="odometer"
							isPrimary={ style === 'Odometer' }
							icon={ TickingCountdownIcon }
							label={ __( 'Odometer' ) }
							onClick={ () => setAttributes( { style: 'Odometer' } ) }
						/>
					</ToolbarGroup>
					<ToolbarGroup>
						{ [ 'left', 'center', 'right', 'justify' ].map( a => (
							<ToolbarButton
								key={ `control-align-${ a }` }
								icon={ `editor-${ a === 'justify' ? a : 'align' + a }` }
								label={ __(
									( a !== 'justify' ? 'Align ' : '' ) +
									a[ 0 ].toUpperCase() +
									a.slice( 1 )
								) }
								isactive={ a }
								onClick={ () => {
									setAttributes( { messageAlign: a } );
								} }
							/>
						) ) }
					</ToolbarGroup>
				</BlockControls>
			),
			<Fragment key="timer-wrapper">
				<h2
					style={ { 
						textAlign: messageAlign,
						color: 'green',
					} }
				>Countdown Timer</h2>
				<RichText
					tagName="div"
					placeholder={ __( 'Text to show after the countdown is over' ) }
					style={ { textAlign: messageAlign } }
					value={ expiryMessage }
					onChange={ text => setAttributes( { expiryMessage: text } ) }
					keepPlaceholderOnFocus
				/>
			</Fragment>,
		];
	} ),

	save: () => null,
} );
