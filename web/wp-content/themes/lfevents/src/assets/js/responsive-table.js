/**
 * Making tables responsive.
 *
 * @package WordPress
 */

$( document ).ready(
	function () {
		$( '.is-style-table-with-column-headers table' ).ReStable(
			{
				rowHeaders: true, // Table has row headers.
				maxWidth: 999, // Size to which the table become responsive.
				keepHtml: true // Keep the html content of cells.
			}
		);
	}
);
