=== Post Meta Controls ===
Contributors: melonpan
Tags: gutenberg, meta, post-meta, settings, controls
Requires at least: 5.1
Tested up to: 5.2
Stable tag: 1.2.0-rc1
Requires PHP: 7.1
License: GPLv3
License URI: https://www.gnu.org/licenses/gpl-3.0.html

Utilities to register, save and modify post meta data in the Gutenberg editor.

== Description ==

Register, Save, Modify and Get meta data in the Gutenberg editor.
Use this plugin to add meta data controls inside a sidebar in the editor of posts, pages or custom post types.
This is the list of controls available:

* Buttons
* Checkbox, Checkbox Multiple
* Color, Color with Alpha
* Custom text
* Date Range, Date Single
* Image, Image Multiple
* Radio
* Range, Range with Float number
* Select
* Text, Textarea

The plugin comes with different options to customize the Sidebars, Tabs, Panels and Setting controls.

== Usage ==

Once the plugin is installed, you will need to include the plugin filter inside your plugin or theme to create a sidebar with it's settings.
The new sidebar/s can be accessed in any post type where it was registered.
Modify the setting values with the controls inside the sidebar.
Use the plugin helpers (see *Helpers to get the meta values* section) to get the meta data in the front end.

== Installation ==

Installation from the WordPress admin.

1. Log in to the WordPress admin and navigate to *Plugins > Add New*.
2. Type *Post Meta Controls* in the Search field.
3. In the results list *Post Meta Controls* plugin should appear, click **Install Now** button.
4. Once it finished installing, click the *Activate* button.
5. Now you can register your sidebar and settings using the filter in your plugin or theme.
6. To view your sidebar go to any post where Gutenberg is enabled and the sidebar was registered to.

== Frequently Asked Questions ==

= Notes about using Meta data and the Blocks editor in your CPT =

To make use of meta in your Custom Post Type remember that **custom-fields** needs to be set in the **includes** property of the [register_post_type function](https://codex.wordpress.org/Function_Reference/register_post_type).
Also, to activate the Gutenberg/Block editor in the post, **editor** needs to be set in the same property (*includes*).

= Registering a sidebar with post meta controls =

**Notes**
This is an example of how to add a sidebar inside the editor.
In this case it will display a sidebar with one tab, one panel and two settings inside it: **buttons** and **checkbox**.
The sidebar will appear in every:

* **post**
* **my_custom_post_type**

These two settings are of **data_type** *meta* so their value will be saved to the post meta.
In the other sections of this readme you can check all the available options for each element.

**Steps**
Inside a php file in your plugin or the functions.php file of your theme call the filter in the following way (make sure *Post Meta Controls* plugin is active):

	function myplugin_create_sidebar( $sidebars ) {

		// First we define the sidebar with it's tabs, panels and settings.
		$sidebar = array(
			'id'              => 'mysidebar',
			'id_prefix'       => 'myidprefix_',
			'label'           => __( 'Sidebar label', 'my_plugin' ),
			'post_type'       => array( 'post', 'my_custom_post_type' ),
			'data_key_prefix' => 'mydataprefix_',
			'icon_dashicon'   => 'carrot',
			'tabs'            => array(
				array(
					'label'  => __( 'Tab label', 'my_plugin' ),
					'panels' => array(
						array(
							'label'    => __( 'Panel label', 'my_plugin' ),
							'settings' => array(
								// Buttons setting.
								array(
									'type'          => 'buttons',
									'data_type'     => 'meta',
									'data_key'      => 'buttons_key',
									'label'         => __( 'Setting label', 'my_plugin' ),
									'help'          => __( 'Setting description', 'my_plugin' ),
									'default_value' => 'bbb',
									'options'       => array(
										array(
											'title'         => __( 'Option title aaa', 'my_plugin' ),
											'value'         => 'aaa',
											'icon_dashicon' => 'carrot',
										),
										array(
											'title'         => __( 'Option title bbb', 'my_plugin' ),
											'value'         => 'bbb',
											'icon_dashicon' => 'sos',
										),
									),
								),
								// Checkbox setting.
								array(
									'type'          => 'checkbox',
									'data_type'     => 'meta',
									'data_key'      => 'checkbox_key',
									'label'         => __( 'Setting label', 'my_plugin' ),
									'help'          => __( 'Setting description', 'my_plugin' ),
									'default_value' => false,
									'use_toggle'    => true,
									'input_label'   => __( 'Input label', 'my_plugin' ),
								),
							),
						),
					),
				),
			),
		);

		// Push the $sidebar we just assigned to the variable
		// to the array of $sidebars that comes in the function argument.
		$sidebars[] = $sidebar;

		// Return the $sidebars array with our sidebar now included.
		return $sidebars;

	}

	add_filter( 'pmc_create_sidebar', 'myplugin_create_sidebar' );

= Helpers to get the meta values =

The following are functions to get the meta values in the front end. These are simple helpers to get the data in a clean way using the WordPress function *get_post_meta()*.

This is the list of arguments the different functions use:

 * **$meta_key** *(string)* Required. Name of the key the setting was registered with. Remember to include the prefix: *myprefix_mymetakey*
 * **$post_id** *(integer)* Post id to get the value from. If an empty string is passed **get_the_ID()** will be used.
 * **$default_value** *(string|integer|float|boolean|array)* Custom value to return in case the meta key doesn't exist yet.
 * **$size** *(string)* Used in the image settings to return this image sizes. Any registered size, default ones are *thumbnail*, *medium*, *large*, *full*.
 * **$return_array** *(boolean)* Used in the image settings. Pass false to return the image id/s instead of its properties.
 * **$return_string** *(boolean)* Used in the color setting to return a color string or an array of color and alpha.

List of functions:

	// Setting - Buttons. Returns a string with the selected option;
	// or the $default_value passed (false) if the meta key doesn't exist.
	pmc_get_buttons( $meta_key, $post_id, $default_value );

	// Setting - Checkbox. Returns true or false;
	// or the $default_value passed (an empty string '') if the meta key doesn't exist.
	pmc_get_checkbox( $meta_key, $post_id, $default_value );

	// Setting - Checkbox Multiple. Returns an array of strings with the selected options;
	// or the $default_value passed (false) if the meta key doesn't exist.
	pmc_get_checkbox_multiple( $meta_key, $post_id, $default_value );

	// Setting - Color. Returns a color string or an array:
	// array( 'color' => 'rgb(0,0,0)', 'alpha' => 50 );
	// or the $default_value passed (false) if the meta key doesn't exist.
	pmc_get_color( $meta_key, $post_id, $default_value, $return_string );

	// Setting - Date Range. Returns an array of two strings: start date and end date;
	// or the $default_value passed (false) if the meta key doesn't exist.
	pmc_get_date_range( $meta_key, $post_id, $default_value );

	// Setting - Date Single. Returns a string;
	// or the $default_value passed (false) if the meta key doesn't exist.
	pmc_get_date_single( $meta_key, $post_id, $default_value );

	// Setting - Image. Returns an integer which is the image id or an array with the image properties:
	// array( 'url' => '#', 'width' => 123, 'height' => 456 );
	// or the $default_value passed (false) if the meta key doesn't exist.
	pmc_get_image( $meta_key, $post_id, $default_value, $size, $return_array );

	// Setting - Image Multiple. Returns an array of integers which are the images id
	// or an array of arrays with the images properties:
	// array( '123' => array( 'url' => '#', 'width' => 123, 'height' => 456 ) );
	// or the $default_value passed (false) if the meta key doesn't exist.
	pmc_get_image_multiple( $meta_key, $post_id, $default_value, $size, $return_array );

	// Setting - Radio. Returns a string with the selected option;
	// or the $default_value passed (false) if the meta key doesn't exist.
	pmc_get_radio( $meta_key, $post_id, $default_value );

	// Setting - Range. Returns an integer; or the $default_value passed (false) if the meta key doesn't exist.
	pmc_get_range( $meta_key, $post_id, $default_value );

	// Setting - Range Float. Returns a float; or the $default_value passed (false) if the meta key doesn't exist.
	pmc_get_range_float( $meta_key, $post_id, $default_value );

	// Setting - Select. Returns a string with the selected option;
	// or the $default_value passed (false) if the meta key doesn't exist.
	pmc_get_select( $meta_key, $post_id, $default_value );

	// Setting - Text. Returns a string;
	// or the $default_value passed (false) if the meta key doesn't exist.
	pmc_get_text( $meta_key, $post_id, $default_value );

	// Setting - Textarea. Returns a string;
	// or the $default_value passed (false) if the meta key doesn't exist.
	pmc_get_textarea( $meta_key, $post_id, $default_value );

= What are the different data_type available? =

Every setting has these available data_type values:

 * **meta**: This value will register and save the setting **data_key** in the post meta. If **register_meta** is *true* (which is by default) the plugin will register the meta. Modifying the value in the editor using the setting control and saving the post will update it's meta key value.
 * **localstorage**: This value is saved only in the current browser but shared among all the domain. It can be used to set some setting regarding the actual users browser that doesnt save any data in the database.
 * **none**: This is the default value. This value will not save the setting data. The value is saved in the editor, in the plugin redux store. But modified values will be lost when the page reloads.

= Sidebar =

The following are all available options for a **Sidebar**. Sidebars contain Tabs.
Several sidebars can be added, just make sure each has a unique id.

	$sidebar = array(
		'id'              => 'sidebar_id', // Required. It has to be unique.
		// Use 'id_prefix' to set a custom prefix for this sidebars elements 'id'.
		'id_prefix'       => '',
		'label'           => __( 'Sidebar label', 'my_plugin' ), // Required.
		// Post types where this sidebar will be assigned and where each
		// setting meta will be registered (if the setting 'data_type' is 'meta').
		'post_type'       => array( 'post' ),
		// Use 'data_key_prefix' to set a custom prefix for this sidebars settings 'data_key'.
		// If 'data_key_prefix' is not assigned, the default 'pmc_' will be used.
		'data_key_prefix' => 'pmc_',
		// Either 'icon_svg' or 'icon_dashicon' can be set.
		// 'icon_svg' takes preference over 'icon_dashicon'.
		'icon_svg'        => // svg string.
			'<svg
				width="18"
				height="18"
				viewBox="0 0 24 24"
				class="my_plugin-svg"
			>
				<polygon points="10,2 22,2 22,14 10,14" />
				<polygon points="4,8 16,8 16,20 4,20" />
			</svg>',
		// Name of a dashicon. To see the list of available icons
		// check: https://developer.wordpress.org/resource/dashicons
		'icon_dashicon'   => 'carrot',
		'tabs'            => array( // Required. Array of tabs to include in this sidebar.
			$my_tab,
			$my_other_tab,
		),
	);

= Tab =

The following are all available options for a **Tab**. Tabs contain Panels.

	$tab = array(
		'label'         => __( 'Tab label', 'my_plugin' ), // Required.
		// Optionally, an id may be specified. It will be used by the plugin to
		// identify the tab and will be applied to the tab html.
		'id'            => 'tab_id',
		// Either 'icon_svg' or 'icon_dashicon' can be set. Both are optional.
		// 'icon_svg' takes preference over 'icon_dashicon'.
		'icon_svg'      => // svg string.
			'<svg
				width="18"
				height="18"
				viewBox="0 0 24 24"
				class="my_plugin-svg"
			>
				<polygon points="10,2 22,2 22,14 10,14" />
				<polygon points="4,8 16,8 16,20 4,20" />
			</svg>',
		// Name of a dashicon. To see the list of available icons
		// check: https://developer.wordpress.org/resource/dashicons
		'icon_dashicon' => 'carrot',
		'panels'        => array( // Required. Array of panels to include in this tab.
			$my_panel,
			$my_other_panel,
		),
	);

= Panel =

The following are all available options for a **Panel**. Panels contain Settings.

	$panel = array(
		'label'         => __( 'Panel label', 'my_plugin' ), // Required if 'collapsible' is true.
		// Optionally, an id may be specified. It will be used by the plugin to
		// identify the panel and will be applied to the panel html.
		'id'            => 'panel_id',
		'collapsible'   => true, // If true the panel will collapse and expand on click.
		'initial_open'  => true, // This option is applicable only if 'collapsible' is true.
		// Either 'icon_svg' or 'icon_dashicon' can be set. Both are optional.
		// 'icon_svg' takes preference over 'icon_dashicon'.
		'icon_svg'      => // svg string.
			'<svg
				width="18"
				height="18"
				viewBox="0 0 24 24"
				class="my_plugin-svg"
			>
				<polygon points="10,2 22,2 22,14 10,14" />
				<polygon points="4,8 16,8 16,20 4,20" />
			</svg>',
		// Name of a dashicon. To see the list of available icons
		// check: https://developer.wordpress.org/resource/dashicons
		'icon_dashicon' => 'carrot',
		'settings'      => array( // Required. Array of settings to include in this panel.
			$my_setting_checkbox,
			$my_setting_radio,
		),
	);

= Setting - Buttons =

The following are all available options for a **Setting - Buttons**.

	$buttons = array(
		'type'            => 'buttons', // Required.
		// Optionally, an id may be specified. It will be used by the plugin to
		// identify the setting and will be applied to the control html.
		// The prefix set in the sidebar option 'id_prefix' will be applied.
		'id'              => 'buttons_id',
		'data_type'       => 'none', // Available: 'meta', 'localstorage', 'none'.
		'data_key'        => 'buttons_key', // Required if 'data_type' is 'meta' or 'localstorage'.
		// Use 'data_key_prefix' to set a custom prefix for this setting 'data_key'.
		// If 'data_key_prefix' is not assigned, the 'data_key_prefix' from the sidebar
		// where this setting is nested will be used.
		'data_key_prefix' => 'pmc_',
		'label'           => __( 'Setting label', 'my_plugin' ),
		'help'            => __( 'Setting description', 'my_plugin' ),
		'register_meta'   => true, // This option is applicable only if 'data_type' is 'meta'.
		'ui_border_top'   => true, // Display CSS border-top in the editor control.
		// Setting buttons specific properties:
		'default_value'   => 'aaa', // The value of one of the 'options'.
		'allow_empty'     => true,
		'options'         => array( // Required.
			array(
				'title'    => __( 'Option title aaa', 'my_plugin' ), // Required.
				'value'    => 'aaa', // Required.
				// Either 'icon_svg' or 'icon_dashicon' can be set.
				// 'icon_svg' takes preference over 'icon_dashicon'.
				// See the next option for an example of 'icon_dashicon'.
				'icon_svg' => // svg string.
					'<svg
						width="18"
						height="18"
						viewBox="0 0 24 24"
						class="my_plugin-svg"
					>
						<polygon points="10,2 22,2 22,14 10,14" />
						<polygon points="4,8 16,8 16,20 4,20" />
					</svg>',
			),
			array(
				'title'         => __( 'Option title bbb', 'my_plugin' ), // Required.
				'value'         => 'bbb', // Required.
				// Name of a dashicon. To see the list of available icons
				// check: https://developer.wordpress.org/resource/dashicons
				'icon_dashicon' => 'carrot',
			),
		),
	);

= Setting - Checkbox =

The following are all available options for a **Setting - Checkbox**.

	$checkbox = array(
		'type'            => 'checkbox', // Required.
		// Optionally, an id may be specified. It will be used by the plugin to
		// identify the setting and will be applied to the control html.
		// The prefix set in the sidebar option 'id_prefix' will be applied.
		'id'              => 'checkbox_id',
		'data_type'       => 'none', // Available: 'meta', 'localstorage', 'none'.
		'data_key'        => 'checkbox_key', // Required if 'data_type' is 'meta' or 'localstorage'.
		// Use 'data_key_prefix' to set a custom prefix for this setting 'data_key'.
		// If 'data_key_prefix' is not assigned, the 'data_key_prefix' from the sidebar
		// where this setting is nested will be used.
		'data_key_prefix' => 'pmc_',
		'label'           => __( 'Setting label', 'my_plugin' ),
		'help'            => __( 'Setting description', 'my_plugin' ),
		'register_meta'   => true, // This option is applicable only if 'data_type' is 'meta'.
		'ui_border_top'   => true, // Display CSS border-top in the editor control.
		// Setting checkbox specific options:
		'default_value'   => false,
		'use_toggle'      => false, // Use toggle control instead of checkbox.
		'input_label'     => __( 'Input label', 'my_plugin' ), // Required.
	);

= Setting - Checkbox Multiple =

The following are all available options for a **Setting - Checkbox Multiple**.

	$checkbox_multiple = array(
		'type'            => 'checkbox_multiple', // Required.
		// Optionally, an id may be specified. It will be used by the plugin to
		// identify the setting and will be applied to the control html.
		// The prefix set in the sidebar option 'id_prefix' will be applied.
		'id'              => 'checkbox_multiple_id',
		'data_type'       => 'none', // Available: 'meta', 'localstorage', 'none'.
		'data_key'        => 'checkbox_multiple_key', // Required if 'data_type' is 'meta' or 'localstorage'.
		// Use 'data_key_prefix' to set a custom prefix for this setting 'data_key'.
		// If 'data_key_prefix' is not assigned, the 'data_key_prefix' from the sidebar
		// where this setting is nested will be used.
		'data_key_prefix' => 'pmc_',
		'label'           => __( 'Setting label', 'my_plugin' ),
		'help'            => __( 'Setting description', 'my_plugin' ),
		'register_meta'   => true, // This option is applicable only if 'data_type' is 'meta'.
		'ui_border_top'   => true, // Display CSS border-top in the editor control.
		// Setting checkbox_multiple specific options:
		'default_value'   => array( 'aaa', 'ccc' ), // Value/s from the 'options'.
		'use_toggle'      => false, // Use toggle control instead of checkbox.
		'options'         => array( // Required.
			'aaa' => __( 'aaa Option', 'my_plugin' ),
			'bbb' => __( 'bbb Option', 'my_plugin' ),
			'ccc' => __( 'ccc Option', 'my_plugin' ),
		),
	);

= Setting - Color =

The following are all available options for a **Setting - Color**.

	$color = array(
		'type'            => 'color', // Required.
		// Optionally, an id may be specified. It will be used by the plugin to
		// identify the setting and will be applied to the control html.
		// The prefix set in the sidebar option 'id_prefix' will be applied.
		'id'              => 'color_id',
		'data_type'       => 'none', // Available: 'meta', 'localstorage', 'none'.
		'data_key'        => 'color_key', // Required if 'data_type' is 'meta' or 'localstorage'.
		// Use 'data_key_prefix' to set a custom prefix for this setting 'data_key'.
		// If 'data_key_prefix' is not assigned, the 'data_key_prefix' from the sidebar
		// where this setting is nested will be used.
		'data_key_prefix' => 'pmc_',
		'label'           => __( 'Setting label', 'my_plugin' ),
		'help'            => __( 'Setting description', 'my_plugin' ),
		'register_meta'   => true, // This option is applicable only if 'data_type' is 'meta'.
		'ui_border_top'   => true, // Display CSS border-top in the editor control.
		// Setting color specific options:
		'default_value'   => '', // A string with a HEX, rgb or rgba color format.
		'alpha_control'   => false, // Include alpha control to set color transparency.
		'palette'         => array(
			'red'   => '#ff0000',
			'green' => '#00ff00',
		),
	);

= Setting - Custom Text =

The following are all available options for a **Setting - Custom Text**.
This control doesn't save data. Use it to show text in the sidebar, like a description or instructions.

	$custom_text = array(
		'type'          => 'custom_text', // Required.
		// Optionally, an id may be specified. It will be used by the plugin to
		// identify the setting and will be applied to the control html.
		// The prefix set in the sidebar option 'id_prefix' will be applied.
		'id'            => 'custom_text_id',
		'label'         => __( 'Setting label', 'my_plugin' ),
		'help'          => __( 'Setting description', 'my_plugin' ),
		'ui_border_top' => true, // Display CSS border-top in the editor control.
		// Setting custom_text specific options:
		'content'       => array(
			array(
				'type'    => 'title', // Required.
				'content' => 'This is a title tag', // Required.
			),
			array(
				'type'    => 'link', // Required.
				'content' => 'This is a link', // Required.
				'href'    => 'https://example.com', // Required.
			),
			array(
				'type'    => 'paragraph', // Required.
				'content' => 'Some text inside a paragraph.', // Required.
			),
			array(
				'type'    => 'ordered_list', // Required.
				'content' => array( // Required.
					'First element',
					'Second element',
					'Third element',
				),
			),
			array(
				'type'    => 'unordered_list', // Required.
				'content' => array( // Required.
					'First element',
					'Second element',
					'Third element',
				),
			),
		),
	);

= Setting - Date Range =

The following are all available options for a **Setting - Date Range**.

	$date_range = array(
		'type'            => 'date_range', // Required.
		// Optionally, an id may be specified. It will be used by the plugin to
		// identify the setting and will be applied to the control html.
		// The prefix set in the sidebar option 'id_prefix' will be applied.
		'id'              => 'date_range_id',
		'data_type'       => 'none', // Available: 'meta', 'localstorage', 'none'.
		'data_key'        => 'date_range_key', // Required if 'data_type' is 'meta' or 'localstorage'.
		// Use 'data_key_prefix' to set a custom prefix for this setting 'data_key'.
		// If 'data_key_prefix' is not assigned, the 'data_key_prefix' from the sidebar
		// where this setting is nested will be used.
		'data_key_prefix' => 'pmc_',
		'label'           => __( 'Setting label', 'my_plugin' ),
		'help'            => __( 'Setting description', 'my_plugin' ),
		'register_meta'   => true, // This option is applicable only if 'data_type' is 'meta'.
		'ui_border_top'   => true, // Display CSS border-top in the editor control.
		// Setting date_range specific options:
		'default_value'   => '', // A string with a date that matches 'format'.
		// To see the available formats
		// check: http://momentjs.com/docs/#/parsing/string-format/
		'format'          => 'DD/MM/YYYY',
		// A string with the locale value.
		// For example 'en' for english, or 'ja' for japanese.
		// To see the available locales check https://momentjs.com/
		'locale'          => 'en',
	);

= Setting - Date Single =

The following are all available options for a **Setting - Date Single**.

	$date_single = array(
		'type'            => 'date_single', // Required.
		// Optionally, an id may be specified. It will be used by the plugin to
		// identify the setting and will be applied to the control html.
		// The prefix set in the sidebar option 'id_prefix' will be applied.
		'id'              => 'date_single_id',
		'data_type'       => 'none', // Available: 'meta', 'localstorage', 'none'.
		'data_key'        => 'date_single_key', // Required if 'data_type' is 'meta' or 'localstorage'.
		// Use 'data_key_prefix' to set a custom prefix for this setting 'data_key'.
		// If 'data_key_prefix' is not assigned, the 'data_key_prefix' from the sidebar
		// where this setting is nested will be used.
		'data_key_prefix' => 'pmc_',
		'label'           => __( 'Setting label', 'my_plugin' ),
		'help'            => __( 'Setting description', 'my_plugin' ),
		'register_meta'   => true, // This option is applicable only if 'data_type' is 'meta'.
		'ui_border_top'   => true, // Display CSS border-top in the editor control.
		// Setting date_single specific options:
		'default_value'   => '', // A string with a date that matches 'format'.
		// To see the available formats
		// check: http://momentjs.com/docs/#/parsing/string-format/
		'format'          => 'DD/MM/YYYY',
		// A string with the locale value.
		// For example 'en' for english, or 'ja' for japanese.
		// To see the available locales check https://momentjs.com/
		'locale'          => 'en',
	);

= Setting - Image =

The following are all available options for a **Setting - Image**.

	$image = array(
		'type'            => 'image', // Required.
		// Optionally, an id may be specified. It will be used by the plugin to
		// identify the setting and will be applied to the control html.
		// The prefix set in the sidebar option 'id_prefix' will be applied.
		'id'              => 'image_id',
		'data_type'       => 'none', // Available: 'meta', 'localstorage', 'none'.
		'data_key'        => 'image_key', // Required if 'data_type' is 'meta' or 'localstorage'.
		// Use 'data_key_prefix' to set a custom prefix for this setting 'data_key'.
		// If 'data_key_prefix' is not assigned, the 'data_key_prefix' from the sidebar
		// where this setting is nested will be used.
		'data_key_prefix' => 'pmc_',
		'label'           => __( 'Setting label', 'my_plugin' ),
		'help'            => __( 'Setting description', 'my_plugin' ),
		'register_meta'   => true, // This option is applicable only if 'data_type' is 'meta'.
		'ui_border_top'   => true, // Display CSS border-top in the editor control.
		// Setting image specific options:
		'default_value'   => 123, // An integer which matches a media library image id.
	);

= Setting - Image Multiple =

The following are all available options for a **Setting - Image Multiple**.

	$image_multiple = array(
		'type'            => 'image_multiple', // Required.
		// Optionally, an id may be specified. It will be used by the plugin to
		// identify the setting and will be applied to the control html.
		// The prefix set in the sidebar option 'id_prefix' will be applied.
		'id'              => 'image_multiple_id',
		'data_type'       => 'none', // Available: 'meta', 'localstorage', 'none'.
		'data_key'        => 'image_multiple_key', // Required if 'data_type' is 'meta' or 'localstorage'.
		// Use 'data_key_prefix' to set a custom prefix for this setting 'data_key'.
		// If 'data_key_prefix' is not assigned, the 'data_key_prefix' from the sidebar
		// where this setting is nested will be used.
		'data_key_prefix' => 'pmc_',
		'label'           => __( 'Setting label', 'my_plugin' ),
		'help'            => __( 'Setting description', 'my_plugin' ),
		'register_meta'   => true, // This option is applicable only if 'data_type' is 'meta'.
		'ui_border_top'   => true, // Display CSS border-top in the editor control.
		// Setting image_multiple specific options:
		'default_value'   => array( 123, 456 ), // Array of integer which match media library images id.
	);

= Setting - Radio =

The following are all available options for a **Setting - Radio**.

	$radio = array(
		'type'            => 'radio', // Required.
		// Optionally, an id may be specified. It will be used by the plugin to
		// identify the setting and will be applied to the control html.
		// The prefix set in the sidebar option 'id_prefix' will be applied.
		'id'              => 'radio_id',
		'data_type'       => 'none', // Available: 'meta', 'localstorage', 'none'.
		'data_key'        => 'radio_key', // Required if 'data_type' is 'meta' or 'localstorage'.
		// Use 'data_key_prefix' to set a custom prefix for this setting 'data_key'.
		// If 'data_key_prefix' is not assigned, the 'data_key_prefix' from the sidebar
		// where this setting is nested will be used.
		'data_key_prefix' => 'pmc_',
		'label'           => __( 'Setting label', 'my_plugin' ),
		'help'            => __( 'Setting description', 'my_plugin' ),
		'register_meta'   => true, // This option is applicable only if 'data_type' is 'meta'.
		'ui_border_top'   => true, // Display CSS border-top in the editor control.
		// Setting radio specific options:
		'default_value'   => 'bbb', // Value from 'options'.
		'options'         => array( // Required.
			'aaa' => __( 'aaa Option', 'my_plugin' ),
			'bbb' => __( 'bbb Option', 'my_plugin' ),
			'ccc' => __( 'ccc Option', 'my_plugin' ),
		),
	);

= Setting - Range =

The following are all available options for a **Setting - Range**.

	$range = array(
		'type'            => 'range', // Required.
		// Optionally, an id may be specified. It will be used by the plugin to
		// identify the setting and will be applied to the control html.
		// The prefix set in the sidebar option 'id_prefix' will be applied.
		'id'              => 'range_id',
		'data_type'       => 'none', // Available: 'meta', 'localstorage', 'none'.
		'data_key'        => 'range_key', // Required if 'data_type' is 'meta' or 'localstorage'.
		// Use 'data_key_prefix' to set a custom prefix for this setting 'data_key'.
		// If 'data_key_prefix' is not assigned, the 'data_key_prefix' from the sidebar
		// where this setting is nested will be used.
		'data_key_prefix' => 'pmc_',
		'label'           => __( 'Setting label', 'my_plugin' ),
		'help'            => __( 'Setting description', 'my_plugin' ),
		'register_meta'   => true, // This option is applicable only if 'data_type' is 'meta'.
		'ui_border_top'   => true, // Display CSS border-top in the editor control.
		// Setting range specific options:
		'default_value'   => 50,
		'step'            => 1,
		'min'             => 0, // Required.
		'max'             => 100, // Required.
	);

= Setting - Range =

The following are all available options for a **Setting - Range**.

	$range_float = array(
		'type'            => 'range_float', // Required.
		// Optionally, an id may be specified. It will be used by the plugin to
		// identify the setting and will be applied to the control html.
		// The prefix set in the sidebar option 'id_prefix' will be applied.
		'id'              => 'range_float_id',
		'data_type'       => 'none', // Available: 'meta', 'localstorage', 'none'.
		'data_key'        => 'range_float_key', // Required if 'data_type' is 'meta' or 'localstorage'.
		// Use 'data_key_prefix' to set a custom prefix for this setting 'data_key'.
		// If 'data_key_prefix' is not assigned, the 'data_key_prefix' from the sidebar
		// where this setting is nested will be used.
		'data_key_prefix' => 'pmc_',
		'label'           => __( 'Setting label', 'my_plugin' ),
		'help'            => __( 'Setting description', 'my_plugin' ),
		'register_meta'   => true, // This option is applicable only if 'data_type' is 'meta'.
		'ui_border_top'   => true, // Display CSS border-top in the editor control.
		// Setting range_float specific options:
		'default_value'   => 5,
		'step'            => 0.5,
		'min'             => 0, // Required.
		'max'             => 10, // Required.
	);

= Setting - Select =

The following are all available options for a **Setting - Select**.

	$select = array(
		'type'            => 'select', // Required.
		// Optionally, an id may be specified. It will be used by the plugin to
		// identify the setting and will be applied to the control html.
		// The prefix set in the sidebar option 'id_prefix' will be applied.
		'id'              => 'select_id',
		'data_type'       => 'none', // Available: 'meta', 'localstorage', 'none'.
		'data_key'        => 'select_key', // Required if 'data_type' is 'meta' or 'localstorage'.
		// Use 'data_key_prefix' to set a custom prefix for this setting 'data_key'.
		// If 'data_key_prefix' is not assigned, the 'data_key_prefix' from the sidebar
		// where this setting is nested will be used.
		'data_key_prefix' => 'pmc_',
		'label'           => __( 'Setting label', 'my_plugin' ),
		'help'            => __( 'Setting description', 'my_plugin' ),
		'register_meta'   => true, // This option is applicable only if 'data_type' is 'meta'.
		'ui_border_top'   => true, // Display CSS border-top in the editor control.
		// Setting select specific options:
		'default_value'   => 'bbb', // Value from 'options'.
		'options'         => array( // Required.
			'aaa' => __( 'aaa Option', 'my_plugin' ),
			'bbb' => __( 'bbb Option', 'my_plugin' ),
			'ccc' => __( 'ccc Option', 'my_plugin' ),
		),
	);

= Setting - Text =

The following are all available options for a **Setting - Text**.

	$text = array(
		'type'            => 'text', // Required.
		// Optionally, an id may be specified. It will be used by the plugin to
		// identify the setting and will be applied to the control html.
		// The prefix set in the sidebar option 'id_prefix' will be applied.
		'id'              => 'text_id',
		'data_type'       => 'none', // Available: 'meta', 'none'.
		'data_key'        => 'text_key', // Required if 'data_type' is 'meta'.
		// Use 'data_key_prefix' to set a custom prefix for this setting 'data_key'.
		// If 'data_key_prefix' is not assigned, the 'data_key_prefix' from the sidebar
		// where this setting is nested will be used.
		'data_key_prefix' => 'pmc_',
		'label'           => __( 'Setting label', 'my_plugin' ),
		'help'            => __( 'Setting description', 'my_plugin' ),
		'register_meta'   => true, // This option is applicable only if 'data_type' is 'meta'.
		'ui_border_top'   => true, // Display CSS border-top in the editor control.
		// Setting text specific options:
		'default_value'   => '',
		'placeholder'     => __( 'Enter text', 'my_plugin' ),
	);

= Setting - Textarea =

The following are all available options for a **Setting - Textarea**.

	$textarea = array(
		'type'            => 'textarea', // Required.
		// Optionally, an id may be specified. It will be used by the plugin to
		// identify the setting and will be applied to the control html.
		// The prefix set in the sidebar option 'id_prefix' will be applied.
		'id'              => 'textarea_id',
		'data_type'       => 'none', // Available: 'meta', 'none'.
		'data_key'        => 'textarea_key', // Required if 'data_type' is 'meta'.
		// Use 'data_key_prefix' to set a custom prefix for this setting 'data_key'.
		// If 'data_key_prefix' is not assigned, the 'data_key_prefix' from the sidebar
		// where this setting is nested will be used.
		'data_key_prefix' => 'pmc_',
		'label'           => __( 'Setting label', 'my_plugin' ),
		'help'            => __( 'Setting description', 'my_plugin' ),
		'register_meta'   => true, // This option is applicable only if 'data_type' is 'meta'.
		'ui_border_top'   => true, // Display CSS border-top in the editor control.
		// Setting textarea specific options:
		'default_value'   => '',
		'placeholder'     => __( 'Enter text', 'my_plugin' ),
	);

== Screenshots ==

1. A sidebar with different tabs, panels and controls.

== Changelog ==

= 1.2.0 =
* Added unavailable_dates option in date_single and date_range.
* Use a rest route to get the sidebars data instead of printing the data inline.
* Fixed date_range defaults not showing.
* Fixed bug when saving empty value in image and image_multiple.
* Code refactor. Migrated JavaScript to TypeScript.

= 1.1.0 =
* Simplified some of the core functions.
* Styling fixes.
* Minor bug fixes.
* Updated dependencies.

= 1.0.1 =
* Checkbox Multiple fix: If there were old values saved that no longer belong to the options, we display them as selected. If they are deselected we remove them from the options.
* Updated dependencies.

= 1.0.0 =
* Initial release.
