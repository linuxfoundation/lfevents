/**
 * BLOCK: text-on-image-block
 *
 * Registering a basic block with Gutenberg.
 * Simple block, renders and saves the same content without any interactivity.
 */

//  Import CSS.
import './style.scss';
import './editor.scss';

const { __ } = wp.i18n;
const { registerBlockType } = wp.blocks;
const { RichText, MediaUpload } = wp.blockEditor;

registerBlockType( 'cgb/block-text-on-image-block', {
	title: __( 'Text on Image Block' ),
	icon: 'welcome-widgets-menus',
	category: 'common',
	keywords: [
		__( 'text-on-image-block' ),
	],
	attributes: {
		bodyContent: {
			type: 'string',
			default: '',
		},
		imgUrl: {
			type: 'string',
			default: 'https://placehold.it/250?text=Sample+Image',
		},
		imgId: {
			type: 'number',
			default: 0,
		},
	},

	edit: function( props ) {
		const { className, setAttributes } = props;
		const { attributes } = props;

		const onChangeContent = ( changes ) => {
			setAttributes( {
				bodyContent: changes,
			} );
		};

		const handleBetterImageSize = ( value ) => {
			const { sizes } = value;
			return ( sizes.medium || sizes.full ).url;
		};

		const onSelectImage = ( value ) => {
			setAttributes( {
				imgId: value.id,
				imgUrl: handleBetterImageSize( value ),
			} );
		};

		return (
			<div className={ className }>
				<div className="media">
					<MediaUpload
						onSelect={ onSelectImage }
						render={ ( { open } ) => {
							return (
								<button onClick={ open }>
									<img src={ attributes.imgUrl } alt="" />
								</button>
							);
						} }
					/>
				</div>
				<div className="copy">
					<RichText
						className="copy-bd"
						tagName="div"
						placeholder="Enter text here that will float on top of the image."
						value={ attributes.bodyContent }
						onChange={ onChangeContent }
						keepPlaceholderOnFocus={ true }
					/>
				</div>
			</div>
		);
	},

	save: () => null,
} );
