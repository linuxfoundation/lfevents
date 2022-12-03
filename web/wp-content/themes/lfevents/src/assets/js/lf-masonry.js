/**
 * Applies masonry layout following this approach:
 * https://medium.com/@andybarefoot/a-masonry-style-layout-using-css-grid-8c663d355ebb
 *
 * @package WordPress
 */

/**
 * Resizes grid item.
 *
 * @param item grid item.
 */
function resizeGridItem(item){
	var grid = document.getElementsByClassName( "grid" )[0];
	var rowHeight = parseInt( window.getComputedStyle( grid ).getPropertyValue( 'grid-auto-rows' ) );
	var rowGap = parseInt( window.getComputedStyle( grid ).getPropertyValue( 'grid-row-gap' ) );
	var rowSpan = Math.ceil( (item.querySelector( '.content' ).getBoundingClientRect().height + rowGap + 32) / (rowHeight + rowGap) );
	item.style.gridRowEnd = "span " + rowSpan;
}

function resizeAllGridItems(){
	var x, allItems;
	allItems = document.getElementsByClassName( "item" );
	var len = allItems.length;
	for (x = 0;x < len;x++) {
		resizeGridItem( allItems[x] );
	}
}

$( document ).ready(
	function () {
		var x, allItems;
		window.onload = resizeAllGridItems();
		window.addEventListener( "resize", resizeAllGridItems );
	}
);
