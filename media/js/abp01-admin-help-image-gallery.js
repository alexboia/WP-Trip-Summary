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

(function($) {
	"use strict";

	$.fn.abp01HelpImageGallery = function(opts) {
		var $me = this;
		var $galleryContainer = null;

		var _options = !!opts 
			? opts 
			: {};

		function readSourceImages() {
			var sourceImages = [];
			var $imageHeaders = $me.find('.abp01-gallery-item-header');

			$imageHeaders.each(function() {
				var $header = $(this);
				var $imageContainer = $header.next('p');
				var $image = $imageContainer.find('img');
				
				var imageInfo = {
					_$header: $header,
					_$container: $imageContainer,

					src: $image.attr('src'),
					title: $image.attr('title'),
					alt: $image.attr('alt'),
					text: $header.text()
				};

				sourceImages.push(imageInfo);
			});

			return sourceImages;
		}

		function buildGalleryContainerElement() {
			$galleryContainer = $([
				'<div class="abp01-admin-help-image-gallery">',
					'<a id="abp01-admin-help-image-gallery-viewer-top" name="abp01-admin-help-image-gallery-viewer-top" />',
					'<div class="abp01-admin-help-image-gallery-enlarged">', 
						'<div class="abp01-admin-help-image-gallery-enlarged-image"></div>',
						'<div class="abp01-admin-help-image-gallery-enlarged-title"></div>',
					'</div>',
					'<ul class="abp01-admin-help-image-gallery-thumbnails"></ul>',
				'</div>'
			].join(''));
		}

		function createGalleryContentsFromSourceImages(sourceImages) {
			var thumbnailsHtml = [];
			var $thumbnailsContainer = getGalleryThumbnailsContainer();

			for (var i = 0; i < sourceImages.length; i ++) {
				var sourceImage = sourceImages[i];
				removeOldImageElements(sourceImage);

				var thumbnailItemHtml = buildImageThumbnailItemHtml(sourceImage);
				thumbnailsHtml.push(thumbnailItemHtml);
			}

			$thumbnailsContainer.html(thumbnailsHtml.join(''));
			$me.prepend($galleryContainer);
		}

		function getGalleryThumbnailsContainer() {
			return $galleryContainer.find('.abp01-admin-help-image-gallery-thumbnails');
		}

		function removeOldImageElements(sourceImage) {
			sourceImage._$header
				.remove();
			sourceImage._$container
				.remove();
		}

		function buildImageThumbnailItemHtml(sourceImage) {
			return [
				'<li class="abp01-gallery-item-thumbnail-viewport">',
					('<img src="' + sourceImage.src 
						+ '" alt="' + sourceImage.alt 
						+ '" title="' + sourceImage.title 
						+ '" class="abp01-gallery-item-thumbnail" />'),
				'</li>'
			].join('');
		}

		function displayEnlargedImage($thumbnail) {
			var $enlargedImageContainer = getGalleryEnlargedImageContainer();
			var $enlargedTitleContainer = getGalleryEnlargedTitleContainer();

			var enlargedImageHtml = buildImageEnlargedHtml($thumbnail);
			
			$enlargedImageContainer.html(enlargedImageHtml);
			$enlargedTitleContainer.text($thumbnail.attr('title'));
			
			markThumbnailCurrent($thumbnail);
		}

		function getGalleryEnlargedImageContainer() {
			return $galleryContainer.find('.abp01-admin-help-image-gallery-enlarged-image');
		}

		function getGalleryEnlargedTitleContainer() {
			return $galleryContainer.find('.abp01-admin-help-image-gallery-enlarged-title');
		}

		function buildImageEnlargedHtml($thumbnail) {
			return '<img src="' + $thumbnail.attr('src') 
				+ '" alt="' + $thumbnail.attr('alt') 
				+ '" title="' + $thumbnail.attr('title') 
				+ '" class="abp01-gallery-item-enlarged" />';
		}

		function markThumbnailCurrent($thumbnail) {
			$('.abp01-gallery-item-thumbnail').removeClass('abp01-gallery-item-thumbnail-selected');
			$thumbnail.addClass('abp01-gallery-item-thumbnail-selected');
		}

		function displayFirstImageEnlarged() {
			var $firstImageThumbnailElement = getFirstThumbnailImage();
			displayEnlargedImage($firstImageThumbnailElement);
		}

		function getFirstThumbnailImage() {
			return $galleryContainer
				.find('.abp01-gallery-item-thumbnail')
				.first();
		}

		function scrollToEnlargedImage() {
			$('body,html').scrollTop(getGalleryViewerTopMarkerOffset());
		}

		function getGalleryViewerTopMarkerOffset() {
			return $('#abp01-admin-help-image-gallery-viewer-top')
				.offset()
				.top;
		}

		function handleImageThumbnailClicked(e) {
			var $selectedThumbnail = $(this);
			displayEnlargedImage($selectedThumbnail);
			scrollToEnlargedImage();
		}

		function registerImageThumbnailsListeners() {
			$galleryContainer
				.find('.abp01-gallery-item-thumbnail')
				.on('click', handleImageThumbnailClicked);
		}

		function build() {
			var sourceImages = readSourceImages();
			if (sourceImages.length > 0) {
				buildGalleryContainerElement();
				createGalleryContentsFromSourceImages(sourceImages);
				registerImageThumbnailsListeners();
				displayFirstImageEnlarged();
			}
		}

		function destroy() {
			unregisterImageThumbnailsListeners();
			destroyGalleryContainer();
		}

		function unregisterImageThumbnailsListeners() {
			$galleryContainer
				.find('.abp01-gallery-item-thumbnail')
				.unbind();
		}

		function destroyGalleryContainer() {
			$galleryContainer.remove();
			$galleryContainer = null;
		}

		function refresh() {
			destroy();
			build();
		}

		build();

		return {
			build: build,
			destroy: destroy,
			refresh: refresh
		};
	};
})(jQuery);