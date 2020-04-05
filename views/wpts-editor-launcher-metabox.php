<?php
/**
 * Copyright (c) 2014-2020 Alexandru Boia
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
    defined('ABP01_LOADED') or die;
?>

<div id="abp01-editor-launcher-root">
    <div id="abp01-editor-launcher-status">
        <div id="abp01-editor-launcher-status-trip-summary-info" class="abp01-editor-launcher-status-item">
            <span class="launcher-icon dashicons <?php echo $data->hasRouteInfo 
                ? 'dashicons-yes-alt' 
                : 'dashicons-dismiss'; ?>"></span>
            <a data-status-text="<?php echo $data->hasRouteInfo
                    ? esc_html__('Trip summary information is present for this post', 'abp01-trip-summary')
                    : esc_html__('Trip summary information is not present for this post', 'abp01-trip-summary'); ?>"
                href="javascript:void(0)" 
                data-action="abp01-openTechBox"
                data-select-tab="abp01-form-info"
                class="status-text launch-editor-trigger"><?php echo esc_html__('Trip summary info', 'abp01-trip-summary'); ?></a>
        </div>
        <div id="abp01-editor-launcher-status-trip-summary-track" class="abp01-editor-launcher-status-item">
            <span class="launcher-icon dashicons <?php echo $data->hasRouteTrack 
                ? 'dashicons-yes-alt' 
                : 'dashicons-dismiss'; ?>"></span>
            <a data-status-text="<?php echo $data->hasRouteTrack 
                    ? esc_html__('Trip summary track is present for this post', 'abp01-trip-summary') 
                    : esc_html__('Trip summary track is not present for this post', 'abp01-trip-summary'); ?>"
                data-action="abp01-openTechBox"
                href="javascript:void(0)"
                data-select-tab="abp01-form-map"
                class="status-text launch-editor-trigger"><?php echo esc_html__('Trip summary track', 'abp01-trip-summary'); ?></a>
        </div>
    </div>
    <div id="abp01-editor-launcher-actions">
        <div class="quick-actions">
            <a id="abp01-quick-actions-trigger" 
                style="display: <?php echo ($data->hasRouteInfo || $data->hasRouteTrack) ? 'block' : 'none' ?>;"
                data-controller-selector="abp01-quick-actions-tooltip" 
                href="javascript:void(0)"><?php echo esc_html__('Quick actions', 'abp01-trip-summary'); ?></a>
        </div>
        <div class="launch-edit">
            <a id="abp01-edit-trigger" 
                data-status-text="<?php echo esc_html__('Click here to edit trip summary information and/or track', 'abp01-trip-summary'); ?>"
                href="javascript:void(0)" 
                data-action="abp01-openTechBox"
                class="button launch-editor-trigger"><?php echo esc_html__('Edit', 'abp01-trip-summary'); ?></a>
        </div>
        <div class="clear"></div>
    </div>
    <div id="abp01-quick-actions-tooltip" class="abp01-quick-actions-tooltip" style='display:none'>
        <?php if ($data->hasRouteInfo): ?>
            <a id="abp01-quick-remove-info" 
                href="javascript:void(0)"><?php echo esc_html__('Clear info', 'abp01-trip-summary'); ?></a>
        <?php endif; ?>
        <?php if ($data->hasRouteTrack): ?>
            <a id="abp01-quick-download-track" 
                href="<?php echo esc_attr($data->trackDownloadUrl); ?>"
                target="_blank"><?php echo esc_html__('Download track', 'abp01-trip-summary'); ?></a>
            <a id="abp01-quick-remove-track" 
                href="javascript:void(0)"><?php echo esc_html__('Clear track', 'abp01-trip-summary'); ?></a>
        <?php endif; ?>
        <div class="clear"></div>
    </div>
    <script id="tpl-abp01-quick-actions-tooltip" type="text/x-kite">
        {{? context.hasRouteInfo }}
            <a id="abp01-quick-remove-info" 
                href="javascript:void(0)"><?php echo esc_html__('Clear info', 'abp01-trip-summary'); ?></a>
        {{/?}}
        {{? context.hasRouteTrack }}
            <a id="abp01-quick-download-track" 
                href="<?php echo esc_attr($data->trackDownloadUrl); ?>"><?php echo esc_html__('Download track', 'abp01-trip-summary'); ?></a>
            <a id="abp01-quick-remove-track" 
                href="javascript:void(0)"><?php echo esc_html__('Clear track', 'abp01-trip-summary'); ?></a>
        {{/?}}
        <div class="clear"></div>
    </script>
</div>