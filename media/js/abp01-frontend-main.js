(function($) {
    "use strict";

    var map = null;
    var context = null;
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

    function hideMapLoadingProgress() {
        $ctrlMapProgress.remove();
    }

    function displayMapLoadingProgress() {
        $ctrlMapProgress = $([
            '<div id="abp01-map-progress" class="abp01-map-progress">',
                '<img src="' + context.imgBase + '/ajax-loader-bar.gif" />',
            '</div>'
        ].join(''));
        $ctrlMapHolder.html($ctrlMapProgress);
    }

    function showMap() {
        $('#abp01-techbox-map').show();
        $ctrlMapHolder = $('#abp01-map');
        map = $ctrlMapHolder.mapTrack({
            trackDataUrl: getAjaxLoadTrackUrl(),
            iconBaseUrl: context.imgBase,
            handlePreLoad: function() {
                displayMapLoadingProgress();
            },
            handleLoad: function() {
                hideMapLoadingProgress();
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
            animate: false,
            tabActiveClass: 'abp01-tab-active',
            panelActiveClass: 'abp01-tabContentActive',
            defaultTab: '#abp01-tab-info',
            updateHash: false
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
        $ctrlTeaserAction.click(function() {
            hasSkippedContent = true;
            $('body,html').animate({
                scrollTop: $ctrlFrontend.offset().top
            }, 500);
        });

        $ctrlContentSkipTeaserAction.click(function() {
            hasSkippedContent = false;
            $ctrlContentSkipTeaser.hide();
            $('body,html').animate({
                scrollTop: $ctrlTitle.offset().top
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
    }

    function initDocument() {
        $(window).scroll(handlePageScroll);
    }

    function initState() {
        context = getContext();
    }

    $(document).ready(function() {
        initState();
        initControls();
        initTeasers();
        initDocument();
        if (context.hasInfo  && context.hasTrack) {
            initTabs();
        } else if (context.hasTrack) {
            showMap();
        }
    });
})(jQuery);