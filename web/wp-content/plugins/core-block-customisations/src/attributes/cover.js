// phpcs:IgnoreFile.
/* Add custom attribute to sidebar of cover block */
const { __ } = wp.i18n;
const { createHigherOrderComponent } = wp.compose;
const { Fragment } = wp.element;
const { InspectorControls, MediaUpload } = wp.blockEditor;
const { PanelBody, ToggleControl, Button } = wp.components;

import classnames from 'classnames';

// Enable custom attributes on Cover block.
const enableSidebarSelectOnBlocks = ['core/cover'];

/**
 * Declare our custom attribute
 */
const setCoverAttributes = (settings, name) => {
    if (!enableSidebarSelectOnBlocks.includes(name)) {
        return settings;
    }

    return Object.assign({}, settings, {
        attributes: Object.assign({}, settings.attributes, {
            activateVideo: { type: 'boolean', default: false },
            videoBackground: { type: 'number' },
            videoBackgroundName: { type: 'string' },
        }),
    });
};
wp.hooks.addFilter(
    'blocks.registerBlockType',
    'core-block-customisations/set-cover-attributes',
    setCoverAttributes
);

/**
 * Add Custom Settings to Cover Sidebar
 */
const addCoverSidebar = createHigherOrderComponent((BlockEdit) => {
    return (props) => {
        if (!enableSidebarSelectOnBlocks.includes(props.name)) {
            return <BlockEdit {...props} />;
        }

        const { attributes, setAttributes } = props;
        const { activateVideo, videoBackground, videoBackgroundName } = attributes;

        return (
            <Fragment>
                <BlockEdit {...props} />
                <InspectorControls>
                    <PanelBody title={__('Video Options')}>
                        <ToggleControl
                            label={__('Use video background')}
                            checked={activateVideo}
                            onChange={() => setAttributes({ activateVideo: !activateVideo })}
                        />
                        {activateVideo && (
                            <div>
                                <MediaUpload
                                    onSelect={(media) => setAttributes({ videoBackground: media.id, videoBackgroundName: media.title })}
                                    allowedTypes={['video']}
                                    value={videoBackground}
                                    render={({ open }) => (
                                        <Button isSecondary onClick={open}>
                                            {videoBackgroundName ? __('Change video') : __('Select video')}
                                        </Button>
                                    )}
                                />
                                {videoBackgroundName && <p>{`${__('Selected:')} ${videoBackgroundName}`}</p>}
                            </div>
                        )}
                    </PanelBody>
                </InspectorControls>
            </Fragment>
        );
    };
}, 'addCoverSidebar');
wp.hooks.addFilter(
    'editor.BlockEdit',
    'core-block-customisations/add-cover-sidebar',
    addCoverSidebar
);

/**
 * Save our custom attribute
 */
const saveCoverAttributes = (extraProps, blockType, attributes) => {
    if (enableSidebarSelectOnBlocks.includes(blockType.name)) {
        const { activateVideo } = attributes;

        if (activateVideo) {
            extraProps.className = classnames(
                extraProps.className,
                'has-video-background'
            );
        }
    }
    return extraProps;
};
wp.hooks.addFilter(
    'blocks.getSaveContent.extraProps',
    'core-block-customisations/save-cover-attributes',
    saveCoverAttributes
);
