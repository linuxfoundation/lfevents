<?php

namespace RebelCode\Wpra\FeedToPost\Modules;

use Psr\Container\ContainerInterface;
use RebelCode\Wpra\Core\Modules\ModuleInterface;
use WP_Post;
use WPRSS_FTP_Settings;
use WPRSS_Help;

/**
 * The module that adds the post protector feature to Feed to Post.
 *
 * @since 3.12
 */
class PostProtectorModule implements ModuleInterface
{
    /**
     * @inheritDoc
     *
     * @since 3.12
     */
    public function getFactories()
    {
        return [
            // The key of the settings field
            'wpra/f2p/protector/field/key' => function () {
                return 'keep_edited_posts';
            },
            // The label for the settings field
            'wpra/f2p/protector/field/label' => function () {
                return __('Do not delete edited posts', 'wprss');
            },
            // The default value for the settings field
            'wpra/f2p/protector/field/default' => function () {
                return '0';
            },
            // The tooltip for the settings field
            'wpra/f2p/protector/field/tooltip' => function () {
                return __(
                    'Check this box to protect posts that you manually edit or publish from being deleted.

                    This applies to limit settings that delete old posts, as well as to any delete option in the plugin.

                    <b>Important:</b> This does not prevent you from manually deleting the posts yourself.
                    <hr/>
                    Default: <em>Off</em>',
                    'wprss');
            },
            // The meta key for the marker meta data that marks a post as protected
            'wpra/f2p/protector/marker/key' => function () {
                return 'wprss_f2p_protected';
            },
        ];
    }

    /**
     * @inheritDoc
     *
     * @since 3.12
     */
    public function getExtensions()
    {
        return [];
    }

    /**
     * @inheritDoc
     *
     * @since 3.12
     */
    public function run(ContainerInterface $c)
    {
        // Registers the setting in the settings page
        add_filter('wprss_settings_array', function ($settings) use ($c) {
            $key = $c->get('wpra/f2p/protector/field/key');
            $label = $c->get('wpra/f2p/protector/field/label');

            $settings['import'][$key] = [
                'label' => $label,
                'callback' => function ($field) use ($c, $key) {
                    $value = wprss_get_general_setting($key);

                    echo wprss_options_render_checkbox($field['field_id'], $key, $value);
                    echo wprss_settings_inline_help($field['field_id'], $field['tooltip']);
                },
            ];

            return $settings;
        });

        // Adds the setting's default value
        add_filter('wprss_default_settings_general', function ($defaults) use ($c) {
            $key = $c->get('wpra/f2p/protector/field/key');
            $default = $c->get('wpra/f2p/protector/field/default');

            $defaults[$key] = $default;

            return $defaults;
        });

        // Adds the tooltip
        add_action('wprss_init', function () use ($c) {
            if (!class_exists('WPRSS_Help')) {
                return;
            }

            $prefix = 'setting-';
            $key = $c->get('wpra/f2p/protector/field/key');
            $tooltip = $c->get('wpra/f2p/protector/field/tooltip');

            WPRSS_Help::get_instance()->add_tooltip($prefix . $key, $tooltip);
        }, 12);

        // Filters the query to restrict it to same-status posts if the option is enabled
        add_filter('wprss_get_feed_items_for_source_args', function ($args, $feedId) use ($c) {
            $key = $c->get('wpra/f2p/protector/field/key');

            $setting = wprss_get_general_setting($key);
            $setting = filter_var($setting, FILTER_VALIDATE_BOOLEAN);

            if (!$setting) {
                return $args;
            }

            $options = WPRSS_FTP_Settings::get_instance()->get_computed_options($feedId);
            $marker = $c->get('wpra/f2p/protector/marker/key');

            $args['post_type'] = $options['post_type'];
            $args['post_status'] = $options['post_status'];
            $args['meta_query']['relation'] = 'AND';
            $args['meta_query'][] = [
                'key' => $marker,
                'compare' => 'NOT EXISTS',
            ];

            return $args;
        }, 11, 2);

        // When an imported post is saved, optionally mark it as protected
        add_action('save_post', function ($postId, WP_Post $post) use ($c) {
            // Prevents infinite loop
            static $running = false;
            if ($running) {
                return;
            }

            // If not an imported post or currently being imported, stop
            $feedId = get_post_meta($postId, 'wprss_feed_id', true);
            if (empty($feedId) || wprss_is_feed_source_updating($feedId)) {
                return;
            }

            // If a revision, or the wrong post type, stop
            $options = WPRSS_FTP_Settings::get_instance()->get_computed_options($feedId);
            if ($post->post_type === 'revision' || $post->post_type !== $options['post_type']) {
                return;
            }

            $running = true;

            // Add the marker to the post
            $marker = $c->get('wpra/f2p/protector/marker/key');
            update_post_meta($postId, $marker, '1');

            $running = false;
        }, 10, 2);
    }
}
