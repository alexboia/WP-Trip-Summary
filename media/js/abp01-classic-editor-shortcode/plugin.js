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

(function() {
    tinymce.create('abp01.plugins.ViewerShortcode', {
        init: function(tinymceEditor, pluginAbsoluteUrl) {
            tinymceEditor.addButton('abp01_insert_viewer_shortcode', {
                title : 'Insert Trip Summary Viewer Shortcode',
                cmd : 'abp01_insert_viewer_shortcode',
                image : pluginAbsoluteUrl + '/button.png'
            });

            tinymceEditor.addCommand('abp01_insert_viewer_shortcode', function() {
                var shortcode = '[abp01_trip_summary_viewer]';
                tinymceEditor.execCommand('mceInsertContent', 0, shortcode);
            });
        },

        createControl : function(controlName, tinymceControlManager) {
            return null;
        },

        getInfo : function() {
            return {
                longname : 'WP Trip Summary Viewer Shortcode Button',
                author : 'Alexandru Boia',
                authorurl : 'http://alexboia.net/',
                infourl : 'https://wordpress.org/plugins/wp-trip-summary/',
                version : '0.1'
            };
        }
    });

    tinymce.PluginManager.add('abp01_viewer_shortcode', abp01.plugins.ViewerShortcode);
})();