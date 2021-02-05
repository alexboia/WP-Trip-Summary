/**
 * Copyright (c) 2014-2021 Alexandru Boia
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

(function($) {
    "use strict";

    var map = null;
    var context = null;
    var settings = null;
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
        $ctrlMapTabContainer.show();
        map = $ctrlMapHolder.mapTrack({
            tileLayer: settings.mapTileLayer,

            //view options
            showScale : settings.mapShowScale,
            showMagnifyingGlass : settings.mapShowMagnifyingGlass,
            showFullScreen : settings.mapShowFullScreen,
            showMinMaxAltitude: settings.mapShowMinMaxAltitude,
            showAltitudeProfile: settings.mapShowAltitudeProfile,
            trackDownloadUrl: settings.mapAllowTrackDownloadUrl ? getDownloadTrackUrl() : null,
            trackLineColour: settings.trackLineColour,
            trackLineWeight: settings.trackLineWeight,

            //labels
            labels: {
                minAltitude: abp01FrontendL10n.lblMinAltitude,
                maxAltitude: abp01FrontendL10n.lblMaxAltitude,
                altitude: abp01FrontendL10n.lblAltitude,
                distance: abp01FrontendL10n.lblDistance
            },

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
            downloadTrackAction: window['abp01_downloadTrackAction'] || null,
            hasInfo: window['abp01_hasInfo'] || false,
            hasTrack: window['abp01_hasTrack'] || false,
            nonceGet: window['abp01_nonceGet'],
            nonceDownload: window['abp01_nonceDownload'] || null,
            postId: window['abp01_postId'] || 0
        };
    }

    function getSettings() {
        return {
            showTeaser: abp01Settings.showTeaser === 'true',
            mapShowFullScreen: abp01Settings.mapShowFullScreen === 'true',
            mapShowMagnifyingGlass: abp01Settings.mapShowMagnifyingGlass === 'true',
            mapShowScale: abp01Settings.mapShowScale === 'true',
            mapShowMinMaxAltitude: abp01Settings.mapShowMinMaxAltitude === 'true',
            mapShowAltitudeProfile: abp01Settings.mapShowAltitudeProfile === 'true',
            mapAllowTrackDownloadUrl: abp01Settings.mapAllowTrackDownloadUrl === 'true',
            mapTileLayer: abp01Settings.mapTileLayer || {},
            trackLineColour: abp01Settings.trackLineColour || '#0033ff',
            trackLineWeight: abp01Settings.trackLineWeight || 3,
            initialViewerTab: abp01Settings.initialViewerTab || 'abp01-tab-info'
        };
    }

    function getAjaxLoadTrackUrl() {
        return URI(context.ajaxBaseUrl)
            .addSearch('action', context.ajaxGetTrackAction)
            .addSearch('abp01_nonce_get', context.nonceGet)
            .addSearch('abp01_postId', context.postId)
            .toString();
    }

    function getDownloadTrackUrl() {
        return URI(context.ajaxBaseUrl)
            .addSearch('action', context.downloadTrackAction)
            .addSearch('abp01_nonce_download', context.nonceDownload)
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

    function initOrRefreshMap() {
        if (map != null) {
            map.forceRedraw();
        } else {
            showMap();
        }
    }

    function initTabs() {
        //init tabs controller and bind tab selection events
        $ctrlTechboxTabs = $('#abp01-techbox-wrapper').easytabs({
            animate : false,
            tabActiveClass : 'abp01-tab-active',
            panelActiveClass : 'abp01-tabContentActive',
            defaultTab : '#abp01-tab-info',
            tabs: '.abp01-tab',
            updateHash : false
        });

        $ctrlTechboxTabs.bind('easytabs:after', function(e, $clicked, $target, eventSettings) {
            var clickedTab = $clicked
                .parent()
                .attr('id');

            if (clickedTab == 'abp01-tab-map') {
                initOrRefreshMap();
            }
        });

        //set initial tab
        window.setTimeout(function() {
            if (settings.initialViewerTab == 'abp01-tab-map') {
                $ctrlTechboxTabs.easytabs('select', '#abp01-tab-map');
            }
        });
    }

    function initTeasers() {
        //skip teaser initialization if it has been hidden
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

        if (context.hasInfo || context.hasTrack) {
            initControls();
            initTeasers();
            initDocument();
            initMapRetry();
        }

        if (context.hasInfo && context.hasTrack) {
            initTabs();
        } else if (context.hasTrack) {
            showMap();
        }
    });
})(jQuery); 