import { useBlockProps } from '@wordpress/block-editor';
import { Button, Notice, Spinner, TextControl } from '@wordpress/components';
import { useState } from '@wordpress/element';
import { __, sprintf } from '@wordpress/i18n';
import apiFetch from '@wordpress/api-fetch';

import './editor.scss';

const ImportReport = ( { report } ) => {
	if ( ! report ) {
		return null;
	}

	const skipped = Array.isArray( report.skipped ) ? report.skipped : [];
	const warnings = Array.isArray( report.warnings ) ? report.warnings : [];

	return (
		<div className="sponsor-directory-editor__report">
			<p>
				<strong>
					{ sprintf(
						/* translators: 1: imported sponsor count, 2: skipped row count. */
						__(
							'%1$d imported; %2$d skipped',
							'sponsor-directory-block'
						),
						report.matchedCount || 0,
						report.skippedCount || 0
					) }
				</strong>
			</p>
			{ skipped.length > 0 && (
				<div>
					<strong>
						{ __( 'Skipped rows', 'sponsor-directory-block' ) }
					</strong>
					<ul>
						{ skipped.map( ( item, index ) => (
							<li key={ `${ item.row }-${ index }` }>
								{ sprintf(
									/* translators: 1: CSV row number, 2: reason. */
									__(
										'Row %1$d: %2$s',
										'sponsor-directory-block'
									),
									item.row,
									item.reason
								) }
							</li>
						) ) }
					</ul>
				</div>
			) }
			{ warnings.length > 0 && (
				<div>
					<strong>
						{ __( 'Warnings', 'sponsor-directory-block' ) }
					</strong>
					<ul>
						{ warnings.map( ( item, index ) => (
							<li key={ `${ item.row }-${ index }` }>
								{ sprintf(
									/* translators: 1: CSV row number, 2: reason. */
									__(
										'Row %1$d: %2$s',
										'sponsor-directory-block'
									),
									item.row,
									item.reason
								) }
							</li>
						) ) }
					</ul>
				</div>
			) }
		</div>
	);
};

export default function Edit( { attributes, setAttributes } ) {
	const { csvUrl, sponsors, importedAt, importReport } = attributes;
	const [ url, setUrl ] = useState( csvUrl );
	const [ isImporting, setIsImporting ] = useState( false );
	const [ error, setError ] = useState( '' );
	const [ success, setSuccess ] = useState( '' );

	const importSponsors = async () => {
		setError( '' );
		setSuccess( '' );
		setIsImporting( true );

		try {
			const response = await apiFetch( {
				path: '/sponsor-directory/v1/import',
				method: 'POST',
				data: { url: url.trim() },
			} );

			setAttributes( {
				csvUrl: url.trim(),
				sponsors: response.sponsors,
				importedAt: response.importedAt,
				importReport: response.report,
			} );
			setSuccess(
				sprintf(
					/* translators: %d: imported sponsor count. */
					__( 'Imported %d sponsors.', 'sponsor-directory-block' ),
					response.report.matchedCount
				)
			);
		} catch ( importError ) {
			setError(
				importError.message ||
					__(
						'The sponsor import failed.',
						'sponsor-directory-block'
					)
			);
		} finally {
			setIsImporting( false );
		}
	};

	return (
		<div { ...useBlockProps( { className: 'sponsor-directory-editor' } ) }>
			<h2>{ __( 'Sponsor Directory', 'sponsor-directory-block' ) }</h2>
			<p>
				{ __(
					'Publish the Google Sheet as CSV, enter its URL, and import a read-only snapshot.',
					'sponsor-directory-block'
				) }
			</p>
			<TextControl
				label={ __(
					'Published Google Sheet CSV URL',
					'sponsor-directory-block'
				) }
				value={ url }
				onChange={ setUrl }
				type="url"
				placeholder="https://docs.google.com/spreadsheets/d/.../export?format=csv"
				disabled={ isImporting }
			/>
			<div className="sponsor-directory-editor__actions">
				<Button
					variant="primary"
					onClick={ importSponsors }
					disabled={ isImporting || ! url.trim() }
				>
					{ sponsors.length > 0
						? __( 'Import again', 'sponsor-directory-block' )
						: __( 'Import sponsors', 'sponsor-directory-block' ) }
				</Button>
				{ isImporting && <Spinner /> }
			</div>
			{ error && (
				<Notice
					status="error"
					isDismissible
					onRemove={ () => setError( '' ) }
				>
					{ error }
				</Notice>
			) }
			{ success && (
				<Notice
					status="success"
					isDismissible
					onRemove={ () => setSuccess( '' ) }
				>
					{ success }
				</Notice>
			) }
			{ sponsors.length === 0 ? (
				<p className="sponsor-directory-editor__empty">
					{ __(
						'No sponsors have been imported.',
						'sponsor-directory-block'
					) }
				</p>
			) : (
				<>
					<p className="sponsor-directory-editor__source">
						{ sprintf(
							/* translators: %s: import date and time. */
							__(
								'Last imported: %s',
								'sponsor-directory-block'
							),
							importedAt
								? new Date( importedAt ).toLocaleString()
								: ''
						) }
					</p>
					<ImportReport report={ importReport } />
					<div className="sponsor-directory-editor__preview">
						<table>
							<thead>
								<tr>
									<th>
										{ __(
											'Sponsor',
											'sponsor-directory-block'
										) }
									</th>
									<th>
										{ __(
											'Post ID',
											'sponsor-directory-block'
										) }
									</th>
									<th>
										{ __(
											'Level',
											'sponsor-directory-block'
										) }
									</th>
								</tr>
							</thead>
							<tbody>
								{ sponsors.map( ( sponsor ) => (
									<tr key={ sponsor.postId }>
										<td>{ sponsor.name }</td>
										<td>{ sponsor.postId }</td>
										<td>{ sponsor.level || '—' }</td>
									</tr>
								) ) }
							</tbody>
						</table>
					</div>
				</>
			) }
		</div>
	);
}
