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
    "use strict";

    function getShortcodeContent(tinymceEditor) {
        return [
            '<p id="abp01-shortcode-container">',
                ('['  + tinymceEditor.settings.abp01_viewer_short_code_name + ']'),
            '</p>'
        ].join('');
    }
    
    function getExistingShortcodeElement(tinymceEditor) {
        var body = tinymceEditor.getBody();
        if (body.querySelector) {
            return body.querySelector('#abp01-shortcode-container');
        } else {
            return body.ownerDocument.getElementById('abp01-shortcode-container');
        }
    }

    function insertShortcodeContent(tinymceEditor) {
        //insert shortcode content and select it
        tinymceEditor.execCommand('mceInsertContent', 0, getShortcodeContent(tinymceEditor));
        tinymceEditor.selection.select(getExistingShortcodeElement(tinymceEditor));
    }

    tinymce.create('abp01.plugins.ViewerShortcode', {
        init: function(tinymceEditor, pluginAbsoluteUrl) {
            tinymceEditor.addButton('abp01_insert_viewer_shortcode', {
                title : 'Insert Trip Summary Viewer Shortcode',
                cmd : 'abp01_insert_viewer_shortcode',
                image : pluginAbsoluteUrl + '/button.png'
            });

            tinymceEditor.addCommand('abp01_insert_viewer_shortcode', function() {
                var existing = getExistingShortcodeElement(tinymceEditor);
                var translatedMessge = tinymceEditor.translate('Shortcode already exists. Do you wish to move it at the new position?');

                //if the shortcode already exists, 
                //  ask the user whether to move it or not
                //if the user wants to move it, simply remove the current occurence
                if (existing != null) {
                    if (confirm(translatedMessge)) {
                        existing.remove();
                        insertShortcodeContent(tinymceEditor);
                    }
                } else {
                    insertShortcodeContent(tinymceEditor);
                }
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

    tinymce.PluginManager.add('abp01_viewer_shortcode', 
        abp01.plugins.ViewerShortcode);
})();