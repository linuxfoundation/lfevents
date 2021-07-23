/**
 * Utils
 *
 * @package
 * @since 1.0.0
 */

// Range function.
export function range( start, end ) {
	return Array( end - start + 1 )
		.fill()
		.map( ( _, idx ) => start + idx );
}
