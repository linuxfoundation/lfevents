import { useBlockProps, InspectorControls } from '@wordpress/block-editor';
import { PanelBody, TextControl, ToggleControl, SelectControl, Placeholder } from '@wordpress/components';

export default function Edit( { attributes, setAttributes } ) {
	const { publicSlug, primaryFilterTitle, timeFormat, enableGridView, enablePersonalAgenda } = attributes;
	const blockProps = useBlockProps();

	return (
		<div { ...blockProps }>
			<InspectorControls>
				<PanelBody title="Sessionize Configuration" initialOpen={ true }>
					<TextControl
						label="Public Slug"
						value={ publicSlug }
						onChange={ ( val ) => setAttributes( { publicSlug: val } ) }
						help="e.g., kubecon-cloudnativecon-europe-2024"
					/>
					<TextControl
						label="Primary Filter Title"
						value={ primaryFilterTitle }
						onChange={ ( val ) => setAttributes( { primaryFilterTitle: val } ) }
					/>
					<SelectControl
						label="Time Format"
						value={ timeFormat }
						options={ [
							{ label: '12-Hour (AM/PM)', value: '12h' },
							{ label: '24-Hour', value: '24h' },
						] }
						onChange={ ( val ) => setAttributes( { timeFormat: val } ) }
					/>
					<ToggleControl
						label="Enable Grid View"
						checked={ enableGridView }
						onChange={ ( val ) => setAttributes( { enableGridView: val } ) }
					/>
					<ToggleControl
						label="Enable Personal Agenda"
						checked={ enablePersonalAgenda }
						onChange={ ( val ) => setAttributes( { enablePersonalAgenda: val } ) }
					/>
				</PanelBody>
			</InspectorControls>

			<Placeholder
				icon="calendar-alt"
				label="Sessionize Schedule Block"
				instructions={ `Configured for slug: ${ publicSlug }. Configure further settings in the block sidebar.` }
			/>
		</div>
	);
}