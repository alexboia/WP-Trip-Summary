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

(function(wp) {
    "use strict";

    var wpElement = wp.element;
    var wpBlocks = wp.blocks;
    var wpData = wp.data;

    var createElement = wpElement
        .createElement;

    wpBlocks.registerBlockType('abp01/block-editor-shortcode', {
        title: 'WP Trip Summary Viewer Shortcode',
        icon: 'chart-area',
        category: 'widgets',
        example: {},
        supports: {
            multiple: false,
            reusable: false
        },
        edit: function(props) {
            var tagName = window
                .abp01ViewerShortCodeBlockSettings
                .tagName;

            return createElement(
                'div',
                { style: {}, className: 'abp01-viewer-shortcode-block-editor' },
                ('[' + tagName + ']')
            );
        },
        save: function(props) {
            return null;
        },
    });

    wp.domReady(function() {      
        var currentPost = wpData
            .select('core/editor')
            .getCurrentPost();
        
        if (!currentPost || (currentPost.type != 'page' && currentPost.type != 'post') ) {
            wpBlocks.unregisterBlockType('abp01/block-editor-shortcode');
        }
    });
})(window.wp);
