(function($) {
	"use strict";

	var map = null;
	var context = null;
	var settings = null;
	var hasSkippedContent = false;
	var scrollTimer = null;

	var $ctrlFrontend = null;
	var $ctrlTechboxTabs = null;
	var $ctrlMapProgress = null;
	var $ctrlMapHolder = null;

	var $ctrlTeaserAction = null;
	var $ctrlContentSkipTeaserAction = null;

	var $ctrlTeaser = null;
	var $ctrlContentSkipTeaser = null;
	var $ctrlTitle = null;
	var $ctrlMapRetry = null;
	var $ctrlMapRetryContainer = null;
	var $ctrlMapTabContainer = null;

	function hideMapLoadingProgress() {
		$ctrlMapProgress.remove();
	}

	function displayMapLoadingProgress() {
		$ctrlMapProgress = $([
			'<div id="abp01-map-progress" class="abp01-map-progress">', 
				'<img src="' + context.imgBase + '/ajax-loader-bar.gif" />', 
			'</div>']
		.join(''));
		$ctrlMapHolder.html($ctrlMapProgress);
	}

	function showMap() {
		var settings = getSettings();
		
		$ctrlMapTabContainer.show();
		map = $ctrlMapHolder.mapTrack({
			tileLayer: settings.mapTileLayer,
			
			//view options
			showScale : true,
			showMagnifyingGlass : settings.mapShowMagnifyingGlass,
			showFullScreen : settings.mapShowFullScreen,

			//map and data options
			trackDataUrl : getAjaxLoadTrackUrl(),
			iconBaseUrl : context.imgBase,

			//callbacks
			handlePreLoad : function() {
				displayMapLoadingProgress();
				$ctrlMapRetryContainer.hide();
			},
			handleLoad : function(success) {
				hideMapLoadingProgress();
				if (!success) {
					$ctrlMapRetryContainer.show();
				} else {
					$ctrlMapRetryContainer.hide();
				}
			}
		});
	}

	function initMapRetry() {
		$ctrlMapRetry.click(function() {
			if (map) {
				map.loadMap();
			}
		});
	}

	function getContext() {
		return {
			imgBase: window['abp01_imgBase'] || null,
			ajaxBaseUrl: window['abp01_ajaxUrl'] || null,
			ajaxGetTrackAction: window['abp01_ajaxGetTrackAction'] || null,
			hasInfo: window['abp01_hasInfo'] || false,
			hasTrack: window['abp01_hasTrack'] || false,
			nonceGet: window['abp01_nonceGet'],
			postId: window['abp01_postId'] || 0
		};
	}

	function getSettings() {
		var pluginSettings = jQuery.extend({}, window['abp01Settings'] || {});
		pluginSettings.showTeaser = pluginSettings.showTeaser === 'true';
		pluginSettings.mapShowFullScreen = pluginSettings.mapShowFullScreen === 'true';
		pluginSettings.mapShowMagnifyingGlass = pluginSettings.mapShowMagnifyingGlass === 'true';
		pluginSettings.mapTileLayer = pluginSettings.mapTileLayer || {};
		return pluginSettings;
	}

	function getAjaxLoadTrackUrl() {
		return URI(context.ajaxBaseUrl)
			.addSearch('action', context.ajaxGetTrackAction)
			.addSearch('abp01_nonce_get', context.nonceGet)
			.addSearch('abp01_postId', context.postId)
			.toString();
	}

	function handlePageScroll() {
		if (scrollTimer !== null) {
			clearTimeout(scrollTimer);
		}
		scrollTimer = setTimeout(function() {
			if (hasSkippedContent && $ctrlFrontend.visible()) {
				$ctrlContentSkipTeaser.show();
			}
			scrollTimer = null;
		}, 100);
	}

	function initTabs() {
		$ctrlTechboxTabs = $('#abp01-techbox-wrapper').easytabs({
			animate : false,
			tabActiveClass : 'abp01-tab-active',
			panelActiveClass : 'abp01-tabContentActive',
			defaultTab : '#abp01-tab-info',
			updateHash : false
		});

		$ctrlTechboxTabs.bind('easytabs:after', function(e, $clicked, $target, settings) {
			if ($target.attr('id') == 'abp01-techbox-map') {
				if (map != null) {
					map.forceRedraw();
				} else {
					showMap();
				}
			}
		});
	}

	function initTeasers() {
		if (!settings.showTeaser) {
			return;
		}
		
		$ctrlTeaserAction.click(function() {
			hasSkippedContent = true;
			$('body,html').animate({
				scrollTop : $ctrlFrontend.offset().top
			}, 500);
		});

		$ctrlContentSkipTeaserAction.click(function() {
			hasSkippedContent = false;
			$ctrlContentSkipTeaser.hide();
			$('body,html').animate({
				scrollTop : $ctrlTitle.offset().top
			}, 500);
		});

		if ($ctrlFrontend.visible()) {
			$ctrlContentSkipTeaser.show();
		}
	}

	function initControls() {
		$ctrlFrontend = $('#abp01-techbox-frontend');
		$ctrlTeaserAction = $('#abp01-techbox-teaser-action');
		$ctrlContentSkipTeaserAction = $('#abp01-techbox-content-skip-teaser-action');
		$ctrlTeaser = $('#abp01-techbox-teaser');
		$ctrlContentSkipTeaser = $('#abp01-techbox-content-skip-teaser');
		$ctrlTitle = $('h1.entry-title');
		$ctrlMapRetry = $('#abp01-map-retry');
		$ctrlMapRetryContainer = $('#abp01-map-retry-container');
		$ctrlMapHolder = $('#abp01-map');
		$ctrlMapTabContainer = $('#abp01-techbox-map');
	}

	function initDocument() {
		$(window).scroll(handlePageScroll);
	}

	function initState() {
		context = getContext();
		settings = getSettings();
	}

	$(document).ready(function() {
		initState();
		initControls();
		initTeasers();
		initDocument();
		initMapRetry();
		if (context.hasInfo && context.hasTrack) {
			initTabs();
		} else if (context.hasTrack) {
			showMap();
		}
	});
})(jQuery); 