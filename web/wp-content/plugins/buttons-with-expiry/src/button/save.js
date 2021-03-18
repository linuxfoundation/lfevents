/* eslint-disable no-restricted-syntax */
/**
 * External dependencies
 */
import classnames from 'classnames';
import {
	RichText,
	useBlockProps,
	getColorClassName,
} from '@wordpress/block-editor';

export default function save( { attributes, className } ) {
	const {
		url,
		text,
		linkTarget,
		rel,
		borderRadius,
		title,
		size,
		backgroundColor,
		customBackgroundColor,
		customTextColor,
		customGradient,
		gradient,
		textColor,
	} = attributes;

	const textClass = getColorClassName( 'color', textColor );

	const backgroundClass =
		! customGradient &&
		getColorClassName( 'background-color', backgroundColor );

	const gradientClass = getColorClassName( 'gradient-background', gradient );
	const buttonSize = [ size || 'button-large' ];

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
		className,
	} );
	const buttonStyle = {
		background: customGradient ? customGradient : undefined,
		backgroundColor:
			backgroundClass || customGradient || gradient
				? undefined
				: customBackgroundColor,
		color: textClass ? undefined : customTextColor,
		borderRadius: borderRadius ? borderRadius + 'px' : undefined,
	};

	return (
		<div { ...useBlockProps.save() }>
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
