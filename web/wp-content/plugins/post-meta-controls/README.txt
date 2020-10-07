=== Post Meta Controls ===
Contributors: melonpan
Tags: gutenberg, meta, post-meta, settings, controls
Requires at least: 5.1
Tested up to: 5.3
Stable tag: 1.3.3
Requires PHP: 7.1
License: GPLv3
License URI: https://www.gnu.org/licenses/gpl-3.0.html

Utilities to register, save and modify post meta data in the Gutenberg editor.


== Description ==

[Demo](https://gutenberg-showcase.melonpan.io/post-meta-controls) - [Documentation](https://melonpan.io/wordpress-plugins/post-meta-controls) - [GitHub](https://github.com/garciaalvaro/post-meta-controls)

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


== Screenshots ==

1. Sidebar with different tabs, panels and controls


== Changelog ==

= 1.3.3 =
* Fixed bug with case sensitive file name mismatch

= 1.3.2 =
* Fixed incompatibilities with WordPress 5.5
* Updated packages

= 1.3.1 =
* Fixed bug that didn't load correctly small images in the editor.
* Minor bug fixes.

= 1.3.0 =
* Added minimum_days option in date_range.
* Added maximum_days option in date_range.

= 1.2.0 =
* Added unavailable_dates option in date_single and date_range.
* Use a rest route to get the sidebars data instead of printing the data inline.
* Fixed WP 5.3 meta key from saving an empty value if the key doesnt exist.
* Fixed momentjs locales file not loading correctly.
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
