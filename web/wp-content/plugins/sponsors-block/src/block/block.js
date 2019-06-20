/**
 * BLOCK: sponsors-block
 *
 * Registering a basic block with Gutenberg.
 * Simple block, renders and saves the same content without any interactivity.
 */


const { __ } = wp.i18n; // Import __() from wp.i18n
const { registerBlockType } = wp.blocks; // Import registerBlockType() from wp.blocks
const { MediaUpload, InspectorControls } = wp.editor; //Import MediaUpload from wp.editor
const { Button, SelectControl, PanelBody, PanelRow } = wp.components; //Import Button from wp.components


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

registerBlockType( 'cgb/sponsors-block', {
    title: __( 'Sponsors' ), // Block title.
    icon: 'editor-kitchensink', // Block icon from Dashicons → https://developer.wordpress.org/resource/dashicons/.
    category: 'common', // Block category
    keywords: [ //Keywords
        __('sponsors'),
    ],
    attributes: { //Attributes
        images : { //Images array
            type: 'array',
		},
		sponsorClass : {
			type: 'string',
			default: 'large',
		}
},

    /**
     * The edit function describes the structure of your block in the context of the editor.
     * This represents what the editor will render when the block is used.
     *
     * The "edit" property must be a valid function.
     *
     * @link https://wordpress.org/gutenberg/handbook/block-api/block-edit-save/
     */
    edit({ attributes, className, setAttributes, focus }) {

        //Destructuring the images array attribute
        const {images = [], sponsorClass} = attributes;


        // This removes an image from the gallery
        const removeImage = (removeImage) => {
                    //filter the images
                    const newImages = images.filter( (image) => {
                        //If the current image is equal to removeImage the image will be returnd
                        if(image.id != removeImage.id) {
                            return image;
                        }
                    });

                    //Saves the new state
                    setAttributes({
                        images:newImages,
                    })
        }


        //Displays the images
        const displayImages = (images) => {
            return (
				//Loops through the images
                images.map( (image) => {
                    return (
						<li class="blocks-gallery-item"><figure>
                            <img className='gallery-item' src={image.url} key={ images.id } />
                            <div className='remove-item' onClick={() => removeImage(image)}><span class="dashicons dashicons-trash"></span></div>
						</figure></li>
                    )
                })

            )
        }

		function onSponsorClassChange(changes) {
			setAttributes({
				sponsorClass: changes
			})
		}

        //JSX to return
        return [
			<InspectorControls>
				<PanelBody><PanelRow>
				<div>
					<SelectControl
						label='Select a style for the sponsor images:'
						value={sponsorClass}
						onChange={onSponsorClassChange}
						options={ [
							{ label: 'Large', value: 'large' },
							{ label: 'Medium', value: 'medium' },
							{ label: 'Small', value: 'small' },
						] }
						/>
				</div>
				</PanelRow></PanelBody>
			</InspectorControls>,
            <div>
                <ul class="wp-block-gallery columns-3 is-cropped">
                    {displayImages(images)}
                </ul>
                <br/>
                <MediaUpload
                        onSelect={(media) => {setAttributes({images: [...images, ...media].sort((a,b) => (a.title > b.title) ? 1 : ((b.title > a.title) ? -1 : 0) )}) }}
                        type="image"
                        multiple={true}
						value={images}

                        render={({open}) => (
                            <Button className="select-images-button is-button is-default is-large" onClick={open}>
                                Add images
                            </Button>
                        )}
                    />
            </div>

		];
    },

    /**
     * The save function defines the way in which the different attributes should be combined
     * into the final markup, which is then serialized by Gutenberg into post_content.
     *
     * The "save" property must be specified and must be a valid function.
     *
     * @link https://wordpress.org/gutenberg/handbook/block-api/block-edit-save/
     */
    save({attributes}) {
        //Destructuring the images array attribute
        const {images = [], sponsorClass} = attributes;

        // Displays the images
        const displayImages = (images) => {
            return (
                images.map( (image,index) => {
                    return (
                            <li class="blocks-gallery-item"><figure>
							<a href={image.description}>
							<img
                                src={image.url}
                                alt={image.alt}
								data-id={image.id}
								data-link={image.url}
								className={'wp-image-' + image.id}
                                />
							</a></figure></li>
                    )
                })
            )
        }

        //JSX to return
        return (
			<ul className={'wp-block-gallery alignwide columns-3 is-cropped ' + sponsorClass}>{ displayImages(images) }</ul>
        );

    },
} );