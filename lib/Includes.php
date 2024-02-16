<?php
/**
 * Copyright (c) 2014-2024 Alexandru Boia and Contributors
 *
 * Redistribution and use in source and binary forms, with or without modification, 
 * are permitted provided that the following conditions are met:
 * 
 *	1. Redistributions of source code must retain the above copyright notice, 
 *		this list of conditions and the following disclaimer.
 *
 * 	2. Redistributions in binary form must reproduce the above copyright notice, 
 *		this list of conditions and the following disclaimer in the documentation 
 *		and/or other materials provided with the distribution.
 *
 *	3. Neither the name of the copyright holder nor the names of its contributors 
 *		may be used to endorse or promote products derived from this software without 
 *		specific prior written permission.
 *
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS" 
 * AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, 
 * THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE ARE DISCLAIMED. 
 * IN NO EVENT SHALL THE COPYRIGHT HOLDER OR CONTRIBUTORS BE LIABLE FOR ANY 
 * DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES 
 * (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; 
 * LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) 
 * HOWEVER CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, 
 * OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE) 
 * ARISING IN ANY WAY OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED 
 * OF THE POSSIBILITY OF SUCH DAMAGE.
 */

if (!defined('ABP01_LOADED') || !ABP01_LOADED) {
	exit ;
}

class Abp01_Includes {
	const JS_MOXIE = 'moxiejs';

	const JS_PLUPLOAD = 'plupload';
	
	const JS_JQUERY = 'jquery';

	const JS_URI_JS = 'uri-js';

	const JS_JQUERY_UI_DATEPICKER = 'jquery-ui-datepicker';

	const JS_WP_COLOR_PICKER = 'wp-color-picker';

	const JS_JQUERY_VISIBLE = 'jquery-visible';

	const JS_JQUERY_BLOCKUI = 'jquery-blockui';

	const JS_JQUERY_TOASTR = 'jquery-toastr';

	const JS_NPROGRESS = 'nprogress';

	const JS_JQUERY_EASYTABS = 'jquery-easytabs';

	const JS_LEAFLET = 'abp01-leaflet';

	const JS_LEAFLET_UTILITY = 'abp01-leaflet-map-utility';

	const JS_LEAFLET_MAGNIFYING_GLASS = 'abp01-leaflet-magnifyingglass';

	const JS_LEAFLET_MAGNIFYING_GLASS_BUTTON = 'abp01-leaflet-magnifyingglass-button';

	const JS_LEAFLET_FULLSCREEN = 'abp01-leaflet-fullscreen';

	const JS_LEAFLET_NOCONFLICT = 'abp01-leaflet-noconflict';
	
	const JS_LEAFLET_ICON_BUTTON = 'abp01-leaflet-icon-button';

	const JS_LEAFLET_MIN_MAX_ALTITUDE_BOX = 'abp01-min-max-altitude-box';

	const JS_LEAFLET_ALTITUDE_PROFILE = 'abp01-altitude-profile';

	const JS_LEAFLET_RECENTER_MAP = 'abp01-recenter-map';

	const JS_LODASH = 'lodash';

	const JS_MACHINA = 'machina';

	const JS_KITE_JS = 'kite-js';

	const JS_TIPPED_JS = 'tipped-js';

	const JS_CHART_JS = 'abp01-chart-js';

	const JS_BOOTSTRAP = 'abp01-bootstrap';

	const JS_ABP01_MAP = 'abp01-map';

	const JS_ABP01_COMMON = 'abp01-common-js';

	const JS_ABP01_PROGRESS_MODAL = 'abp01-progress-modal';

	const JS_ABP01_CONFIRM_DIALOG_MODAL = 'abp01-confirm-dialog-modal';

	const JS_ABP01_ALERT_INLINE = 'abp01-alert-inline';

	const JS_ABP01_PROGRESS_OVERLAY = 'abp01-progress-overlay';

	const JS_ABP01_OPERATION_MESSAGE = 'abp01-operation-message-js';

	const JS_ABP01_NUMERIC_STEPPER = 'abp01-numeric-stepper-js';

	const JS_ABP01_HELP_IMAGE_GALLERY = 'abp01-help-image-gallery-js';

	const JS_ABP01_ADMIN_MAIN = 'abp01-main-admin';

	const JS_ABP01_FRONTEND_MAIN = 'abp01-main-frontend';

	const JS_ABP01_ADMIN_SETTINGS = 'abp01-settings-admin';

	const JS_ABP01_ADMIN_LOOKUP_MGMT = 'abp01-admin-lookup-management';

	const JS_ABP01_ADMIN_MAINTENANCE = 'abp01-admin-maintenance';

	const JS_ABP01_ADMIN_HELP = 'abp01-admin-help';

	const JS_ABP01_VIEWER_SHORTCODE_BLOCK = 'abp01-viewer-short-code-block';

	const JS_ABP01_CLASSIC_EDITOR_VIEWER_SHORTCODE_PLUGIN = 'abp01-classic-editor-viewer-shortcode-plugin';
	
	const JS_ABP01_LISTING_AUDIT_LOG = 'abp01-listing-audit-log';

	const JS_ABP01_ADMIN_LOG_ENTRIES = 'abp01-admin-log-entries';

	const JS_ABP01_ADMIN_SYSTEM_LOGS = 'abp01-admin-system-logs';

	const JS_SYSTEM_THICKBOX = 'thickbox';

	const JS_SELECT2 = 'select2-js';

	const STYLE_WP_COLOR_PICKER = 'wp-color-picker';

	const STYLE_DASHICONS = 'dashicons';

	const STYLE_NPROGRESS = 'nprogress-css';

	const STYLE_SELECT2 = 'select2-css';

	const STYLE_LEAFLET = 'leaflet-css';

	const STYLE_LEAFLET_MAGNIFYING_GLASS = 'leaflet-magnifyingglass-css';

	const STYLE_LEAFLET_MAGNIFYING_GLASS_BUTTON = 'leaflet-magnifyingglass-button-css';

	const STYLE_LEAFLET_FULLSCREEN = 'leaflet-fullscreen-css';

	const STYLE_FRONTEND_MAIN = 'abp01-frontend-main-css';

	const STYLE_JQUERY_TOASTR = 'jquery-toastr-css';

	const STYLE_BOOTSTRAP = 'abp01-bootstrap-css';

	const STYLE_TIPPED_JS = 'tipped-js-css';

	const STYLE_CHART_JS = 'abp01-chart-js-css';

	const STYLE_ADMIN_SYSTEM_LOGS = 'abp01-admin-system-logs-css';

	const STYLE_ABP01_NUMERIC_STEPPER = 'abp01-numeric-stepper-css';

	const STYLE_ABP01_HELP_IMAGE_GALLERY = 'abp01-help-image-gallery-css';

	const STYLE_ADMIN_COMMON = 'abp01-admin-common-css';

	const STYLE_ADMIN_MAIN = 'abp01-main-css';

	const STYLE_ADMIN_SETTINGS = 'abp01-settings-css';

	const STYLE_ADMIN_LOOKUP_MANAGEMENT = 'abp01-lookup-management-css';
	
	const STYLE_ADMIN_HELP = 'abp01-help-css';

	const STYLE_ADMIN_ABOUT = 'abp01-about-css';

	const STYLE_ADMIN_MAINTENANCE = 'abp01-maintenance-css';

	const STYLE_ADMIN_POSTS_LISTING = 'abp01-admin-posts-listing-css';

	const STYLE_ADMIN_AUDIT_LOG = 'abp01-admin-audit-log-css';

	const STYLE_ADMIN_LISTING_AUDIT_LOG = 'abp01-admin-listing-audit-log-css';

	const STYLE_ADMIN_LOG_ENTRIES = 'abp01-admin-log-entries-css';

	const STYLE_SYSTEM_THICKBOX = 'thickbox';

	const STYLE_FRONTEND_LOG_ENTRIES = 'abp01-frontend-log-entries-css';

	const STYLE_FRONTEND_MAIN_TWENTY_TEN = 'abp01-frontend-main-twentyten-css';

	const STYLE_FRONTEND_MAIN_TWENTY_ELEVEN = 'abp01-frontend-main-twentyeleven-css';

	const STYLE_FRONTEND_MAIN_TWENTY_THIRTEEN = 'abp01-frontend-main-twentythirteen-css';

	const STYLE_FRONTEND_MAIN_TWENTY_FOURTEEN = 'abp01-frontend-main-twentyfourteen-css';

	const STYLE_FRONTEND_MAIN_TWENTY_FIFTEEN = 'abp01-frontend-main-twentyfifteen-css';

	const STYLE_FRONTEND_MAIN_TWENTY_SIXTEEN = 'abp01-frontend-main-twentysixteen-css';

	const STYLE_FRONTEND_MAIN_TWENTY_SEVENTEEN = 'abp01-frontend-main-twentyseventeen-css';

	const STYLE_FRONTEND_MAIN_TWENTY_NINETEEN = 'abp01-frontend-main-twentynineteen-css';

	/**
	 * @var Abp01_Includes_Manager
	 */
	private static $_includesManager = null;

	private static $_scripts = array(
		self::JS_URI_JS => array(
			'path' => 'media/js/3rdParty/uri/URI.js', 
			'version' => '1.19.2'
		), 
		self::JS_JQUERY_VISIBLE => array(
			'path' => 'media/js/3rdParty/visible/jquery.visible.js', 
			'version' => '1.1.0',
			'deps' => array(
				self::JS_JQUERY
			)
		), 
		self::JS_JQUERY_BLOCKUI => array(
			'path' => 'media/js/3rdParty/jquery.blockUI.js', 
			'version' => '2.66',
			'deps' => array(
				self::JS_JQUERY
			)
		), 
		self::JS_JQUERY_TOASTR => array(
			'path' => 'media/js/3rdParty/toastr/toastr.js', 
			'version' => '2.1.4',
			'deps' => array(
				self::JS_JQUERY
			)
		), 
		self::JS_NPROGRESS => array(
			'path' => 'media/js/3rdParty/nprogress/nprogress.js', 
			'version' => '0.2.0'
		), 
		self::JS_JQUERY_EASYTABS => array(
			'path' => 'media/js/3rdParty/easytabs/jquery.easytabs.js', 
			'version' => '3.2.0',
			'deps' => array(
				self::JS_JQUERY
			)
		), 
		self::JS_SELECT2 => array(
			'path' => 'media/js/3rdParty/select2/js/select2.js',
			'version' => '4.0.13',
			'deps' => array(
				self::JS_JQUERY
			)
		),
		self::JS_BOOTSTRAP => array(
			'path' => 'media/bootstrap/js/bootstrap.bundle.min.js',
			'version' => '5.3.2',
			'deps' => array()
		),
		self::JS_LEAFLET_NOCONFLICT => array(
			'path' => 'media/js/abp01-leaflet-noconflict.js', 
			'version' => ABP01_VERSION
		),
		self::JS_LEAFLET => array(
			'path' => 'media/js/3rdParty/leaflet/leaflet-src.js', 
			'version' => '1.6.0',
			//see https://leafletjs.com/reference-1.6.0.html#noconflict
			//see https://github.com/Leaflet/Leaflet/issues/6703
			'inline-setup' => 'window.' . ABP01_WRAPPED_LEAFLET_CONTEXT . ' = window.abp01NoConflictLeaflet();',
			'deps' => array(
				self::JS_LEAFLET_NOCONFLICT
			)
		), 
		self::JS_LEAFLET_UTILITY => array(
			'path' => 'media/js/abp01-map-utility.js',
			'version' => ABP01_VERSION,
			'is-leaflet-plugin' => true,
			'needs-wrap' => false,
			'deps' => array(
				self::JS_LEAFLET
			)
		),
		self::JS_LEAFLET_MAGNIFYING_GLASS => array(
			'path' => 'media/js/3rdParty/leaflet-plugins/leaflet-magnifyingglass/leaflet.magnifyingglass.js', 
			'version' => '1.0.6',
			'is-leaflet-plugin' => true,
			'needs-wrap' => true,
			'deps' => array(
				self::JS_LEAFLET
			)
		), 
		self::JS_LEAFLET_MAGNIFYING_GLASS_BUTTON => array(
			'path' => 'media/js/3rdParty/leaflet-plugins/leaflet-magnifyingglass/leaflet.magnifyingglass.button.js', 
			'version' => '1.0.6',
			'is-leaflet-plugin' => true,
			'needs-wrap' => true,
			'deps' => array(
				self::JS_LEAFLET
			)
		), 
		self::JS_LEAFLET_FULLSCREEN => array(
			'path' => 'media/js/3rdParty/leaflet-plugins/leaflet-fullscreen/leaflet.fullscreen.js', 
			'version' => '1.0.2',
			'is-leaflet-plugin' => true,
			'needs-wrap' => true,
			'deps' => array(
				self::JS_LEAFLET
			)
		), 
		self::JS_LEAFLET_ICON_BUTTON => array(
			'path' => 'media/js/abp01-icon-button.js',
			'version' => ABP01_VERSION,
			'is-leaflet-plugin' => true,
			'needs-wrap' => false,
			'deps' => array(
				self::JS_LEAFLET
			)
		),
		self::JS_LEAFLET_MIN_MAX_ALTITUDE_BOX => array(
			'path' => 'media/js/abp01-min-max-altitude-box.js',
			'version' => ABP01_VERSION,
			'is-leaflet-plugin' => true,
			'needs-wrap' => false,
			'deps' => array(
				self::JS_LEAFLET
			)
		),
		self::JS_LEAFLET_ALTITUDE_PROFILE => array(
			'path' => 'media/js/abp01-altitude-profile.js',
			'version' => ABP01_VERSION,
			'is-leaflet-plugin' => true,
			'needs-wrap' => false,
			'deps' => array(
				self::JS_LEAFLET,
				self::JS_LEAFLET_UTILITY,
				self::JS_CHART_JS
			)
		),
		self::JS_LEAFLET_RECENTER_MAP => array(
			'path' => 'media/js/abp01-recenter-map.js',
			'version' => ABP01_VERSION,
			'is-leaflet-plugin' => true,
			'needs-wrap' => false,
			'deps' => array(
				self::JS_LEAFLET,
				self::JS_LEAFLET_UTILITY
			)
		),
		self::JS_MACHINA => array(
			'path' => 'media/js/3rdParty/machina/machina.js', 
			'version' => '0.3.1',
			'deps' => array(
				self::JS_LODASH
			)
		), 
		self::JS_KITE_JS => array(
			'path' => 'media/js/3rdParty/kite.js', 
			'version' => '1.0'
		), 
		self::JS_TIPPED_JS => array(
			'path' => 'media/js/3rdParty/tipped/js/tipped.js', 
			'version' => '4.7.0'
		),
		self::JS_CHART_JS => array(
			'path' => 'media/js/3rdParty/chartjs/Chart.js', 
			'version' => '2.9.3'
		),

		self::JS_ABP01_COMMON => array(
			'path' => 'media/js/abp01-common.js', 
			'version' => ABP01_VERSION,
			'deps' => array(
				self::JS_JQUERY
			)
		),
		self::JS_ABP01_OPERATION_MESSAGE => array(
			'path' => 'media/js/abp01-operation-message.js', 
			'version' => ABP01_VERSION,
			'deps' => array(
				self::JS_JQUERY
			)
		),
		self::JS_ABP01_NUMERIC_STEPPER => array(
			'path' => 'media/js/abp01-numeric-stepper.js', 
			'version' => ABP01_VERSION,
			'deps' => array(
				self::JS_JQUERY
			)
		),
		self::JS_ABP01_HELP_IMAGE_GALLERY => array(
			'path' => 'media/js/abp01-admin-help-image-gallery.js', 
			'version' => ABP01_VERSION,
			'deps' => array(
				self::JS_JQUERY
			)
		),
		self::JS_ABP01_MAP => array(
			'path' => 'media/js/abp01-map.js', 
			'version' => ABP01_VERSION,
			'deps' => array(
				self::JS_LEAFLET,
				self::JS_JQUERY
			)
		), 
		self::JS_ABP01_PROGRESS_OVERLAY => array(
			'path' => 'media/js/abp01-progress-overlay.js', 
			'version' => ABP01_VERSION,
			'deps' => array(
				self::JS_JQUERY,
				self::JS_JQUERY_BLOCKUI,
				self::JS_MACHINA,
				self::JS_NPROGRESS,
				self::JS_KITE_JS
			)
		), 

		self::JS_ABP01_PROGRESS_MODAL => array(
			'path' => 'media/js/components/abp01-progress-modal.js', 
			'version' => ABP01_VERSION,
			'deps' => array(
				self::JS_JQUERY,
				self::JS_JQUERY_BLOCKUI,
				self::JS_BOOTSTRAP,
				self::JS_ABP01_COMMON
			)
		), 

		self::JS_ABP01_CONFIRM_DIALOG_MODAL => array(
			'path' => 'media/js/components/abp01-confirm-dialog-modal.js', 
			'version' => ABP01_VERSION,
			'deps' => array(
				self::JS_JQUERY,
				self::JS_BOOTSTRAP,
				self::JS_ABP01_COMMON
			)
		), 

		self::JS_ABP01_ALERT_INLINE => array(
			'path' => 'media/js/components/abp01-alert-inline.js', 
			'version' => ABP01_VERSION,
			'deps' => array(
				self::JS_JQUERY,
				self::JS_BOOTSTRAP
			)
		), 

		self::JS_ABP01_ADMIN_MAIN => array(
			'path' => 'media/js/abp01-admin-main.js', 
			'version' => ABP01_VERSION,
			'deps' => array(
				self::JS_JQUERY,
				self::JS_JQUERY_EASYTABS,
				self::JS_JQUERY_BLOCKUI,
				self::JS_JQUERY_TOASTR,
				self::JS_MOXIE,
				self::JS_PLUPLOAD,
				self::JS_SELECT2,
				self::JS_KITE_JS,
				self::JS_URI_JS,
				self::JS_LEAFLET,
				self::JS_LEAFLET_RECENTER_MAP,
				self::JS_TIPPED_JS,
				self::JS_ABP01_COMMON,
				self::JS_ABP01_PROGRESS_OVERLAY,
				self::JS_ABP01_MAP
			)
		), 

		self::JS_ABP01_ADMIN_LOG_ENTRIES => array(
			'path' => 'media/js/abp01-admin-log-entries.js', 
			'version' => ABP01_VERSION,
			'deps' => array(
				self::JS_JQUERY,
				self::JS_JQUERY_BLOCKUI,
				self::JS_JQUERY_TOASTR,
				self::JS_JQUERY_UI_DATEPICKER,
				self::JS_URI_JS,
				self::JS_TIPPED_JS,
				self::JS_KITE_JS,
				self::JS_LODASH,
				self::JS_ABP01_COMMON,
				self::JS_ABP01_PROGRESS_OVERLAY,
				self::JS_ABP01_NUMERIC_STEPPER
			)
		),

		self::JS_ABP01_FRONTEND_MAIN => array(
			'path' => 'media/js/abp01-frontend-main.js', 
			'version' => ABP01_VERSION,
			'deps' => array(
				self::JS_JQUERY,
				self::JS_JQUERY_EASYTABS,
				self::JS_JQUERY_VISIBLE,
				self::JS_URI_JS,
				self::JS_LEAFLET_FULLSCREEN,
				self::JS_LEAFLET_MAGNIFYING_GLASS,
				self::JS_LEAFLET_MAGNIFYING_GLASS_BUTTON,
				self::JS_LEAFLET_ICON_BUTTON,
				self::JS_LEAFLET_MIN_MAX_ALTITUDE_BOX,

				array(
					'handle' => self::JS_CHART_JS,
					'if' => array(__CLASS__, '_hasAltitudeProfile')
				),

				array(
					'handle' => self::JS_LEAFLET_ALTITUDE_PROFILE,
					'if' => array(__CLASS__, '_hasAltitudeProfile')
				),

				self::JS_LEAFLET_RECENTER_MAP,
				self::JS_ABP01_MAP
			)
		), 
		self::JS_ABP01_ADMIN_SETTINGS => array(
			'path' => 'media/js/abp01-admin-settings.js', 
			'version' => ABP01_VERSION,
			'deps' => array(
				self::JS_JQUERY,
				self::JS_WP_COLOR_PICKER,
				self::JS_JQUERY_BLOCKUI,
				self::JS_URI_JS,
				self::JS_TIPPED_JS,
				self::JS_ABP01_COMMON,
				self::JS_ABP01_PROGRESS_OVERLAY,
				self::JS_ABP01_NUMERIC_STEPPER,
				self::JS_ABP01_OPERATION_MESSAGE
			)
		),
		self::JS_ABP01_ADMIN_LOOKUP_MGMT => array(
			'path' => 'media/js/abp01-admin-lookup-management.js',
			'version' => ABP01_VERSION,
			'deps' => array(
				self::JS_SYSTEM_THICKBOX,
				self::JS_JQUERY,
				self::JS_JQUERY_BLOCKUI,
				self::JS_KITE_JS,
				self::JS_URI_JS,
				self::JS_ABP01_COMMON,
				self::JS_ABP01_PROGRESS_OVERLAY,
				self::JS_ABP01_OPERATION_MESSAGE
			)
		),
		self::JS_ABP01_ADMIN_MAINTENANCE => array(
			'path' => 'media/js/abp01-admin-maintenance.js',
			'version' => ABP01_VERSION,
			'deps' => array(
				self::JS_JQUERY,
				self::JS_JQUERY_BLOCKUI,
				self::JS_KITE_JS,
				self::JS_URI_JS,
				self::JS_ABP01_COMMON,
				self::JS_ABP01_PROGRESS_OVERLAY,
				self::JS_ABP01_OPERATION_MESSAGE
			)
		),
		self::JS_ABP01_ADMIN_SYSTEM_LOGS => array(
			'path' => 'media/js/admin/abp01-admin-system-logs.js',
			'version' => ABP01_VERSION,
			'deps' => array(
				self::JS_JQUERY,
				self::JS_BOOTSTRAP,
				self::JS_URI_JS,
				self::JS_ABP01_COMMON,
				self::JS_ABP01_OPERATION_MESSAGE,
				self::JS_ABP01_PROGRESS_MODAL,
				self::JS_ABP01_CONFIRM_DIALOG_MODAL,
				self::JS_ABP01_ALERT_INLINE
			)
		),
		self::JS_ABP01_ADMIN_HELP => array(
			'path' => 'media/js/abp01-admin-help.js',
			'version' => ABP01_VERSION,
			'deps' => array(
				self::JS_JQUERY,
				self::JS_JQUERY_BLOCKUI,
				self::JS_URI_JS,
				self::JS_ABP01_COMMON,
				self::JS_ABP01_PROGRESS_OVERLAY,
				self::JS_ABP01_HELP_IMAGE_GALLERY,
				self::JS_ABP01_OPERATION_MESSAGE
			)
		),
		self::JS_ABP01_LISTING_AUDIT_LOG => array(
			'path' => 'media/js/abp01-admin-listing-audit-log.js',
			'version' => ABP01_VERSION,
			'deps' => array(
				self::JS_JQUERY,
				self::JS_JQUERY_BLOCKUI,
				self::JS_URI_JS,
				self::JS_ABP01_COMMON,
				self::JS_ABP01_PROGRESS_OVERLAY
			)
		),
		self::JS_ABP01_VIEWER_SHORTCODE_BLOCK => array(
			'path' => 'media/js/abp01-block-editor-shortcode/block.js',
			'version' => ABP01_VERSION,
			'deps' => array(
				'wp-blocks', 
				'wp-element',
				'wp-data',
				'wp-dom-ready',
				'wp-edit-post'
			)
		),
		self::JS_ABP01_CLASSIC_EDITOR_VIEWER_SHORTCODE_PLUGIN => array(
			'path' => 'media/js/abp01-classic-editor-shortcode/plugin.js',
			'version' => ABP01_VERSION
		)
	);

	private static $_styles = array(
		self::STYLE_NPROGRESS => array(
			'path' => 'media/js/3rdParty/nprogress/nprogress.css', 
			'version' => '0.2.0'
		), 
		self::STYLE_SELECT2  => array(
			'path' => 'media/js/3rdParty/select2/css/select2.css',
			'version' => '4.0.13'
		),
		self::STYLE_LEAFLET => array(
			'path' => 'media/js/3rdParty/leaflet/leaflet.css', 
			'version' => '1.6.0'
		), 
		self::STYLE_LEAFLET_MAGNIFYING_GLASS => array(
			'path' => 'media/js/3rdParty/leaflet-plugins/leaflet-magnifyingglass/leaflet.magnifyingglass.css', 
			'version' => '0.2'
		), 
		self::STYLE_LEAFLET_MAGNIFYING_GLASS_BUTTON => array(
			'path' => 'media/js/3rdParty/leaflet-plugins/leaflet-magnifyingglass/leaflet.magnifyingglass.button.css', 
			'version' => '0.2'
		), 
		self::STYLE_LEAFLET_FULLSCREEN => array(
			'path' => 'media/js/3rdParty/leaflet-plugins/leaflet-fullscreen/leaflet.fullscreen.css', 
			'version' => '0.0.4'
		), 
		self::STYLE_BOOTSTRAP => array(
			'path' => 'media/bootstrap/css/abp01-bootstrap.css',
			'version' => '5.3.2',
			'deps' => array()
		),
		self::STYLE_ABP01_NUMERIC_STEPPER => array(
			'path' => 'media/css/abp01-numeric-stepper.css', 
			'version' => ABP01_VERSION,
			'deps' => array(
				self::STYLE_DASHICONS
			)
		), 
		self::STYLE_ABP01_HELP_IMAGE_GALLERY => array(
			'path' => 'media/css/abp01-admin-help-image-gallery.css', 
			'version' => ABP01_VERSION,
			'deps' => array(
				self::STYLE_DASHICONS
			)
		),
		self::STYLE_JQUERY_TOASTR => array(
			'path' => 'media/js/3rdParty/toastr/toastr.css', 
			'version' => '2.1.4'
		), 
		self::STYLE_TIPPED_JS => array(
			'path' => 'media/js/3rdParty/tipped/css/tipped.css', 
			'version' => '4.7.0'
		),
		self::STYLE_CHART_JS => array(
			'path' => 'media/js/3rdParty/chartjs/Chart.css', 
			'version' => '2.9.3'
		),

		self::STYLE_FRONTEND_MAIN => array(
			'path' => 'media/css/abp01-frontend-main.css', 
			'version' => ABP01_VERSION,
			'allowOverrideFromTheme' => true,
			'deps' => array(
				self::STYLE_DASHICONS,
				self::STYLE_NPROGRESS,
				self::STYLE_LEAFLET,
				self::STYLE_LEAFLET_FULLSCREEN,
				self::STYLE_LEAFLET_MAGNIFYING_GLASS,
				self::STYLE_LEAFLET_MAGNIFYING_GLASS_BUTTON,

				array(
					'handle' => self::STYLE_CHART_JS,
					'if' => array(__CLASS__, '_hasAltitudeProfile')
				)
			)
		), 
		self::STYLE_FRONTEND_LOG_ENTRIES => array(
			'path' => 'media/css/abp01-frontend-log-entries.css', 
			'version' => ABP01_VERSION,
			'deps' => array(
				self::STYLE_FRONTEND_MAIN
			)
		),
		self::STYLE_FRONTEND_MAIN_TWENTY_TEN => array(
			'path' => 'media/css/twentyten/theme.css', 
			'version' => ABP01_VERSION,
			'deps' => array(
				self::STYLE_FRONTEND_MAIN
			)
		),
		self::STYLE_FRONTEND_MAIN_TWENTY_ELEVEN => array(
			'path' => 'media/css/twentyeleven/theme.css', 
			'version' => ABP01_VERSION,
			'deps' => array(
				self::STYLE_FRONTEND_MAIN
			)
		), 
		self::STYLE_FRONTEND_MAIN_TWENTY_THIRTEEN => array(
			'path' => 'media/css/twentythirteen/theme.css', 
			'version' => ABP01_VERSION,
			'deps' => array(
				self::STYLE_FRONTEND_MAIN
			)
		),
		self::STYLE_FRONTEND_MAIN_TWENTY_FIFTEEN => array(
			'path' => 'media/css/twentyfifteen/theme.css', 
			'version' => ABP01_VERSION,
			'deps' => array(
				self::STYLE_FRONTEND_MAIN
			)
		), 
		self::STYLE_FRONTEND_MAIN_TWENTY_FOURTEEN => array(
			'path' => 'media/css/twentyfourteen/theme.css', 
			'version' => ABP01_VERSION,
			'deps' => array(
				self::STYLE_FRONTEND_MAIN
			)
		),
		self::STYLE_FRONTEND_MAIN_TWENTY_SIXTEEN => array(
			'path' => 'media/css/twentysixteen/theme.css', 
			'version' => ABP01_VERSION,
			'deps' => array(
				self::STYLE_FRONTEND_MAIN
			)
		),
		self::STYLE_FRONTEND_MAIN_TWENTY_SEVENTEEN => array(
			'path' => 'media/css/twentyseventeen/theme.css', 
			'version' => ABP01_VERSION,
			'deps' => array(
				self::STYLE_FRONTEND_MAIN
			)
		),
		self::STYLE_FRONTEND_MAIN_TWENTY_NINETEEN => array(
			'path' => 'media/css/twentynineteen/theme.css', 
			'version' => ABP01_VERSION,
			'deps' => array(
				self::STYLE_FRONTEND_MAIN
			)
		),

		self::STYLE_ADMIN_COMMON => array(
			'path' => 'media/css/admin/abp01-admin-common.css', 
			'version' => ABP01_VERSION
		),
		self::STYLE_ADMIN_MAIN => array(
			'path' => 'media/css/abp01-main.css', 
			'version' => ABP01_VERSION,
			'deps' => array(
				self::STYLE_NPROGRESS,
				self::STYLE_LEAFLET,
				self::STYLE_SELECT2,
				self::STYLE_JQUERY_TOASTR,
				self::STYLE_TIPPED_JS,
				self::STYLE_ADMIN_COMMON
			)
		),
		self::STYLE_ADMIN_LOG_ENTRIES => array(
			'path' => 'media/css/abp01-admin-log-entries.css', 
			'version' => ABP01_VERSION,
			'deps' => array(
				self::STYLE_NPROGRESS,
				self::STYLE_JQUERY_TOASTR,
				self::STYLE_TIPPED_JS,
				self::STYLE_ADMIN_COMMON,
				self::STYLE_ABP01_NUMERIC_STEPPER
			)
		),
		self::STYLE_ADMIN_SYSTEM_LOGS => array(
			'path' => 'media/css/admin/abp01-admin-system-logs.css', 
			'version' => ABP01_VERSION,
			'deps' => array(
				self::STYLE_BOOTSTRAP,
				self::STYLE_DASHICONS,
				self::STYLE_ADMIN_COMMON
			)
		),
		self::STYLE_ADMIN_SETTINGS => array(
			'alias' => self::STYLE_ADMIN_MAIN,
			'deps' => array(
				self::STYLE_WP_COLOR_PICKER,
				self::STYLE_NPROGRESS,
				self::STYLE_TIPPED_JS,
				self::STYLE_ABP01_NUMERIC_STEPPER,
				self::STYLE_ADMIN_COMMON
			)
		),
		self::STYLE_ADMIN_LOOKUP_MANAGEMENT => array(
			'alias' => self::STYLE_ADMIN_MAIN,
			'deps' => array(
				self::STYLE_SYSTEM_THICKBOX,
				self::STYLE_NPROGRESS,
				self::STYLE_JQUERY_TOASTR,
				self::STYLE_ADMIN_COMMON
			)
		),
		self::STYLE_ADMIN_MAINTENANCE => array(
			'alias' => self::STYLE_ADMIN_MAIN,
			'deps' => array(
				self::STYLE_SYSTEM_THICKBOX,
				self::STYLE_NPROGRESS,
				self::STYLE_JQUERY_TOASTR,
				self::STYLE_ADMIN_COMMON
			)
		),
		self::STYLE_ADMIN_HELP => array(
			'path' => 'media/css/abp01-help.css', 
			'version' => ABP01_VERSION,
			'deps' => array(
				self::STYLE_NPROGRESS,
				self::STYLE_ABP01_HELP_IMAGE_GALLERY,
				self::STYLE_ADMIN_COMMON
			)
		),
		self::STYLE_ADMIN_ABOUT => array(
			'path' => 'media/css/abp01-about.css', 
			'version' => ABP01_VERSION,
			'deps' => array(
				self::STYLE_ADMIN_COMMON
			)
		),
		self::STYLE_ADMIN_POSTS_LISTING => array(
			'path' => 'media/css/abp01-admin-posts-listing.css', 
			'version' => ABP01_VERSION,
			'deps' => array(
				self::STYLE_ADMIN_COMMON
			)
		),
		self::STYLE_ADMIN_AUDIT_LOG => array(
			'alias' => self::STYLE_ADMIN_COMMON
		),
		self::STYLE_ADMIN_LISTING_AUDIT_LOG => array(
			'alias' => self::STYLE_ADMIN_MAIN,
			'deps' => array(
				self::STYLE_ADMIN_AUDIT_LOG,
				self::STYLE_NPROGRESS
			)
		)
	);

	private static $_styleSlugsForThemeIds = array(
		'twentyten' => self::STYLE_FRONTEND_MAIN_TWENTY_TEN,
		'twentyeleven' => self::STYLE_FRONTEND_MAIN_TWENTY_ELEVEN,
		'twentyfifteen' => self::STYLE_FRONTEND_MAIN_TWENTY_FIFTEEN,
		'twentyfourteen' => self::STYLE_FRONTEND_MAIN_TWENTY_FOURTEEN,
		'twentythirteen' => self::STYLE_FRONTEND_MAIN_TWENTY_THIRTEEN,
		'twentysixteen' => self::STYLE_FRONTEND_MAIN_TWENTY_SIXTEEN,
		'twentyseventeen' => self::STYLE_FRONTEND_MAIN_TWENTY_SEVENTEEN,
		'twentynineteen' => self::STYLE_FRONTEND_MAIN_TWENTY_NINETEEN
	);

	public static function configure($refPluginsPath, $scriptsInFooter) {
		$includesManager = new Abp01_Includes_Manager(self::$_scripts, 
			self::$_styles, 
			$refPluginsPath, 
			$scriptsInFooter);

		$includesManager->setScriptsPathRewriter(new Abp01_Includes_MaybeLeafletPluginScriptPathRewriter())
			->setScriptsDependencySelector(new Abp01_Includes_CallbackDependencySelector())
			->setStylesDependencySelector(new Abp01_Includes_CallbackDependencySelector());

		self::$_includesManager = $includesManager;
	}

	public static function isConfigured() {
		return self::$_includesManager instanceof Abp01_Includes_Manager;
	}

	private static function _hasAltitudeProfile(Abp01_Env $env, Abp01_Settings $settings) {
		return $settings->getShowAltitudeProfile();
	}

	private static function _includeScript($handle) {
		if (empty($handle)) {
			return;
		}
		self::$_includesManager->enqueueScript($handle);
	}

	private static function _includeStyle($handle) {
		if (empty($handle)) {
			return;
		}
		self::$_includesManager->enqueueStyle($handle);
	}

	public static function injectPluginSettings($scriptHandle) {
		wp_localize_script($scriptHandle, 
			'abp01Settings', 
			self::_getInjectablePluginSettings());
	}

	private static function _getInjectablePluginSettings() {
		$settings = self::_getSettings();
		$mainTileLayer = $settings->getMainTileLayer();

		return array(
			'_env' => array(
				'WP_DEBUG' => defined('WP_DEBUG') && WP_DEBUG === true
			),
			'showTeaser' => abp01_bool2str($settings->getShowTeaser()), 
			'mapShowFullScreen' => abp01_bool2str($settings->getShowFullScreen()), 
			'mapShowMagnifyingGlass' => abp01_bool2str($settings->getShowMagnifyingGlass()), 
			'mapAllowTrackDownloadUrl' => abp01_bool2str($settings->getAllowTrackDownload()),
			'mapShowScale' => abp01_bool2str($settings->getShowMapScale()),
			'mapShowMinMaxAltitude' => abp01_bool2str($settings->getShowMinMaxAltitude()),
			'mapShowAltitudeProfile' => abp01_bool2str($settings->getShowAltitudeProfile()),
			'mapTileLayer' => abp01_esc_js_object($mainTileLayer, ARRAY_A),
			'trackLineColour' => $settings->getTrackLineColour(),
			'trackLineWeight' => $settings->getTrackLineWeight(),
			'initialViewerTab' => $settings->getInitialViewerTab()
		);
	}

	private static function _getSettings() {
		return abp01_get_settings();
	}

	public static function includeScriptAdminEditorMain($addScriptSettings, $localization) {
		self::_includeScript(self::JS_ABP01_ADMIN_MAIN);

		if ($addScriptSettings) {
			self::injectPluginSettings(self::JS_ABP01_ADMIN_MAIN);
		}

		if (!empty($localization)) {
			wp_localize_script(self::JS_ABP01_ADMIN_MAIN, 
				'abp01MainL10n', 
				$localization);
		}
	}

	public static function includeScriptFrontendMain($addScriptSettings, $localization) {
		self::_includeScript(self::JS_ABP01_FRONTEND_MAIN);

		if ($addScriptSettings) {
			self::injectPluginSettings(self::JS_ABP01_FRONTEND_MAIN);
		}

		if (!empty($localization)) {
			wp_localize_script(self::JS_ABP01_FRONTEND_MAIN, 
				'abp01FrontendL10n', 
				$localization);
		}
	}

	public static function includeScriptAdminSettings($localization) {
		self::_includeScript(self::JS_ABP01_ADMIN_SETTINGS);
		if (!empty($localization)) {
			wp_localize_script(self::JS_ABP01_ADMIN_SETTINGS, 
				'abp01SettingsL10n', 
				$localization);
		}
	}

	public static function includeScriptAdminLookupMgmt($localization) {
		self::_includeScript(self::JS_ABP01_ADMIN_LOOKUP_MGMT);
		if (!empty($localization)) {
			wp_localize_script(self::JS_ABP01_ADMIN_LOOKUP_MGMT, 
				'abp01LookupMgmtL10n', 
				$localization);
		}
	}

	public static function includeScriptAdminMaintenance($localization) {
		self::_includeScript(self::JS_ABP01_ADMIN_MAINTENANCE);
		if (!empty($localization)) {
			wp_localize_script(self::JS_ABP01_ADMIN_MAINTENANCE, 
				'abp01MaintenanceL10n', 
				$localization);
		}
	}

	public static function includeScriptAdminHelp($localization) {
		self::_includeScript(self::JS_ABP01_ADMIN_HELP);
		if (!empty($localization)) {
			wp_localize_script(self::JS_ABP01_ADMIN_HELP, 
				'abp01HelpL10n', 
				$localization);
		}
	}

	public static function includeScriptAdminListingAuditLog($localization) {
		self::_includeScript(self::JS_ABP01_LISTING_AUDIT_LOG);
		if (!empty($localization)) {
			wp_localize_script(self::JS_ABP01_LISTING_AUDIT_LOG, 
				'abp01ListingAuditLogL10n', 
				$localization);
		}
	}

	public static function includeScriptBlockEditorViewerShortCodeBlock() {	
		self::_includeScript(self::JS_ABP01_VIEWER_SHORTCODE_BLOCK, 'registered');
		wp_localize_script(self::JS_ABP01_VIEWER_SHORTCODE_BLOCK, 
			'abp01ViewerShortCodeBlockSettings', 
			array(
				'tagName' => ABP01_VIEWER_SHORTCODE
			));
	}

	public static function includeScriptAdminLogEntries($localization) {
		self::_includeScript(self::JS_ABP01_ADMIN_LOG_ENTRIES);

		if (!empty($localization)) {
			wp_localize_script(self::JS_ABP01_ADMIN_LOG_ENTRIES, 
				'abp01AdminLogEntriesL10n', 
				$localization);
		}
	}

	public static function includeScriptAdminSystemLogs($localization) {
		self::_includeScript(self::JS_ABP01_ADMIN_SYSTEM_LOGS);

		self::_includeScriptAdminCommonTranslations();
		if (!empty($localization)) {
			wp_localize_script(self::JS_ABP01_ADMIN_SYSTEM_LOGS, 
				'abp01AdminSystemLogL10n', 
				$localization);
		}
	}

	private static function _includeScriptAdminCommonTranslations() {
		wp_localize_script(self::JS_ABP01_COMMON, 
			'abp01AdminCommonL10n', 
			Abp01_TranslatedScriptMessages::getCommonScriptTranslations());
	}

	public static function getClassicEditorViewerShortcodePluginUrl() {
		return self::$_includesManager
			->getScriptSrcUrl(self::JS_ABP01_CLASSIC_EDITOR_VIEWER_SHORTCODE_PLUGIN);
	}

	public static function includeStyleFrontendMain() {
		self::_includeStyle(self::STYLE_FRONTEND_MAIN);
		self::includeStyleFrontendMainThemeSpecificIfPresent();
	}

	public static function includeStyleFrontendMainThemeSpecificIfPresent() {
		$themeId = self::_getEnv()->getCurrentThemeId();
		if (isset(self::$_styleSlugsForThemeIds[$themeId])) {
			$styleSlug = self::$_styleSlugsForThemeIds[$themeId];
			self::_includeStyle($styleSlug);
		}
	}

	private static function _getEnv() {
		return abp01_get_env();
	}

	public static function enableStyleOverrideFromCurrentTheme() {
		self::$_includesManager->setStylesPathRewriter(new Abp01_Includes_FrontendStylePerThemePathRewriter());
	}

	public static function includeStyleAdminMain() {
		self::_includeStyle(self::STYLE_ADMIN_MAIN);
	}

	public static function includeStyleAdminLookupManagement() {
		self::_includeStyle(self::STYLE_ADMIN_LOOKUP_MANAGEMENT);
	}

	public static function includeStyleAdminSettings() {
		self::_includeStyle(self::STYLE_ADMIN_SETTINGS);
	}

	public static function includeStyleAdminHelp() {
		self::_includeStyle(self::STYLE_ADMIN_HELP);
	}

	public static function includeStyleAdminAbout() {
		self::_includeStyle(self::STYLE_ADMIN_ABOUT);
	}

	public static function includeStyleAdminMaintenance() {
		self::_includeStyle(self::STYLE_ADMIN_MAINTENANCE);
	}

	public static function includeStyleAdminPostsListing() {
		self::_includeStyle(self::STYLE_ADMIN_POSTS_LISTING);
	}

	public static function includeStyleAdminAuditLog() {
		self::_includeStyle(self::STYLE_ADMIN_AUDIT_LOG);
	}

	public static function includeStyleAdminLogEntries() {
		self::_includeStyle(self::STYLE_ADMIN_LOG_ENTRIES);
	}

	public static function includeStyleAdminListingAuditLog() {
		self::_includeStyle(self::STYLE_ADMIN_LISTING_AUDIT_LOG);
	}

	public static function includeStyleFrontendLogEntries() {
		self::_includeStyle(self::STYLE_FRONTEND_LOG_ENTRIES);
	}

	public static function includeStyleAdminSystemLogs() {
		self::_includeStyle(self::STYLE_ADMIN_SYSTEM_LOGS);
	}
}
