<?php
/**
 * Plugin Name: WP Trip Summary
 * Author: Alexandru Boia
 * Author URI: http://alexboia.net
 * Version: 0.1.1
 * Description: Aids a travel blogger to add structured information about his tours (biking, hiking, train travels etc.)
 * License: New BSD License
 * Plugin URI: http://alexboia.net/abp01-trip-summary
 * Text Domain: abp01-trip-summary
 */

/**
 * Any file used by this plugin can be protected against direct browser access by checking this flag
 */
define('ABP01_LOADED', true);

/**
 * Current version
 */
define('ABP01_VERSION', '0.1.1');

define('ABP01_DISABLE_MINIFIED', false);

define('ABP01_PLUGIN_ROOT', dirname(__FILE__));
define('ABP01_LIB_DIR', ABP01_PLUGIN_ROOT . '/lib');

define('ABP01_ACTION_EDIT', 'abp01_edit_info');
define('ABP01_ACTION_CLEAR_INFO', 'abp01_clear_info');
define('ABP01_ACTION_CLEAR_TRACK', 'abp01_clear_track');
define('ABP01_ACTION_UPLOAD_TRACK', 'abp01_upload_track');
define('ABP01_ACTION_GET_TRACK', 'abp01_get_track');

define('ABP01_NONCE_TOUR_EDITOR', 'abp01.nonce.tourEditor');
define('ABP01_NONCE_GET_TRACK', 'abp01.nonce.getTrack');

define('ABP01_TRACK_UPLOAD_KEY', 'abp01_track_file');
define('ABP01_TRACK_UPLOAD_CHUNK_SIZE', 102400);
define('ABP01_TRACK_UPLOAD_MAX_FILE_SIZE', max(wp_max_upload_size(), 10485760));

/**
 * Initializes the autoloading process
 * @return void
 */
function abp01_init_autoloaders() {
    require_once ABP01_LIB_DIR . '/Autoloader.php';
    Abp01_Autoloader::init(ABP01_LIB_DIR);
}

function abp0_dump($var) {
    if (function_exists('xdebug_var_dump')) {
        xdebug_var_dump($var);
    } else {
        print '<pre>';
        var_dump($var);
        print '</pre>';
    }
}

/**
 * Appends the given error to the given message if WP_DEBUG is set to true; otherwise returns the original message
 * @param string $message
 * @param mixed $error
 * @return string The processed message
 */
function abp01_append_error($message, $error) {
    if (WP_DEBUG) {
        if ($error instanceof Exception) {
            $message .= sprintf(': %s (%s) in file %s line %d',
                $error->getMessage(),
                $error->getCode(),
                $error->getFile(),
                $error->getLine());
        } else if (!empty($e)) {
            $message .= ': ' . $e;
        }
    }
    return $message;
}

/**
 * Increase script execution limit and maximum memory limit
 * @return void
 */
function abp01_increase_limits() {
    if (function_exists('set_time_limit')) {
        @set_time_limit(5 * 60);
    }
    if (function_exists('ini_set')) {
        @ini_set('memory_limit', WP_MAX_MEMORY_LIMIT);
    }
}

/**
 * Creates a nonce to be used in the trip summary editor
 * @param int $postId The ID of the post for which the nonce will be generated
 * @return string The created nonce
 */
function abp01_create_edit_nonce($postId) {
    return wp_create_nonce(ABP01_NONCE_TOUR_EDITOR . ':' . $postId);
}

/**
 * Creates a nonce to be used when reading a trip's GPX track
 * @param int $postId The ID of the post for which the nonce will be generated
 * @return string The created nonce
 */
function abp01_create_get_track_nonce($postId) {
    return wp_create_nonce(ABP01_NONCE_GET_TRACK . ':' . $postId);
}

/**
 * Checks whether the current request has a valid nonce for the given post ID in the context of track editing
 * @param int $postId
 * @return bool True if valid, False otherwise
 */
function abp01_verify_edit_nonce($postId) {
    return check_ajax_referer(ABP01_NONCE_TOUR_EDITOR . ':' . $postId, 'abp01_nonce', false);
}

/**
 * Checks whether the current request has a valid nonce for the given post ID in the context of reading a trip's GPS track
 * @param int $postId
 * @return bool True if valid, False otherwise
 */
function abp01_verify_get_track_nonce($postId) {
    return check_ajax_referer(ABP01_NONCE_GET_TRACK . ':' . $postId, 'abp01_nonce_get', false);
}

/**
 * Encodes and outputs the given data as JSON and sets the appropriate headers
 * @param mixed $data The data to be encoded and sent to client
 * @return void
 */
function abp01_send_json($data) {
    $data = json_encode($data);
    header('Content-Type: application/json');
    if (extension_loaded('zlib') && function_exists('ini_set')) {
        @ini_set('zlib.output_compression', false);
        @ini_set('zlib.output_compression_level', 0);
        if (isset($_SERVER['HTTP_ACCEPT_ENCODING']) && stripos($_SERVER['HTTP_ACCEPT_ENCODING'], 'gzip') !== false) {
            header('Content-Encoding: gzip');
            $data = gzencode($data, 8, FORCE_GZIP);
        }
    }
    die($data);
}

/**
 * Conditionally escapes the given value for safe usage within an HTML document
 * @param mixed $value
 * @return mixed The encoded value
 */
function abp01_escape_value($value) {
    if (gettype($value) == 'string') {
        $value = esc_html($value);
    }
    return $value;
}

/**
 * Compute the path to the GPX file for the given track
 * @param Abp01_Route_Track $track
 * @return string The computed path
 */
function abp01_get_absolute_track_file_path(Abp01_Route_Track $track) {
    $file = $track->getFile();
    $parent = wp_normalize_path(realpath(dirname(__FILE__) . '/../'));
    return wp_normalize_path($parent . '/' . $file);
}

/**
 * Compute the full GPX file upload destination file path for the given post ID
 * @param int $postId
 * @return string The computed path
 */
function abp01_get_track_upload_destination($postId) {
    $env = Abp01_Env::getInstance();
    $fileName = sprintf('track-%d.gpx', $postId);
    $directory = wp_normalize_path($env->getDataDir() . '/storage');
    return wp_normalize_path($directory . '/' . $fileName);
}

/**
 * Determine the HTTP method used with the current request
 * @return string The current HTTP method or null if it cannot be determined
 */
function abp01_get_http_method() {
    return isset($_SERVER['REQUEST_METHOD']) ?
        strtolower($_SERVER['REQUEST_METHOD']) : null;
}

/**
 * Check whether the currently displayed screen is either the post editing or the post creation screen
 * @return bool
 */
function abp01_is_editing_post() {
    $currentPage = isset($GLOBALS['pagenow']) ? strtolower($GLOBALS['pagenow']) : null;
    return in_array($currentPage, array(
        'post-new.php',
        'post.php'));
}

/**
 * Tries to infer the current post ID from the current context. Several paths are tried:
 *  - The global $post object
 *  - The value of the _GET 'post' parameter
 *  - The value of the _GET 'abp01_postId' post parameter
 * @return mixed Int if a post ID is found, null otherwise
 */
function abp01_get_current_post_id() {
    $post = isset($GLOBALS['post']) ? $GLOBALS['post'] : null;
    if ($post && isset($post->ID)) {
        return intval($post->ID);
    } else if (isset($_GET['post'])) {
        return intval($_GET['post']);
    } else if (isset($_GET['abp01_postId'])) {
        return intval($_GET['abp01_postId']);
    }
    return null;
}

/**
 * Checks whether the current user can edit the current post's trip summary details.
 * If null is given, the function tries to infer the current post ID from the current context
 * @param mixed The current post as either a WP_Post instance, an integer or a null value
 * @return bool True if can edit, false otherwise
 */
function abp01_can_edit_trip_summary($post = null) {
    if ($post && is_object($post)) {
        $postId = intval($post->ID);
    } else if ($post && is_numeric($post)) {
        $postId = $post;
    } else {
        $postId = abp01_get_current_post_id();
    }
    return Abp01_Auth::getInstance()->canEditTourSummary($postId);
}

/**
 * Computes the GPX track cache file path for the given post ID
 * @param int $postId
 * @return string
 */
function abp01_get_track_cache_file_path($postId) {
    $path = sprintf('%s/data/cache/track-%d.cache', ABP01_PLUGIN_ROOT, $postId);
    return wp_normalize_path($path);
}

/**
 * Caches the serialized version of the given GPX track document for the given post ID
 * @param int $postId
 * @param Abp01_Route_Track_Document $route
 * @return void
 */
function abp01_save_cached_track($postId, Abp01_Route_Track_Document $route) {
    $path = abp01_get_track_cache_file_path($postId);
    file_put_contents($path, $route->serializeDocument(), LOCK_EX);
}

/**
 * Retrieves and deserializes the cached version of the GPX track document corresponding to the given post ID
 * @param int $postId
 * @return Abp01_Route_Track_Document The deserialized document
 */
function abp01_get_cached_track($postId) {
    $path = abp01_get_track_cache_file_path($postId);
    if (!is_readable($path)) {
        return null;
    }
    $contents = file_get_contents($path);
    return Abp01_Route_Track_Document::fromSerializedDocument($contents);
}

/**
 * Get the directories in which the frontend viewer templates should be searched.
 * @return stdClass
 */
function abp01_get_frontend_template_locations() {
    $env = Abp01_Env::getInstance();
    $dirs = new stdClass();
    $dirs->default = ABP01_PLUGIN_ROOT . '/views';
    $dirs->theme = $env->getCurrentThemeDir() . '/abp01-viewer';
    $dirs->themeUrl = $env->getCurrentThemeUrl() . '/abp01-viewer';
    return $dirs;
}

/**
 * Retrieve translations used in the main editor script
 * @return array key-value pairs where keys are javascript property names and values are the translation strings
 */
function abp01_get_main_admin_script_translations() {
    return array(
        'btnClearInfo' => __('Clear info', 'abp01-trip-summary'),
        'btnClearTrack' => __('Clear track', 'abp01-trip-summary'),

        'lblPluploadFileTypeSelector' => __('GPX files', 'abp01-trip-summary'),
        'lblGeneratingPreview' => __('Generating preview. Please wait...', 'abp01-trip-summary'),

        'lblTrackUploadingWait' => __('Uploading track', 'abp01-trip-summary'),
        'lblTrackUploaded' => __('The track has been uploaded and saved successfully', 'abp01-trip-summary'),

        'lblTypeBiking' => __('Biking', 'abp01-trip-summary'),
        'lblTypeHiking' => __('Hiking', 'abp01-trip-summary'),
        'lblTypeTrainRide' => __('Train ride', 'abp01-trip-summary'),

        'lblClearingTrackWait' => __('Clearing track. Please wait...', 'abp01-trip-summary'),
        'lblTrackClearOk' => __('The track has been successfully cleared', 'abp01-trip-summary'),
        'lblTrackClearFail' => __('The data could not be updated', 'abp01-trip-summary'),
        'lblTrackClearFailNetwork' => __('The data could not be updated due to a possible network error or an internal server issue', 'abp01-trip-summary'),

        'lblSavingDataWait' => __('Saving data. Please wait...', 'abp01-trip-summary'),
        'lblDataSaveOk' => __('The data has been saved', 'abp01-trip-summary'),
        'lblDataSaveFail' => __('The data could not be saved', 'abp01-trip-summary'),
        'lblDataSaveFailNetwork' => __('The data could not be saved due to a possible network error or an internal server issue', 'abp01-trip-summary'),

        'lblClearingInfoWait' => __('Clearing trip info. Please wait...', 'abp01-trip-summary'),
        'lblClearInfoOk' => __('The trip info has been cleared', 'abp01-trip-summary'),
        'lblClearInfoFail' => __('The trip info could not be cleared', 'abp01-trip-summary'),
        'lblClearInfoFailNetwork' => __('The trip info could not be cleared due to a possible network error or an internal server issue', 'abp01-trip-summary'),

        'errPluploadTooLarge' => __('The selected file is too large. Maximum allowed size is 10MB', 'abp01-trip-summary'),
        'errPluploadFileType' => __('The selected file type is not valid. Only GPX files are allowed', 'abp01-trip-summary'),
        'errPluploadIoError' => __('The file could not be read', 'abp01-trip-summary'),
        'errPluploadSecurityError' => __('The file could not be read', 'abp01-trip-summary'),
        'errPluploadInitError' => __('The uploader could not be initialized', 'abp01-trip-summary'),
        'errPluploadHttp' => __('The file could not be uploaded', 'abp01-trip-summary'),

        'errServerUploadFileType' => __('The selected file type is not valid. Only GPX files are allowed', 'abp01-trip-summary'),
        'errServerUploadTooLarge' => __('The selected file is too large. Maximum allowed size is 10MB', 'abp01-trip-summary'),
        'errServerUploadNoFile' => __('No file was uploaded', 'abp01-trip-summary'),
        'errServerUploadInternal' => __('The file could not be uploaded due to a possible internal server issue', 'abp01-trip-summary'),
        'errServerUploadFail' => __('The file could not be uploaded', 'abp01-trip-summary')
    );
}

/**
 * Retrieve translations used when installing the plug-in
 * @return array key-value pairs where keys are installation error codes and values are the translated strings
 */
function abp01_get_installation_error_translations() {
    $env = Abp01_Env::getInstance();
    load_plugin_textdomain('abp01-trip-summary', false, dirname(plugin_basename(__FILE__)) . '/lang/');
    return array(
        Abp01_Installer::INCOMPATIBLE_PHP_VERSION =>
            sprintf(__('Minimum required PHP version is %s', 'abp01-trip-summary'), $env->getRequiredPhpVersion()),
        Abp01_Installer::INCOMPATIBLE_WP_VERSION =>
            sprintf(__('Minimum required WP version is %s', 'abp01-trip-summary'), $env->getRequiredWpVersion()),
        Abp01_Installer::SUPPORT_LIBXML_NOT_FOUND =>
            __('LIBXML support was not found on your system', 'abp01-trip-summary'),
        Abp01_Installer::SUPPORT_MYSQLI_NOT_FOUND =>
            __('Mysqli extension was not found on your system or is not fully compatible', 'abp01-trip-summary'),
        Abp01_Installer::SUPPORT_MYSQL_SPATIAL_NOT_FOUND =>
            __('MySQL spatial support was not found on your system', 'abp01-trip-summary')
    );
}

/**
 * Retrieve translations used in the main frontend script
 * @return array key-value pairs where keys are javascript property names and values are the translation strings
 */
function abp01_get_main_frontend_translations() {
    return array();
}

/**
 * Render the button that opens the editor, in the post creation or post edit screen
 * @param stdClass $data Context data
 * @return void
 */
function abp01_render_techbox_button(stdClass $data) {
    require_once ABP01_PLUGIN_ROOT . '/views/techbox-button.php';
}

/**
 * Renders the editor in the post creation or post edit screen
 * @param stdClass $data The existing trip summary and context data
 * @return void
 */
function abp01_render_techbox_editor(stdClass $data) {
    require_once ABP01_PLUGIN_ROOT . '/views/helpers/controls.php';
    require_once ABP01_PLUGIN_ROOT . '/views/techbox-editor.php';
}

/**
 * Render the trip summary viewer
 * @param stdClass $data The trip summary and context data
 * @return void
 */
function abp01_render_techbox_frontend(stdClass $data) {
    $locations = abp01_get_frontend_template_locations();
    $themeHelpers = $locations->theme . '/helpers/controls.frontend.php';

    //if the custom viewer theme has overridden the helpers, include those helpers
    if (is_readable($themeHelpers)) {
        require_once $themeHelpers;
    }

    //include the default helpers - the actual functions will only be defined if no overrides are found
    require_once $locations->default . '/helpers/controls.frontend.php';

    //if the custom viewer theme has overridden the main view, include the override
    $themeViewer = $locations->theme . '/techbox-frontend.php';
    if (!is_readable($themeViewer)) {
        //otherwise, include the default main view
        require_once $locations->default . '/techbox-frontend.php';
    } else {
        require_once $themeViewer;
    }
}

/**
 * Render the trip summary teaser
 * @param stdClass $data The trip summary and context data
 */
function abp01_render_techbox_frontend_teaser(stdClass $data) {
    $locations = abp01_get_frontend_template_locations();
    $themeHelpers = $locations->theme . '/helpers/controls.frontend.php';

    //if the custom viewer theme has overridden the helpers, include those helpers
    if (is_readable($themeHelpers)) {
        require_once $themeHelpers;
    }

    //include the default helpers - the actual functions will only be defined if no overrides are found
    require_once $locations->default . '/helpers/controls.frontend.php';

    //if the custom viewer theme has overridden the teaser view, include the override
    $themeTeaser = $locations->theme . '/techbox-frontend-teaser.php';
    if (!is_readable($themeTeaser)) {
        //otherwise, include the default teaser view
        require_once $locations->default . '/techbox-frontend-teaser.php';
    } else {
        require_once $themeTeaser;
    }
}

/**
 * Handles plug-in activation
 * @return void
 */
function abp01_activate() {
    if (!current_user_can('activate_plugins')) {
        return;
    }
    $installer = new Abp01_Installer();
    $test = $installer->canBeInstalled();
    if ($test !== 0) {
        $errors = abp01_get_installation_error_translations();
        $message = isset($errors[$test]) ? $errors[$test] : __('The plugin cannot be installed on your system', 'abp01-trip-summary');
        deactivate_plugins(plugin_basename(__FIILE__));
        wp_die($message);
    } else if ($test === false) {
        wp_die(abp01_append_error('Failed plug-in compatibility check', $installer->getLastError()), 'Activation error');
    } else {
        if (!$installer->activate()) {
            wp_die(abp01_append_error('Could not activate plug-in', $installer->getLastError()), 'Activation error');
        }
    }
}

/**
 * Handles plug-in deactivation
 * @return void
 */
function abp01_deactivate() {
    if (!current_user_can('activate_plugins')) {
        return;
    }
    $installer = new Abp01_Installer();
    if (!$installer->deactivate()) {
        wp_die(abp01_append_error('Could not deactivate plug-in', $installer->getLastError()), 'Deactivation error');
    }
}

/**
 * Handles plug-in removal
 * @return void
 */
function abp01_uninstall() {
    if (!current_user_can('activate_plugins')) {
        return;
    }
    $installer = new Abp01_Installer();
    if (!$installer->uninstall()) {
        wp_die(abp01_append_error('Could not uninstall plug-in', $installer->getLastError()), 'Uninstall error');
    }
}

/**
 * Run plug-in init sequence
 */
function abp01_init_plugin() {
    load_plugin_textdomain('abp01-trip-summary', false, dirname(plugin_basename(__FILE__)) . '/lang/');
}

/**
 * Add the button that opens the editor, in the post creation or post edit screen.
 * For now, it simply requests the button be rendered, without any further actions being taken
 * @return void
 */
function abp01_add_editor_media_buttons() {
    abp01_render_techbox_button(new stdClass());
}

/**
 * Adds the editor in the post creation or post edit screen
 * @param object $post The current post being created or modified
 * @return void
 */
function abp01_add_admin_editor($post) {
    if (!abp01_can_edit_trip_summary($post)) {
        return;
    }

    $data = new stdClass();
    $lookup = Abp01_Lookup::getInstance();
    $manager = Abp01_Route_Manager::getInstance();

    //get the lookup data
    $data->difficultyLevels = $lookup->getDifficultyLevelOptions();
    $data->pathSurfaceTypes = $lookup->getPathSurfaceTypeOptions();
    $data->recommendedSeasons = $lookup->getRecommendedSeasonsOptions();
    $data->bikeTypes = $lookup->getBikeTypeOptions();
    $data->railroadOperators = $lookup->getRailroadOperatorOptions();
    $data->railroadLineStatuses = $lookup->getRailroadLineStatusOptions();
    $data->railroadLineTypes = $lookup->getRailroadLineTypeOptions();
    $data->railroadElectrification = $lookup->getRailroadElectrificationOptions();

    //current context information
    $data->postId = intval($post->ID);
    $data->hasTrack = $manager->hasRouteTrack($post->ID);

    $data->ajaxEditInfoAction = ABP01_ACTION_EDIT;
    $data->ajaxUploadTrackAction = ABP01_ACTION_UPLOAD_TRACK;
    $data->ajaxGetTrackAction = ABP01_ACTION_GET_TRACK;
    $data->ajaxClearTrackAction = ABP01_ACTION_CLEAR_TRACK;
    $data->ajaxClearInfoAction = ABP01_ACTION_CLEAR_INFO;

    $data->ajaxUrl = get_admin_url(null, 'admin-ajax.php', 'admin');
    $data->imgBaseUrl = plugins_url('media/img', __FILE__);
    $data->nonce = abp01_create_edit_nonce($data->postId);
    $data->nonceGet = abp01_create_get_track_nonce($data->postId);

    $data->flashUploaderUrl = includes_url('js/plupload/plupload.flash.swf');
    $data->xapUploaderUrl = includes_url('js/plupload/plupload.silverlight.xap');
    $data->uploadMaxFileSize = ABP01_TRACK_UPLOAD_MAX_FILE_SIZE;
    $data->uploadChunkSize = ABP01_TRACK_UPLOAD_CHUNK_SIZE;
    $data->uploadKey = ABP01_TRACK_UPLOAD_KEY;

    //the already existing values
    $info = $manager->getRouteInfo($data->postId);
    if ($info instanceof Abp01_Route_Info) {
        $tripData = $info->getData();
        foreach ($tripData as $key => $value) {
            if (is_array($value)) {
                $value = array_map('abp01_escape_value', $value);
            } else {
                $value = abp01_escape_value($value);
            }
            $tripData[$key] = $value;
        }
        $data->tourInfo = $tripData;
        $data->tourType = $info->getType();
    } else {
        $data->tourType = null;
        $data->tourInfo = null;
    }

    //finally, render the editor
    abp01_render_techbox_editor($data);
}

/**
 * Queues the appropriate styles with respect to the current admin screen
 * @return void
 */
function abp01_add_admin_styles() {
    if (abp01_is_editing_post() && abp01_can_edit_trip_summary(null)) {
        wp_enqueue_style('nprogress-css', plugins_url('media/js/3rdParty/nprogress/nprogress.css', __FILE__),
            array(), '0.1.6', 'all');
        wp_enqueue_style('jquery-icheck-css', plugins_url('media/js/3rdParty/icheck/skins/minimal/_all.css', __FILE__),
            array(), '1.0.2', 'all');
        wp_enqueue_style('leaflet-css', plugins_url('media/js/3rdParty/leaflet/leaflet.css', __FILE__),
            array(), '0.7.3', 'all');
        wp_enqueue_style('jquery-toastr-css', plugins_url('media/js/3rdParty/toastr/toastr.css', __FILE__),
            array(), '2.0.3', 'all');
        wp_enqueue_style('abp01-main-css', plugins_url('media/css/abp01-main.css', __FILE__),
            array(), '0.1', 'all');
    }
}

/**
 * Queues the appropriate frontend styles with respect to the current frontend screen
 * @return void
 */
function abp01_add_frontend_styles() {
    if (is_single()) {
        wp_enqueue_style('dashicons');
        wp_enqueue_style('nprogress-css', plugins_url('media/js/3rdParty/nprogress/nprogress.css', __FILE__),
            array(), '2.0.3', 'all');
        wp_enqueue_style('leaflet-css', plugins_url('media/js/3rdParty/leaflet/leaflet.css', __FILE__),
            array(), '0.7.3', 'all');

        $locations = abp01_get_frontend_template_locations();
        $cssRelativePath = 'media/css/abp01-frontend-main.css';
        $themeCssFile = $locations->theme . '/' . $cssRelativePath;

        //if the the theme has overridden the css file, include the override
        if (is_readable($themeCssFile)) {
            wp_enqueue_style('abp01-frontend-main-css', $locations->themeUrl . '/' . $cssRelativePath,
                array(), '0.1', 'all');
        } else {
            //otherwise, include the default css file
            wp_enqueue_style('abp01-frontend-main-css', plugins_url($cssRelativePath, __FILE__),
                array(), '0.1', 'all');
        }
    }
}

/**
 * Queues the appropriate scripts with respect to the current admin screen
 * @return void
 */
function abp01_add_admin_scripts() {
    if (abp01_is_editing_post() && abp01_can_edit_trip_summary(null)) {
        wp_enqueue_script('uri-js', plugins_url('media/js/3rdParty/uri/URI.js', __FILE__),
            array(), '1.14.1', false);
        wp_enqueue_script('jquery-icheck', plugins_url('media/js/3rdParty/icheck/icheck.js', __FILE__),
            array(), '1.0.2', false);
        wp_enqueue_script('jquery-blockui', plugins_url('media/js/3rdParty/jquery.blockUI.js', __FILE__),
            array(), '2.66', false);
        wp_enqueue_script('jquery-toastr', plugins_url('media/js/3rdParty/toastr/toastr.js', __FILE__),
            array(), '2.0.3', false);
        wp_enqueue_script('nprogress', plugins_url('media/js/3rdParty/nprogress/nprogress.js', __FILE__),
            array(), '0.2.0', false);
        wp_enqueue_script('jquery-easytabs', plugins_url('media/js/3rdParty/easytabs/jquery.easytabs.js', __FILE__),
            array(), '3.2.0', false);
        wp_enqueue_script('leaflfet', plugins_url('media/js/3rdParty/leaflet/leaflet-src.js', __FILE__),
            array(), '0.7.3', false);
        wp_enqueue_script('lodash', plugins_url('media/js/3rdParty/lodash/lodash.js', __FILE__),
            array(), '0.3.1', false);
        wp_enqueue_script('machina', plugins_url('media/js/3rdParty/machina/machina.js', __FILE__),
            array(), '0.3.1', false);
        wp_enqueue_script('kite-js', plugins_url('media/js/3rdParty/kite.js', __FILE__),
            array(), '1.0', false);

        wp_enqueue_script('abp01-map', plugins_url('media/js/abp01-map.js', __FILE__),
            array(), '0.1', false);
        wp_enqueue_script('abp01-progress-overlay', plugins_url('media/js/abp01-progress-overlay.js', __FILE__),
            array(), '0.1', false);
        wp_enqueue_script('abp01-main-admin', plugins_url('media/js/abp01-admin-main.js', __FILE__),
            array(), '0.1', false);

        wp_localize_script('abp01-main-admin', 'abp01MainL10n',
            abp01_get_main_admin_script_translations());
    }
}

/**
 * Queues the appropriate frontend scripts with respect to the current frontend screen
 * @return void
 */
function abp01_add_frontend_scripts() {
    if (is_single()) {
        wp_enqueue_script('jquery');
        wp_enqueue_script('jquery-visible', plugins_url('media/js/3rdParty/visible/jquery.visible.js', __FILE__),
            array(), '1.1.0', false);
        wp_enqueue_script('uri-js', plugins_url('media/js/3rdParty/uri/URI.js', __FILE__),
            array(), '1.14.1', false);
        wp_enqueue_script('jquery-easytabs', plugins_url('media/js/3rdParty/easytabs/jquery.easytabs.js', __FILE__),
            array(), '3.2.0', false);
        wp_enqueue_script('leaflfet', plugins_url('media/js/3rdParty/leaflet/leaflet-src.js', __FILE__),
            array(), '0.7.3', false);

        wp_enqueue_script('abp01-map', plugins_url('media/js/abp01-map.js', __FILE__),
            array(), '0.1', false);
        wp_enqueue_script('abp01-main-frontend', plugins_url('media/js/abp01-frontend-main.js', __FILE__),
            array(), '0.1', false);

        wp_localize_script('abp01-main-frontend', 'abp01FrontendL10n',
            abp01_get_main_frontend_translations());
    }
}

/**
 * Handles the data submitted by the user from the post editor. The result of this operation is sent back as JSON.
 * Execution halts if the given request context is not valid:
 *  - invalid HTTP method or...
 *  - no valid post ID or...
 *  - no valid nonce detected or...
 *  - the current user lacks proper capabilities
 * @return void
 */
function abp01_save_info() {
    //only HTTP post method is allowed
    if (abp01_get_http_method() != 'post') {
        die;
    }

    $postId = abp01_get_current_post_id();
    if (!abp01_can_edit_trip_summary($postId) || !abp01_verify_edit_nonce($postId)) {
        die;
    }

    $type = isset($_POST['type']) ? $_POST['type'] : null;
    if (!$type) {
        die;
    }

    $response = new stdClass();
    $manager = Abp01_Route_Manager::getInstance();
    $info = new Abp01_Route_Info($type);

    $response->success = false;
    $response->message = null;

    foreach ($info->getValidFieldNames() as $field) {
        if (isset($_POST[$field])) {
            $info->$field = $_POST[$field];
        }
    }

    if ($manager->saveRouteInfo($postId, get_current_user_id(), $info)) {
        $response->success = true;
    } else {
        $response->message = __('The data could not be saved due to a possible database error', 'abp01-trip-summary');
    }

    abp01_send_json($response);
}

/**
 * Filter function attached to the 'the_content' filter.
 * Its purpose is to render the trip summary viewer at the end of the post's content, but only within the post's page
 * The assumption is made that the wpautop filter has been previously removed from the filter chain
 * @param string $content The initial post content
 * @return string The filtered post content
 */
function abp01_get_info($content) {
    $content = wpautop($content);
    if (!is_single()) {
        return $content;
    }

    $postId = abp01_get_current_post_id();
    if (!$postId) {
        return $content;
    }

    $data = new stdClass();
    $lookup = Abp01_Lookup::getInstance();
    $manager = Abp01_Route_Manager::getInstance();
    $info = $manager->getRouteInfo($postId);

    $data->info = new stdClass();
    $data->info->exists = false;

    $data->track = new stdClass();
    $data->track->exists = $manager->hasRouteTrack($postId);

    //set the current trip summary information
    if ($info) {
        $data->info->exists = true;
        $data->info->isBikingTour = $info->isBikingTour();
        $data->info->isHikingTour = $info->isHikingTour();
        $data->info->isTrainRideTour = $info->isTrainRideTour();

        foreach ($info->getData() as $field => $value) {
            $lookupKey = $info->getLookupKey($field);
            if ($lookupKey) {
                if (is_array($value)) {
                    foreach ($value as $k => $v) {
                        $value[$k] = $lookup->lookup($lookupKey, $v);
                    }
                } else {
                    $value = $lookup->lookup($lookupKey, $value);
                }
            }
            $data->info->$field = $value;
        }
    }

    //current context information
    $data->postId = $postId;
    $data->nonceGet = abp01_create_get_track_nonce($postId);
    $data->ajaxUrl = get_admin_url(null, 'admin-ajax.php', 'admin');
    $data->ajaxGetTrackAction = ABP01_ACTION_GET_TRACK;
    $data->imgBaseUrl = plugins_url('media/img', __FILE__);

    //render the teaser and the viewer and attach the results to the post content
    if ($data->info->exists || $data->track->exists) {
        ob_start();
        abp01_render_techbox_frontend_teaser($data);
        $content = ob_get_clean() . $content;

        ob_start();
        abp01_render_techbox_frontend($data);
        $content = $content . ob_get_clean();
    }

    return $content;
}

/**
 * Handles the request for trip summary info removal.
 * Execution halts if the given request context is not valid:
 *  - invalid HTTP method or...
 *  - no valid post ID or...
 *  - no valid nonce detected or...
 *  - the current user lacks proper capabilities
 * @return void
 */
function abp01_remove_info() {
    //only HTTP POST method is allowed
    if (abp01_get_http_method() != 'post') {
        die;
    }

    $postId = abp01_get_current_post_id();
    if (!abp01_can_edit_trip_summary($postId) || !abp01_verify_edit_nonce($postId)) {
        die;
    }

    $response = new stdClass();
    $response->success = false;
    $response->message = null;

    $manager = Abp01_Route_Manager::getInstance();
    if (!$manager->deleteRouteInfo($postId)) {
        $response->message = __('The data could not be saved due to a possible database error', 'abp01-trip-summary');
    } else {
        $response->success = true;
    }

    abp01_send_json($response);
}

/**
 * Handles the GPX track upload requests. Chunked file uploads are supported.
 * After file transfer is completed, it is parsed and the route information is stored.
 * Execution halts if the given request context is not valid:
 *  - invalid HTTP method or...
 *  - no valid post ID or...
 *  - no valid nonce detected or...
 *  - the current user lacks proper capabilities
 * @return void
 */
function abp01_upload_track() {
    //only HTTP POST method is allowed
    if (abp01_get_http_method() != 'post') {
        die('1');
    }

    $postId = abp01_get_current_post_id();
    if (!abp01_can_edit_trip_summary($postId) || !abp01_verify_edit_nonce($postId)) {
        die('2');
    }

    //increase script execution limits: memory & cpu time
    abp01_increase_limits();

    $currentUserId = get_current_user_id();
    $destination = abp01_get_track_upload_destination($postId);

    if (ABP01_TRACK_UPLOAD_CHUNK_SIZE > 0) {
        $chunk = isset($_REQUEST['chunk']) ? intval($_REQUEST['chunk']) : 0;
        $chunks = isset($_REQUEST['chunks']) ? intval($_REQUEST['chunks']) : 0;
    } else {
        $chunk = $chunks = 0;
    }

    //create and configure the uploader
    $uploader = new Abp01_Uploader(ABP01_TRACK_UPLOAD_KEY, $destination, array(
        'chunk' => $chunk,
        'chunks' => $chunks,
        'chunkSize' => ABP01_TRACK_UPLOAD_CHUNK_SIZE,
        'maxFileSize' => ABP01_TRACK_UPLOAD_MAX_FILE_SIZE,
        'allowedFileTypes' => array(
            'application/gpx',
            'application/x-gpx+xml',
            'application/xml-gpx',
            'application/xml',
            'text/xml'
        )
    ));
    $uploader->setCustomValidator(array(new Abp01_Validate_GpxDocument(), 'validate'));

    $result = new stdClass();
    $result->status = $uploader->receive();
    $result->ready = $uploader->isReady();

    //if the upload has completed, then process the newly uploaded file and save the track information
    if ($result->ready) {
        $route = file_get_contents($destination);
        if (!empty($route)) {
            $parser = new Abp01_Route_Track_GpxDocumentParser();
            $route = $parser->parse($route);
            if ($route && !$parser->hasErrors()) {
                $manager = Abp01_Route_Manager::getInstance();
                $destination = plugin_basename($destination);

                $track = new Abp01_Route_Track($destination, $route->getBounds(),
                    $route->minAlt,
                    $route->maxAlt);

                if (!$manager->saveRouteTrack($postId, $currentUserId, $track)) {
                    $result->status = Abp01_Uploader::UPLOAD_INTERNAL_ERROR;
                }
            } else {
                $result->status = Abp01_Uploader::UPLOAD_NOT_VALID;
            }
        } else {
            $result->status = Abp01_Uploader::UPLOAD_NOT_VALID;
        }
    }

    abp01_send_json($result);
}

/**
 * Handles the track retrieval request. Script execution halts if the request context is not valid:
 *  - invalid HTTP method or...
 *  - invalid nonce provided
 * @return void
 */
function abp01_get_track() {
    //only HTTP GET method is allowed
    if (abp01_get_http_method() != 'get') {
        die;
    }

    $postId = abp01_get_current_post_id();
    if (!abp01_verify_get_track_nonce($postId)) {
        die;
    }

    //increase script execution limits: memory & cpu time
    abp01_increase_limits();

    $response = new stdClass();
    $response->success = false;
    $response->message = null;
    $response->track = null;

    $route = abp01_get_cached_track($postId);
    if (!($route instanceof Abp01_Route_Track_Document)) {
        $manager = Abp01_Route_Manager::getInstance();
        $track = $manager->getRouteTrack($postId);
        if ($track) {
            $file = abp01_get_absolute_track_file_path($track);
            if (is_readable($file)) {
                $parser = new Abp01_Route_Track_GpxDocumentParser();
                $route = $parser->parse(file_get_contents($file));
                if ($route) {
                    $route = $route->simplify(0.01);
                    $response->success = true;
                    abp01_save_cached_track($postId, $route);
                } else {
                    $response->message = __('Track file could not be parsed', 'abp01-trip-summary');
                }
            } else {
                $response->message = __('Track file not found or is not readable', 'abp01-trip-summary');
            }
        }
    } else {
        $response->success = true;
    }

    if ($response->success) {
        $response->track = new stdClass();
        $response->track->route = $route;
        $response->track->bounds = $route->getBounds();
        $response->track->start = $route->getStartPoint();
        $response->track->end = $route->getEndPoint();
    }

    abp01_send_json($response);
}

/**
 * Handles the GPX track removal request. The result of this operation is sent back as JSON
 * Execution halts if the given request context is not valid:
 *  - invalid HTTP method or...
 *  - no valid post ID or...
 *  - no valid nonce detected or...
 *  - the current user lacks proper capabilities
 * @return void
 */
function abp01_remove_track() {
    //only HTTP post method is allowed
    if (abp01_get_http_method() != 'post') {
        die;
    }

    $postId = abp01_get_current_post_id();
    if (!abp01_verify_edit_nonce($postId) || !abp01_can_edit_trip_summary($postId)) {
        die;
    }

    $response = new stdClass();
    $response->success = false;
    $response->message = null;

    $manager = Abp01_Route_Manager::getInstance();
    if ($manager->deleteRouteTrack($postId)) {
        //delete track file
        $trackFile = abp01_get_track_upload_destination($postId);
        if (file_exists($trackFile)) {
            @unlink($trackFile);
        }

        //delete cached track file
        $cacheFile = abp01_get_track_cache_file_path($postId);
        if (file_exists($cacheFile)) {
            @unlink($cacheFile);
        }

        $response->success = true;
    } else {
        $response->message = __('The data could not be updated due to a possible database error', 'abp01-trip-summary');
    }

    abp01_send_json($response);
}

abp01_init_autoloaders();

if (function_exists('register_activation_hook')) {
    register_activation_hook(__FILE__, 'abp01_activate');
}

if (function_exists('register_deactivation_hook')) {
    register_deactivation_hook(__FILE__, 'abp01_deactivate');
}

if (function_exists('register_uninstall_hook')) {
    register_uninstall_hook(__FILE__, 'abp01_uninstall');
}

if (function_exists('add_action')) {
    add_action('media_buttons', 'abp01_add_editor_media_buttons', 20);
    add_action('admin_enqueue_scripts', 'abp01_add_admin_styles');
    add_action('admin_enqueue_scripts', 'abp01_add_admin_scripts');
    add_action('edit_form_after_editor', 'abp01_add_admin_editor');

    add_action('wp_ajax_' . ABP01_ACTION_EDIT, 'abp01_save_info');
    add_action('wp_ajax_' . ABP01_ACTION_UPLOAD_TRACK, 'abp01_upload_track');
    add_action('wp_ajax_' . ABP01_ACTION_CLEAR_TRACK, 'abp01_remove_track');
    add_action('wp_ajax_' . ABP01_ACTION_CLEAR_INFO, 'abp01_remove_info');

    add_action('wp_ajax_' . ABP01_ACTION_GET_TRACK, 'abp01_get_track');
    add_action('wp_ajax_nopriv_' . ABP01_ACTION_GET_TRACK, 'abp01_get_track');

    add_action('wp_enqueue_scripts', 'abp01_add_frontend_styles');
    add_action('wp_enqueue_scripts', 'abp01_add_frontend_scripts');

    remove_filter('the_content', 'wpautop');
    add_filter('the_content', 'abp01_get_info', 0);

    add_action('plugins_loaded', 'abp01_init_plugin');
}