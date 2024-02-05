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
    defined('ABP01_LOADED') or die;
?>

<script type="text/javascript">
    var abp01_imgBase = '<?php echo esc_js($data->imgBaseUrl); ?>';
    var abp01_ajaxUrl = '<?php echo esc_js($data->ajaxUrl); ?>';
    var abp01_ajaxGetTrackAction = '<?php echo esc_js($data->ajaxGetTrackAction); ?>';
	var abp01_downloadTrackAction = '<?php echo esc_js($data->downloadTrackAction); ?>';

    var abp01_hasInfo = <?php echo $data->info->exists ? 'true' : 'false' ?>;
    var abp01_hasTrack = <?php echo $data->track->exists ? 'true' : 'false' ?>;
    var abp01_hasAdditionalTabs = <?php echo abp01_frontend_viewer_has_additional_tabs($data) ? 'true' : 'false' ?>;
    var abp01_totalTabCount = <?php echo abp01_count_frontend_viewer_tabs($data); ?>;
    var abp01_postId = '<?php echo $data->postId; ?>';
    var abp01_nonceGet = '<?php echo esc_js($data->nonceGet); ?>';
	var abp01_nonceDownload = '<?php echo esc_js($data->nonceDownload); ?>';
</script>