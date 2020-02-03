<?php

use Aventura\Wprss\Core\Caching;
use Aventura\Wprss\Core\Caching\ImageCache;
use Aventura\Wprss\Core\Model\Regex\HtmlEncoder;

/**
 * The WPRSS_FTP_Images class handles the caching of images retrieved from converted posts,
 * creating featured images for such posts, and adding the images to the media library.
 *
 * @since 1.0
 */
class WPRSS_FTP_Images
{
    /**
     * The singleton class instance
     */
    protected static $instance = null;

    /**
     * The image cache class instance
     */
    protected $_cache;

    /**
     * @since 3.7.3
     * @var HtmlEncoder
     */
    protected $regexEncoder;

    const IMAGE_CACHE_TTL = WPRSS_FTP_IMAGE_CACHE_TTL;

    const IMAGE_CACHE_DOWNLOAD_REQUEST_TIMEOUT = WPRSS_FTP_IMAGE_CACHE_DOWNLOAD_REQUEST_TIMEOUT;

    const IMAGE_CACHE_ORIG_FILENAME_LENGTH = WPRSS_FTP_IMAGE_CACHE_ORIG_FILENAME_LENGTH;

    const RX_D = '!';

    /**
     * The class constructor
     *
     * @since 1.0
     */
    public function __construct()
    {
        if (self::$instance === null) {
            add_action('wprss_ftp_no_featured_image', array($this, 'maybe_reject_post'), 10, 2);
        } else {
            wp_die(__("WPRSS_FTP_Images class is a singleton class and cannot be redeclared.", WPRSS_TEXT_DOMAIN));
        }
    }

    /**
     * Returns the singleton instance of the class
     *
     * @since 1.0
     * @return WPRSS_FTP_Images
     */
    public static function get_instance()
    {
        if (self::$instance === null) {
            self::$instance = new WPRSS_FTP_Images();
        }

        return self::$instance;
    }

    /**
     * Returns the cache object used by this instance.
     *
     * On first call will initialize and configure it.
     *
     * @since 3.5.1
     * @return Aventura\Wprss\Core\Caching\ImageCache
     */
    public function get_cache()
    {
        if (is_null($this->_cache)) {
            $this->_cache = new ImageCache();
            $this->_cache->set_ttl($this->get_cache_ttl());
            $this->_cache->set_download_request_timeout($this->get_cache_download_request_timeout());
            $this->_cache->set_cache_orig_filename_length($this->get_cache_orig_filename_length());
        }

        return $this->_cache;
    }

    /**
     * Get the TTL (Time To Live) for cache files.
     *
     * Can be overridden by implementing a handler for the
     * `wprss_f2p_image_cache_ttl` filter.
     *
     * @since 3.5.1
     * @return int Number of seconds for the cache files to be considered valid.
     */
    public function get_cache_ttl()
    {
        return apply_filters('wprss_f2p_image_cache_ttl', self::IMAGE_CACHE_TTL);
    }

    /**
     * Get the timeout for cache download requests.
     *
     * Can be overridden by implementing a handler for the
     * `wprss_f2p_image_cache_download_request_timeout` filter.
     *
     * @since 3.5.1
     * @return int Number of seconds to wait for the image resource to respond when downloading.
     */
    public function get_cache_download_request_timeout()
    {
        return apply_filters('wprss_f2p_image_cache_download_request_timeout',
            self::IMAGE_CACHE_DOWNLOAD_REQUEST_TIMEOUT);
    }

    /**
     * Extracts image URLs from `srcset` attribute values.
     *
     * @since 3.7.3
     *
     * @param string|string[] $srcset One or many `srcset` attribute value strings.
     *
     * @return array[] A set of arrays, each one of which contains URLs from a `srcset` attrbute.
     */
    public function get_srcset_img_urls($srcset)
    {
        if (!is_array($srcset)) {
            $srcset = array($srcset);
        }

        $urls = array();
        foreach ($srcset as $_sIdx => $_srcset) {
            $urls[$_sIdx] = array();
            $_srcset = trim($_srcset);
            $_srcset = explode(',', $_srcset);
            foreach ($_srcset as $_definition) {
                $_definition = trim($_definition);
                if (($pieces = preg_split('!([\s]+)!m', $_definition)) === false) {
                    continue;
                }

                if (isset($pieces[0])) {
                    $urls[$_sIdx][] = $pieces[0];
                }
            }
        }

        return $urls;
    }

    /**
     * Extracts all `srcset` attribute value from one or more strings.
     *
     * @since 3.7.3
     *
     * @param string|string[] $images One or many image tags HTML strings, or other tag strings, that may contain
     *                                `srcset` attributes.
     *
     * @return string[] A set of `srcset` attribute value strings.
     */
    public function get_srcset_values($images)
    {
        if (!is_array($images)) {
            $images = array($images);
        }

        $d = static::RX_D;
        $exprSrcSet = $this->htmlEncodifyRegex('srcset="(.*?)"', $d);
        $exprSrcSet = $d . $exprSrcSet . $d . 'mi';

        $values = array();
        foreach ($images as $_imgTag) {
            $matches = array();
            preg_match_all($exprSrcSet, $_imgTag, $matches);
            $matches = $this->_cleanHtmlRegexMatches($matches);
            if (isset($matches[1])) {
                $values = array_merge($values, $matches[1]);
            }
        }

        return $values;
    }

    /**
     * Merges primary and secondary such that after each primary image come its corresponding secondary images.
     *
     * @since 3.7.3
     *
     * @param string[] $primary   An array of primary image URLs.
     * @param array[]  $secondary An array of secondary image URL arrays.
     *
     * @return string An array of images, sorted such that secondary images come after each corresponding primary image.
     */
    protected function _mergeFoundImages($primary, $secondary)
    {
        $result = array();
        $pIdx = 0;
        $secondaryKeys = array_keys($secondary);
        foreach ($primary as $_pImg) {
            $result[] = $_pImg;
            if (!isset($secondary[$pIdx])) {
                continue;
            }

            $_secondary = $secondary[$secondaryKeys[$pIdx]];
            foreach ($_secondary as $_sImg) {
                $result[] = $_sImg;
            }

            $pIdx++;
        }

        return $result;
    }

    /**
     * Get the length of the original resource filename to preserve in the cache filename.
     *
     * @since 3.7.3
     * @return int Number of characters of the original filename to preserve.
     */
    public function get_cache_orig_filename_length()
    {
        return apply_filters('wprss_f2p_image_cache_orig_filename_length', self::IMAGE_CACHE_ORIG_FILENAME_LENGTH);
    }

    /**
     * Finds the image URLs in a given piece of HTML content.
     *
     * @since 3.11
     *
     * @param string     $content The string content.
     * @param int|string $source  The ID of the feed source whose settings to use.
     *
     * @return string[]
     */
    public function find_images($content, $source)
    {
        // Get the computed settings
        $options = WPRSS_FTP_Settings::get_instance()->get_computed_options($source);

        // Get the minimum image size settings
        $min_width = $options['image_min_width'];
        $min_height = $options['image_min_height'];

        $d = static::RX_D; // Delimiter
        $imgExpr = $this->htmlEncodifyRegex('<img.*?src="(.*?)".*?>', $d);
        $imgExpr = $d . $imgExpr . $d . 'i';

        // Match all <img> tag src attributes ( the image source url )
        preg_match_all($imgExpr, $content, $matches);

        $matches = $this->_cleanHtmlRegexMatches($matches);
        $imageTags = isset($matches[0]) ? $matches[0] : [];
        $imageUrls = isset($matches[1]) ? $matches[1] : [];

        // Include the file and media libraries
        require_once(ABSPATH . 'wp-admin/includes/media.php');
        require_once(ABSPATH . 'wp-admin/includes/file.php');
        require_once(ABSPATH . 'wp-admin/includes/image.php');

        $foundImages = $imageUrls;

        /*
         * Process `srcset` attributes if the "Save all image sizes" option is enabled.
         */
        if (WPRSS_FTP_Utils::multiboolean($options['save_all_image_sizes'])) {
            $srcSetAttributes = $this->get_srcset_values($imageTags);
            $srcSetImgUrls = $this->get_srcset_img_urls($srcSetAttributes);
            $foundImages = $this->_mergeFoundImages($foundImages, $srcSetImgUrls);
        }

        return $foundImages;
    }

    /**
     * Downloads a given list of images according a feed source's settings and attaches them to a post.
     *
     * @since 3.11
     *
     * @param $images
     * @param $postId
     * @param $source
     */
    public function save_images($images, $postId, $source)
    {
        $logger = WPRSS_FTP_Utils::get_logger($source);

        // Get the computed settings
        $options = WPRSS_FTP_Settings::get_instance()->get_computed_options($source);

        // Get the option for local image saving
        $saveImages = WPRSS_FTP_Utils::multiboolean($options['save_images_locally']) === true;
        // Get the minimum image size settings
        $min_width = $options['image_min_width'];
        $min_height = $options['image_min_height'];

        $finalImages = [];
        foreach ($images as $url) {
            // Attempt to get a larger version, if image is from facebook
            $newUrl = $this->attempt_to_get_large_fb_image($url);

            // Only proceed if images should be saved and the image is not a local one
            if ($saveImages && !wprss_ftp_is_url_local($url)) {
                // Use a large time limit, to accommodate for big file sizes
                set_time_limit(600);

                // Check its size and download it if big enough
                $img = ($this->image_obeys_minimum_size($newUrl, $min_width, $min_height) !== false)
                    ? wprss_ftp_media_sideload_image($newUrl, $postId)
                    : null;

                if ($img !== null && !is_wp_error($img)) {
                    $newUrl = wp_get_attachment_url($img);
                } else {
                    $logger->warning('Failed to download image from "{url}". Error: {error}', [
                        'url' => $newUrl,
                        'error' => is_wp_error($img) ? $img->get_error_message() : 'unknown',
                    ]);
                }
            }

            // Map the new URL to the original URL
            $finalImages[$url] = $newUrl;
        }

        // Now we have the images array built
        // We extract the original image URLs from the keys and the new image URLs from the values
        $old = array_keys($finalImages);
        $new = array_values($finalImages);

        $content = get_post($postId)->post_content;

        if (WPRSS_FTP_Utils::multiboolean($options['save_images_locally']) === true) {
            // Replace the old image URLs with the new URLs
            $content = str_replace($old, $new, $content);
            // Update the post content
            WPRSS_FTP_Utils::update_post_content($postId, $content);
        } elseif (apply_filters('wprss_ftp_strip_images_from_post', false) === true) {
            // Remove all image tags
            $content = preg_replace("/<img[^>]+\>/i", '', $content);
            // Update the post content
            WPRSS_FTP_Utils::update_post_content($postId, $content);
        }

        do_action('wprss_ftp_saved_images_from_post', $postId, $source, $finalImages);
    }

    /**
     * Determines and saves the featured image for the imported post.
     *
     * This is ran before content images have been downloaded.
     *
     * @since 2.7.4
     *
     * @param int   $post_ID The ID of the post that is being imported.
     * @param int   $source  The ID of the feed source, which is importing the post
     * @param array $images  A numeric array, where each value is the remote URL of an image in post content.
     */
    public function save_ft_image_for_post($post_ID, $source, $images)
    {
        // Get the post form the ID
        $post = get_post($post_ID);
        // If the post is null, return null.
        if ($post === null) {
            // Trigger action for no featured image determined for this post
            do_action('wprss_ftp_no_featured_image', $post_ID, $source);

            return null;
        }

        // Get the post content
        $content = $post->post_content;

        // Get the computed settings for the feed source
        $options = WPRSS_FTP_Settings::get_instance()->get_computed_options($source);

        // If the featured image option is disabled, do NOT continue.
        if (WPRSS_FTP_Utils::multiboolean($options['use_featured_image']) === false) {
            // Trigger action for no featured image determined for this post
            do_action('wprss_ftp_no_featured_image', $post_ID, $source);

            return null;
        }

        $logger = WPRSS_FTP_Utils::get_logger($source);

        // Start by trimming whitespace from image URLs
        $images = array_map('trim', $images);

        // The URL of the determined featured image
        $featured_image_url = null;

        // Get the minimum image size settings
        $min_width = $options['image_min_width'];
        $min_height = $options['image_min_height'];

        // DETERMINED FEATURED IMAGE
        $featured_image = null;
        $featured_image_source = null;
        // WHETHER OR NOT USING THE FALLBACK IMAGE (used to skip most of the image processing in the function)
        $using_fallback = false;

        // Check which featured image option is being used
        switch ($options['featured_image']) {
            default:

                // FIRST/LAST image in post content
            case 'first':
            case 'last':

                // If using the Last Image option, reverse the images array
                if ($options['featured_image'] === 'last') {
                    $images = array_reverse($images, true);
                }

                // Iterate through all the images
                foreach ($images as $_image) {
                    // The the image URL is empty, or it does not obey the minimum size constraint, jump to next image
                    if (empty($_image) || !$this->image_obeys_minimum_size($_image, $min_width, $min_height)) {
                        continue;
                    }

                    // Attempt to use this iamge as featured imafe
                    $ft_image_found = $_image;
                    $featured_image = $_image;

                    // Check if the image URL is local
                    if (!wprss_ftp_is_url_local($featured_image)) {
                        // If not, download it and attach it to the post
                        $featured_image = apply_filters('wprss_ftp_featured_image_url', $featured_image, $post_ID,
                            $source, $options['featured_image']);
                        $featured_image = wprss_ftp_media_sideload_image($featured_image, $post_ID, true);

                        $logger->info('Set {type} image in content as the featured image', [
                            'type' => $options['featured_image'],
                        ]);
                    } // If it is local, simply attach it to the post
                    else {
                        $featured_image = apply_filters('wprss_ftp_featured_image_url', $featured_image, $post_ID,
                            $source, $options['featured_image']);
                        self::set_featured_image($post_ID, $featured_image, true);
                    }

                    // If no error was encountered, exit the loop
                    // If an error was encountered, the next image will be tested.
                    if (!is_wp_error($featured_image)) {
                        $featured_image_source = $_image;
                        break;
                    }
                }

                // Indicate that NO image was used as featured image
                if (is_wp_error($featured_image)) {
                    $logger->notice('Could not set featured image from post content. {details}', [
                        'details' => $featured_image->get_error_message(),
                    ]);

                    $featured_image_source = $featured_image = null;
                }

                break; // END OF FIRST / LAST IMAGE CASE

            // FEED <MEDIA:THUMBNAIL> IMAGE / <ENCLOSURE> TAG
            case 'thumb':
            case 'enclosure':

                // Prepare the tag in which to look for the image
                $tag = ($options['featured_image'] == 'thumb') ? 'media:thumbnail' : 'enclosure:thumbnail';
                $orig_tag = $tag;
                $tag = apply_filters('wprss_ftp_featured_image_meta_key', $tag, $post_ID, $source,
                    $options['featured_image']);

                /* Get the media thumbnail from post meta ( converter class places the tag contents in post meta ).
                 * If the original meta key was modified by the `wprss_ftp_featured_image_meta_key` filter,
                 * no prefix is applied to the meta key.
                 */
                $thumbnail = trim(WPRSS_FTP_Meta::get_instance()
                                                ->get_meta($post_ID, $tag, ($use_prefix = $tag === $orig_tag)));

                // Check if the thumbnail is large enough to accept
                if ($this->image_obeys_minimum_size($thumbnail, $min_width, $min_height) === true) {
                    // Download this image, attach it to the post and use it as the featured image
                    $featured_image_source = $thumbnail;
                    $thumbnail = apply_filters('wprss_ftp_featured_image_url', $thumbnail, $post_ID, $source,
                        $options['featured_image']);

                    $featured_image = wprss_ftp_media_sideload_image($thumbnail, $post_ID, true);
                    $logger->info('Set {type} thumbnail as the featured image', [
                        'type' => ($options['featured_image'] == 'thumb') ? 'media' : 'enclosure',
                    ]);

                    // If an error was encountered, set the featured image to NULL
                    if (is_wp_error($featured_image)) {
                        $logger->error('Could not set featured image from thumbnail/enclosure tag. {details}', [
                            'details' => $featured_image->get_error_message(),
                        ]);

                        $featured_image_source = $featured_image = null;
                    }
                }

                break; // END OF MEDIA:THUMBNAIL / ENCLOSURE CASE

            // FALLBACK FEATURED IMAGE
            case 'fallback':

                // Get the fallback featured image
                $fallback_image = get_post_thumbnail_id($source);

                // Check if the fallback featured image is set
                if (!empty($fallback_image)) {
                    // If it is set, use it as featured image for the imported post
                    self::set_featured_image($post_ID, $fallback_image);
                    // Indicate that the fallback was used
                    $using_fallback = true;

                    $logger->info('Used the feed source\'s fallback featured image');
                }
                break;
        } // End of switch

        // If the fallback image was used, then we are done.
        if (!$using_fallback) {
            // If a featured image was determined
            if ($featured_image !== null && !is_wp_error($featured_image)) {
                // Check for filter to remove featured image from post
                $remove_ft_image = apply_filters('wprss_ftp_remove_ft_image_from_content', false);
                // We remove the ft image, if the filter returns TRUE, or if it returns an array and the post source is in the array.
                $remove = $remove_ft_image === true || (is_array($remove_ft_image) && in_array($source,
                            $remove_ft_image));

                // If removing and the ft image is in the content (not media:thumbnail)
                // (Determined either by legacy filter or meta option)
                if ($remove || WPRSS_FTP_Utils::multiboolean($options['remove_ft_image']) === true) {
                    $new_content = $content;
                    $img_to_remove = $featured_image;
                    if ($options['featured_image'] === 'first' || $options['featured_image'] === 'last') {
                        $img_to_remove = $ft_image_found;
                    }
                    // Prepare the img tag regex
                    $d = static::RX_D; // In case the image tag contains the delimiter somewhere, it needs to be escaped too
                    $tag_search = array(
                        '<img[^<>]*?src="%1$s"[^<>]*?>',
                        '<img[^<>]*?srcset="[^<>]*?%1$s.[^<>]*?"[^<>]*?>',
                    );
                    foreach ($tag_search as $_expr) {
                        // This will transform the expression to match images in html-encoded content
                        $_expr = $this->htmlEncodifyRegex($_expr, $d);
                        /* The result should be an expression that matches <img> tags, both regular and HTML-encoded.
                         * However, this is still a format string, ready to accept the image URL
                         */
                        $_expr = sprintf($_expr, preg_quote(esc_attr($img_to_remove), $d));
                        // Replace the tag with an empty string, and get the new content
                        $new_content = preg_replace($d . $_expr . $d . 'Uis', '', $new_content,
                            apply_filters('wprss_ftp_remove_ft_image_limit', 1));
                    }

                    $logger->debug('Removed featured image from the post\'s content');

                    // Update the post content, if changed
                    if ($new_content !== $content) {
                        WPRSS_FTP_Utils::update_post_content($post_ID, $new_content);
                    }
                }
            }

            // However,
            // If NO featued image was determined
            else {
                $featured_image = null;

                // Get the user filter for using the feed image
                $user_filter = apply_filters('wprss_ftp_feed_image_fallback', false, $post_ID, $source, $images);
                $user_filter_enabled = $user_filter === true || (is_array($user_filter) && in_array($source,
                            $user_filter));

                // Check if the core supports getting the feed image and if the user filter is enabled
                if (function_exists('wprss_get_feed_image') && $user_filter_enabled) {
                    // Get the url of the feed image
                    $feed_image = wprss_get_feed_image($source);

                    // Attempt to download it and attach it to the post
                    $feed_image = apply_filters('wprss_ftp_featured_image_url', $feed_image, $post_ID, $source,
                        $options['featured_image']);
                    $featured_image_source = $feed_image;
                    $featured_image = wprss_ftp_media_sideload_image($feed_image, $post_ID, true);

                    // If an error was encountered, indicate it by setting the featured image to NULL
                    if (is_wp_error($featured_image) || $featured_image === null) {
                        $feed_image = $featured_image = null;
                    } else {
                        $logger->info('Used the feed\'s site image as the featured image');
                    }
                }

                // If the feed image did not work, resort to using the fallback, if set
                if ($featured_image == null) {
                    // Get the fallback image
                    $fallback_image = get_post_thumbnail_id($source);
                    // If it is set, use it as the featured image for the post
                    if (!empty($fallback_image)) {
                        self::set_featured_image($post_ID, $fallback_image);
                        $logger->info('Used the fallback image as the featured image');
                    } else {
                        $logger->notice('No featured image was found for "{post}"', [
                            'post' => $post->post_title,
                        ]);

                        // Trigger action for no featured image determined for this post
                        do_action('wprss_ftp_no_featured_image', $post_ID, $source);
                    }
                }
            }
        }

        update_post_meta($post_ID, 'wprss_ftp_featured_image_source', (string) $featured_image_source);

        do_action('wprss_ftp_determined_featured_image', $post_ID, $source);

        return $featured_image_source;
    }

    /**
     * Returns a representation of an HTML expression that matches all representations of that HTML.
     *
     * @since 3.7.3
     *
     * @param string $expr      The expression to encodify.
     * @param string $delimiter The delimiter of the expression.
     *                          Default: the value of the `RX_D` class constant.
     *
     * @return string
     */
    function htmlEncodifyRegex($expr, $delimiter = null)
    {
        $encoder = $this->_getRegexEncoder();

        // Default delimiter
        if (!is_null($delimiter)) {
            $encoder->setDelimiter($delimiter);
        }

        $result = $encoder->encodify($expr);

        return $result;
    }

    /**
     * Gets the regex HTML encoder instance.
     *
     * @since 3.7.3
     *
     * @return HtmlEncoder
     */
    protected function _getRegexEncoder()
    {
        if (is_null($this->regexEncoder)) {
            $this->regexEncoder = wprss_feedtopost_addon()->getFactory()->createRegexEncoder();
        }

        $this->regexEncoder->reset();

        return $this->regexEncoder;
    }

    /**
     * Cleans matches for an HTML encodified regular expression.
     *
     * @since 3.7.3
     *
     * @param array $matches The matches to clean.
     *
     * @return array The cleaned matches.
     *  Numeric keys will be re-indexed. String keys will be preserved.
     */
    protected function _cleanHtmlRegexMatches(array $matches)
    {
        return $this->_getRegexEncoder()->cleanMatches($matches);
    }

    /**
     * Checks if the post is to be rejected since no featured image was determined.
     *
     * If the 'must_have_ft_image' is enabled for the feed source that imported the post, the
     * post is deleted. Otherwise no action is taken.
     *
     * This function triggers the action 'wprss_ftp_after_post_rejected_no_ft_image' upon
     * completion.
     *
     * @since 3.3
     *
     * @param $post_id   int The ID of the post.
     * @param $source_id int The ID of the feed source.
     */
    public function maybe_reject_post($post_id, $source_id)
    {
        $logger = WPRSS_FTP_Utils::get_logger($source_id);

        // Get the meta option value that determines if posts are allowed if they have no featured image
        $must_have_ft_image = WPRSS_FTP_Meta::get_instance()->get($source_id, 'must_have_ft_image');
        // Check if the option is enabled
        $enabled = WPRSS_FTP_Utils::multiboolean($must_have_ft_image) === true;
        if ($enabled) {
            $logger->debug('Discarding post "{title}" due to a lack of a featured image.', [
                'title' => get_the_title($post_id),
            ]);

            // Filter force deletion. False for sending post to trash
            $force_delete = apply_filters('wprss_ftp_post_rejected_no_ft_image_force_delete', true);
            // Delete the post
            wp_delete_post($post_id, $force_delete);
            // Return null from the convertor
            add_filter('wprss_ftp_converter_return_post_' . $post_id, '__return_false');
        }

        // Trigger action. First param: TRUE reject, FALSE accepted.
        do_action('wprss_ftp_after_post_rejected_no_ft_image', $enabled, $source_id);
    }

    /**
     * Sets the image as a featured image to the post.
     *
     * @since 1.9.2
     *
     * @param int $post_id The ID of the post
     * @param int $image   the ID of the image
     */
    public static function set_featured_image($post_id, $image, $is_url = false)
    {
        $thumbnail = ($is_url) ? self::get_attachment_id_from_url($image) : $image;
        set_post_thumbnail($post_id, $thumbnail);

        $url = ($is_url) ? $image : wp_get_attachment_url($image);

        // Check the featured image meta filter
        $featured_image_meta = apply_filters('wprss_ftp_featured_image_meta', false);
        // check if it is false. If not, use the meta key to insert the image
        if ($featured_image_meta !== false && $featured_image_meta !== '') {
            update_post_meta($post_id, $featured_image_meta, $url);
        }

        // Check the featured image meta ID filter
        $featured_image_meta_id = apply_filters('wprss_ftp_featured_image_meta_id', false);
        // check if it is false. If not, use the meta key to insert the image ID
        if ($featured_image_meta_id !== false && $featured_image_meta_id !== '') {
            update_post_meta($post_id, $featured_image_meta_id, $post_id);
        }
    }

    /**
     * Returns the attachment ID of the image with the given source
     *
     * @since 1.0
     */
    public static function get_attachment_id_from_url($image_src)
    {
        global $wpdb;
        $query = "SELECT ID FROM {$wpdb->posts} WHERE guid='$image_src'";
        $id = $wpdb->get_var($query);

        return $id;
    }

    /**
     * FACEBOOK IMAGE URL FIX.
     * Attempts to return a larger image then the one included from Facebook.
     *
     * @since 1.8.2
     */
    public function attempt_to_get_large_fb_image($url)
    {
        // Check if it is a facebook image url and if a larger size exists
        if (stripos($url, "fbcdn") > 0) {
            $components = explode('_', basename($url));
            $id = $components[1];
            if (stripos($url, "-vthumb") > 0) {
                $id = $components[2];
            }

            # Contact the FB Graph API to get the image data.
            $graph_url = "http://graph.facebook.com/{$id}";
            $response = wp_remote_get($graph_url);

            if (!is_wp_error($response) && isset($response['body'])) {
                $json = json_decode($response['body'], true);

                if (isset($json['images']) && isset($json['images'][0])) {
                    // Grab the first, highest-res image in the images array
                    $hires_url = $json['images'][0]['source'];

                    return $hires_url;
                } elseif (isset($json['format']) && isset($json['format'][0])) {
                    // Grab the /last/, highest-res image in the images array
                    $idx = sizeof($json['format']) - 1;
                    $hires_url = $json['format'][$idx]['picture'];

                    return $hires_url;
                }
            }
        } elseif (stripos($url, "fbexternal") > 0 && stripos($url, "safe_image.php") > 0) {
            $idx = stripos($url, "url=");
            $url = substr($url, $idx + strlen("url="));
            $url = urldecode($url);
        }

        return $url; // We couldn't find a high-res image
    }

    /**
     * Checks if the given image obeys the given minimum size contraints.
     *
     * @since 1.8.2
     */
    public function image_obeys_minimum_size($img_url, $min_width, $min_height)
    {
        // Check for filter to skip the size checking
        $skip_size_check = apply_filters('wprss_ftp_skip_image_size_check', false);
        if ($skip_size_check === true) {
            return true;
        }

        $img_url = trim($img_url);
        $img_url = str_replace(' ', '%20', $img_url);

        // Get the image via the cache.
        try {
            $img = $this->get_cache()->get_images($img_url);
        } catch (Exception $e) {
            WPRSS_FTP_Utils::get_logger()->warning('Failed to fetch image size from {url}. Error: {error}', [
                'url' => $img_url,
                'error' => $e->getMessage(),
            ]);

            return false;
        }

        // Check if the cache download or lookup succeeded.
        if ($img instanceof Caching\ImageCache\Image) {
            // Try to get the dimensions of the image.
            $dimensions = $img->get_size();

            if (!empty($dimensions)) {
                // We found the dimensions of the image.
                list($width, $height) = $dimensions;
            } else {
                // Couldn't get the dimensions, this isn't a valid image.
                $img->delete();

                return false;
            }
        }

        // Check if the image obeys the size requirements.
        $obeysMinimum = true;
        if ($min_width !== '') {
            $obeysMinimum = ($obeysMinimum && ($width >= $min_width));
        }
        if ($min_height !== '') {
            $obeysMinimum = ($obeysMinimum && ($height >= $min_height));
        }

        if ($obeysMinimum === false) {
            $img->delete(); // We don't need to keep this image around.
        }

        return $obeysMinimum;
    }
}

add_filter('wprss_ftp_feed_image_fallback', 'wprss_ftp_feed_image_fallback', 10, 4);
/**
 * Sets the fallback to feed image option to true.
 *
 * @since 1.3.1
 *
 * @param int   $post_ID  ID of the post, for which to get the setting
 * @param int|WP_Post The source or source ID of the subject post
 * @param array $images   Numeric array of URLs for images, that are candidates to become the subject posts's thumbnail
 * @param bool  $fallback Purpose of this argument is unknown.
 */
function wprss_ftp_feed_image_fallback($fallback, $post_ID, $source, $images)
{
    $options = WPRSS_FTP_Settings::get_instance()->get_computed_options($source);

    return WPRSS_FTP_Utils::multiboolean(isset($options['fallback_to_feed_image']) ? $options['fallback_to_feed_image']
        : null);
}

/**
 * Checks if the wprss_media_sideload_image function exists ( in the core ) and runs it.
 * Otherwise, the WP media_sideload_image function is used.
 *
 * @since 1.4.1
 */
function wprss_ftp_media_sideload_image_deprecated($url, $post_id)
{
    if (function_exists('wprss_media_sideload_image')) {
        return wprss_media_sideload_image(urldecode($url), $post_id);
    } else {
        return media_sideload_image($url, $post_id);
    }
}

/**
 * Checks if the given url is a local or external one
 *
 * @since 1.8.2
 */
function wprss_ftp_is_url_local($url, $home_url = null)
{
    if (is_null($home_url)) {
        $home_url = get_option('siteurl');
    }

    // What about the URLs are we comparing?
    $relevant_parts = array('host', 'path');

    // Get the site's url
    $siteurl = trim(WPRSS_FTP_Utils::rebuild_url($home_url, $relevant_parts), '/');
    // The URL in question
    $url = trim(WPRSS_FTP_Utils::rebuild_url(WPRSS_FTP_Utils::encode_and_parse_url($url), $relevant_parts, '/'));

    return strpos($url, $siteurl) === 0;
}

/**
 * Download an image from the specified URL and attach it to a post.
 *
 * Modified version of core function media_sideload_image() in /wp-admin/includes/media.php
 * (which returns an html img tag instead of attachment ID).
 * Additional functionality: ability override actual filename,
 * and to pass $post_data to override values in wp_insert_attachment (original only allowed $desc).
 *
 * Uses image cache to avoid re-downloading images. Keeps cache intact by
 * creating a copy of the cache file, which will eventually be moved.
 *
 * Credits to somatic
 * http://wordpress.stackexchange.com/questions/30284/media-sideload-image-file-name/44115#44115
 *
 * @since 2.7.4
 *
 * @param string $url       (required) The URL of the image to download
 * @param int    $post_id   (required) The post ID the media is to be associated with
 * @param bool   $thumb     (optional) Whether to make this attachment the Featured Image for the post (post_thumbnail)
 * @param string $filename  (optional) Replacement filename for the URL filename (do not include extension)
 * @param array  $post_data (optional) Array of key => values for wp_posts table (ex: 'post_title' => 'foobar',
 *                          'post_status' => 'draft')
 *
 * @return int|object The ID of the attachment or a WP_Error on failure
 */
function wprss_ftp_media_sideload_image(
    $url = null,
    $post_id = null,
    $thumb = null,
    $filename = null,
    $post_data = array()
) {
    // Use Core's version is its available
    if (function_exists('wpra_media_sideload_image')) {
        return wpra_media_sideload_image($url, $post_id, $thumb, $filename, $post_data);
    }

    if (!$url || !$post_id) {
        return new WP_Error('missing', "Need a valid URL and post ID...");
    }

    // Get the local image file
    $cache = WPRSS_FTP_Images::get_instance()->get_cache();
    try {
        $img = $cache->get_images($url);
        /* @var $img Aventura\Wprss\Core\Caching\ImageCache\Image */
    } catch (Exception $e) {
        return new WP_Error('could_not_load_image', $e->getMessage(), $url);
    }

    $logger = WPRSS_FTP_Utils::get_logger();
    $logger->debug('Image from cache: {url} -> {path}', [
        'url' => $img->get_url(),
        'path' => $img->get_local_path(),
    ]);

    // Get the path
    $tmp = $img->get_local_path();
    copy($tmp, $tmp = wp_tempnam()); // media_handle_sideload() will move the file, but we need the cache to remain
    $tmpPath = pathinfo($tmp);
    $ext = isset($tmpPath['extension']) ? trim($tmpPath['extension']) : null;

    $logger->debug('Copied cached image to {path}', [
        'path' => $tmp,
    ]);

    $url_filename = $img->get_unique_name();

    // override filename if given, reconstruct server path
    if (!empty($filename)) {
        $filename = sanitize_file_name($filename);
        // build new path
        $new = $tmpPath['dirname'] . "/" . $filename . "." . $ext;
        // renames temp file on server
        rename($tmp, $new);
        // push new filename (in path) to be used in file array later
        $tmp = $new;
    }

    // determine file type (ext and mime/type)
    $url_type = wp_check_filetype($url_filename);

    // If the wp_check_filetype function fails to determine the MIME type
    if (empty($url_type['type'])) {
        $url_type = wprss_ftp_check_filetype($tmp, $url);
    }
    $ext = $url_type['ext'];

    // assemble file data (should be built like $_FILES since wp_handle_sideload() will be using)
    $file_array = array();
    // full server path to temp file
    $file_array['tmp_name'] = $tmp;
    $url = trim($img->get_url());
    $parts = parse_url($url);
    $baseName = uniqid($parts['host']);

    if (!empty($filename)) {
        // user given filename for title, add original URL extension
        $baseName = $filename . "." . $ext;
    } else {
        // The original basename, falling back to auto-generated based on domain
        $base = basename($parts['path']);
        if (strlen($baseName) || trim($baseName) !== '/') {
            $baseName = $base;
        }
    }
    $file_array['name'] = $baseName;

    // set additional wp_posts columns
    if (empty($post_data['post_title'])) {
        // just use the original filename (no extension)
        $post_data['post_title'] = $file_array['name'];
    }

    // make sure gets tied to parent
    if (empty($post_data['post_parent'])) {
        $post_data['post_parent'] = $post_id;
    }

    // required libraries for media_handle_sideload
    require_once(ABSPATH . 'wp-admin/includes/file.php');
    require_once(ABSPATH . 'wp-admin/includes/media.php');
    require_once(ABSPATH . 'wp-admin/includes/image.php');

    // NO FILENAME FIX
    // WordPress does not allow file images that are not in the form of a filename
    // ex: http://domain.com/thoufiqadsjucpqwuamoshfjnax8mtrh/iorqhewufjasj

    if (apply_filters('wprss_ftp_override_upload_security', true) === true) {
        // If we successfully retrieved the MIME type
        if ($url_type !== false && isset($url_type['type']) && !empty($url_type['type'])) {
            // Prepare the MIME and file type
            global $wprss_ftp_ext_override;
            global $wprss_ftp_type_override;
            $wprss_ftp_type_override = $url_type['type'];

            $mime_to_ext = wprss_ftp_get_mimetype_ext_mapping();

            // Get the ext
            if (isset($mime_to_ext[$wprss_ftp_type_override])) {
                $wprss_ftp_ext_override = $mime_to_ext[$wprss_ftp_type_override];
            } else {
                $wprss_ftp_ext_override = 'png'; // Default to png (most common web image extension)
            }
            // Add a filter to ensure that the image ext and mime type get passed through
            add_filter('wp_check_filetype_and_ext', 'wprss_ftp_mime_override', 10, 4);
        }
    }

    // do the validation and storage stuff
    clearstatcache(false,
        $file_array['tmp_name']); // For some reason, deep down filesize() returned 0 for the temporary file without this
    $att_id = media_handle_sideload($file_array, $post_id, '',
        $post_data); // $post_data can override the items saved to wp_posts table, like post_mime_type, guid, post_parent, post_title, post_content, post_status

    // If error storing permanently, unlink
    if (is_wp_error($att_id)) {
        $logger->warning('Failed to download and attach image to post #{id}. Image URL: {url}', [
            'id' => $post_id,
            'url' => $url,
        ]);

        $img->delete();
        @unlink($tmp); // Delete the cache copy needed for media_handle_sideload()

        return $att_id; // output wp_error
    }

    // set as post thumbnail if desired
    if ($thumb) {
        WPRSS_FTP_Images::set_featured_image($post_id, $att_id);
    }

    return $att_id;
}

/**
 * Overrides WordPress' security check if no image extension of MIME type is given.
 *
 * @since 2.8.1
 */
function wprss_ftp_mime_override($image, $file, $filename, $mimes)
{
    if (empty($image['ext'])) {
        global $wprss_ftp_ext_override;
        $image['ext'] = $wprss_ftp_ext_override;
    }
    if (empty($image['type'])) {
        global $wprss_ftp_type_override;
        $image['type'] = $wprss_ftp_type_override;
    }

    return $image;
}

/**
 * Generates a random string with a given length.
 *
 * @since 2.9.6
 *
 * @param int $length The length of the generated string.
 *
 * @return string The generated string
 */
function wprss_ftp_generate_random_string($length = 10)
{
    $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $randomString = '';
    for ($i = 0; $i < $length; $i++) {
        $randomString .= $characters[rand(0, strlen($characters) - 1)];
    }

    return $randomString;
}

/**
 * Fallback function for determining mime type and extension of an image
 *
 * @since 3.5.3
 *
 * @param string $local_image_path  Local path of the downloaded image
 * @param string $remote_image_path Remote image url
 *
 * @return array Values with extension first and mime type.
 */
function wprss_ftp_check_filetype($local_image_path, $remote_image_path)
{
    $ext = false;
    $type = false;

    $mime_to_ext_mapping = wprss_ftp_get_mimetype_ext_mapping();

    $mime_var = 'mime';
    $image_response = @getimagesize($local_image_path);

    // Trying to get MIME type of the image
    if (!isset($image_response) || $image_response == false) {
        $image_response = @get_headers($remote_image_path, 1);
        if ($image_response !== false) {
            $mime_var = 'Content-Type';
        }
    }

    // If mime type successfully determined
    if (!empty($image_response[$mime_var])) {
        $type = $image_response[$mime_var];

        if (isset($mime_to_ext_mapping[$type])) {
            $ext = $mime_to_ext_mapping[$type];
        }
    }

    return compact('ext', 'type');
}

/**
 * Return Mime type and ext mapping array
 *
 * @since 3.5.3
 * @return array Mime type and ext mapping
 */
function wprss_ftp_get_mimetype_ext_mapping()
{

    // Get MIME to extension mappings ( from WordPress wp_check_filetype_and_ext() function )
    return apply_filters(
        'getimagesize_mimes_to_exts', array(
            'image/jpeg' => 'jpg',
            'image/png' => 'png',
            'image/gif' => 'gif',
            'image/bmp' => 'bmp',
            'image/tiff' => 'tif',
        )
    );
}
