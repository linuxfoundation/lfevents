/* eslint-disable no-restricted-syntax */
/**
 * External dependencies
 */
import classnames from 'classnames';

/**
 * WordPress dependencies
 */
import {
	RichText,
	getColorClassName,
} from '@wordpress/block-editor';

export default function save( { attributes } ) {
	const {
		backgroundColor,
		borderRadius,
		customBackgroundColor,
		customTextColor,
		customGradient,
		linkTarget,
		gradient,
		rel,
		text,
		textColor,
		title,
		url,
		size,
	} = attributes;

	const textClass = getColorClassName( 'color', textColor );
	const backgroundClass =
		! customGradient &&
		getColorClassName( 'background-color', backgroundColor );

	const gradientClass = getColorClassName( 'gradient-background', gradient );
	const buttonSize = [
		size || 'button-medium',
	];

	const buttonClasses = classnames( 'wp-block-button__link', {
		'has-text-color': textColor || customTextColor,
		[ textClass ]: textClass,
		'has-background':
			backgroundColor ||
			customBackgroundColor ||
			customGradient ||
			gradient,
		[ backgroundClass ]: backgroundClass,
		[ gradientClass ]: gradientClass,
		'no-border-radius': borderRadius === 0,
		[ buttonSize ]: true,
	} );

	const buttonStyle = {
		background: customGradient ? customGradient : undefined,
		backgroundColor:
			backgroundClass || customGradient || gradient ?
				undefined :
				customBackgroundColor,
		color: textClass ? undefined : customTextColor,
		borderRadius: borderRadius ? borderRadius + 'px' : undefined,
	};

	// The use of a `title` attribute here is soft-deprecated, but still applied
	// if it had already been assigned, for the sake of backward-compatibility.
	// A title will no longer be assigned for new or updated button block links.

	return (
		<div>
			<RichText.Content
				tagName="a"
				className={ buttonClasses }
				href={ url }
				title={ title }
				style={ buttonStyle }
				value={ text }
				target={ linkTarget }
				rel={ rel }
			/>
		</div>
	);
}
