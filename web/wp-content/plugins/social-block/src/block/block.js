/**
 * Register block JS
 *
 * @package WordPress
 * @since 1.0.0
 *
 * @tags
 * @phpcs:disable WordPress.WhiteSpace.OperatorSpacing.NoSpaceAfter
 * @phpcs:disable WordPress.WhiteSpace.OperatorSpacing.NoSpaceBefore
 * @phpcs:disable Generic.WhiteSpace.ScopeIndent.IncorrectExact
 * @phpcs:disable Generic.WhiteSpace.ScopeIndent.Incorrect
 * @phpcs:disable PEAR.Functions.FunctionCallSignature.Indent
 */

// Import CSS.
import './style.scss';
import './editor.scss';

import Edit from './edit.js';

const { __ } = wp.i18n;
const { registerBlockType } = wp.blocks;

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
registerBlockType(
	 'lf/social-block',
	{
	title: __( 'Social Block' ),
	description: __( 'Block which inserts the social media icons for an event. Uses Social and Design settings from the Event parent.' ),
	icon: 'twitter',
	category: 'common',
	keywords: [
		__( 'social' ),
		__( 'facebook' ),
		__( 'twitter' ),
		__( 'lf' ),
		__( 'linux' ),
		__( 'icons' ),
		__( 'follow' ),
	],
	supports: {
			align: [ 'left', 'right', 'center' ],
			html: false,
	},
	example: {},
	attributes: {
			align: {
				type: 'string',
				default: 'full',
			},
			iconColor: {
							type: 'string',
							default: '#000000',
				},
				menu_color_1: {
										type: 'string',
										source: 'meta',
										meta: 'lfes_menu_color',
										default: '#000000',
						},
						menu_color_2: {
											type: 'string',
											source: 'meta',
											meta: 'lfes_menu_color_2',
											default: '#000000',
							},
							menu_color_3: {
													type: 'string',
													source: 'meta',
													meta: 'lfes_menu_color_3',
													default: '#000000',
								},
								wechat_url: {
																type: 'string',
																source: 'meta',
																meta: 'lfes_wechat',
																default: '',
										},
										linkedin_url: {
																		type: 'string',
																		source: 'meta',
																		meta: 'lfes_linkedin',
										},
										qq_url: {
																		type: 'string',
																		source: 'meta',
																		meta: 'lfes_qq',
										},
										youtube_url: {
																		type: 'string',
																		source: 'meta',
																		meta: 'lfes_youtube',
										},
										facebook_url: {
																		type: 'string',
																		source: 'meta',
																		meta: 'lfes_facebook',
										},
										twitter_url: {
																		type: 'string',
																		source: 'meta',
																		meta: 'lfes_twitter',
										},
										instagram_url: {
																		type: 'string',
																		source: 'meta',
																		meta: 'lfes_instagram',
										},
									},
									edit: Edit,
									save() {
																				return null;
									},
								}
							);
