<?php
/**
 * Copyright (c) 2014-2026 Alexandru Boia and Contributors
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

if (!defined('ABP01_LOADED')) {
	die;
}

if (!function_exists('abp01_extract_value_from_frontend_data')) {
	/**
	 * Extracts a field value from the info data store ($data->info)
	 * 
	 * @param stdClass $data The main data store
	 * @param string $field The field to extract from $data->info
	 * @return mixed|null The field value or null if not found
	 */
	function abp01_extract_value_from_frontend_data(stdClass $data, string $field): mixed {
		if ($data->info && isset($data->info->$field)) {
			return $data->info->$field;
		} else {
			return null;
		}
	}
}

if (!function_exists('abp01_extract_displayable_info_item_value')) {
	function abp01_extract_displayable_info_item_value(mixed $rawValue): string {
		return esc_html(is_object($rawValue) 
			? $rawValue->label 
			: $rawValue);
	}
}

if (!function_exists('abp01_render_info_item_value_part')) {
	function abp01_render_info_item_value_part(?string $fieldValue, int $valueIndex, int $showCount, ?string $layoutCssClass): string {
		$shouldBeHidden = $showCount > 0 && ($valueIndex >= $showCount);

		$displayCssClass = $shouldBeHidden 
			? 'abp01-field-value-hideable' 
			: 'abp01-field-value-show';

		$displayInlineCss = $shouldBeHidden 
			? 'display: none;' 
			: '';

		$fieldValueHtml = sprintf('<span class="abp01-field-value-multi %s %s" style="%s">%s</span>', 
			$layoutCssClass, 
			$displayCssClass,
			$displayInlineCss,
			$fieldValue);

		return $fieldValueHtml;
	}
}

if (!function_exists('abp01_render_info_item_value_more_link')) {
	function abp01_render_info_item_value_more_link(int $countRemaining, ?string $layoutCssClass): string {
		return sprintf(
				'<span class="abp01-field-value-multi %s abp01-field-value-show-more">'  
					. '<span class="abp01-field-value-show-more-txt">%s</span>'  
					. '<a href="javascript:void(0)">%s</a>' 
				. '</span>',
			$layoutCssClass,
			sprintf(__('and %d more', 'abp01-trip-summary'), $countRemaining),
			__('(show)', 'abp01-trip-summary')
		);
	}
}

if (!function_exists('abp01_format_info_item_single_value')) {
	function abp01_format_info_item_single_value(string|stdClass|null $value, ?string $suffix, stdClass $settings) {
		$fieldValue = abp01_extract_displayable_info_item_value($value);
		if (!empty($suffix)) {
			$fieldValue .= ' ' . $suffix;
		}

		$fieldValueHtml = sprintf('<span class="abp01-field-value-single">%s</span>', 
			$fieldValue);

		return $fieldValueHtml;
	}
}

if (!function_exists('abp01_format_info_item_multi_value')) {
	function abp01_format_info_item_multi_value(array $value, ?string $suffix, stdClass $settings): string {
		$fieldValue = '';
		$fieldValueHtml = '';
		
		$valueIndex = 0;
		$itemValueCount = count($value);

		$showCount = $settings->viewerItemValueDisplayCount;
		$layoutCssClass = $settings->viewerItemLayout;

		foreach ($value as $v) {
			$fieldValue = abp01_extract_displayable_info_item_value($v);
			if ($valueIndex < $itemValueCount - 1) {
				$fieldValue .= ',';
			}

			$fieldValueHtml .= abp01_render_info_item_value_part($fieldValue, 
				$valueIndex, 
				$showCount, 
				$layoutCssClass);

			$valueIndex += 1;
		}

		if ($showCount > 0 && $showCount < $itemValueCount) {
			$fieldValueHtml .= abp01_render_info_item_value_more_link($itemValueCount - $showCount, 
				$layoutCssClass);
		}

		return $fieldValueHtml;
	}
}

if (!function_exists('abp01_format_info_item_value')) {
	/**
	 * Format the given value, also adding a suffix if not empty.
	 * If the value is an array, then each its elements are joined in a comma separated string .
	 * If the value is an object, then its "label" property is returned. 
	 * The same applies for array elements, when the value is an array.
	 * 
	 * @param mixed $value The value to format
	 * @param string $suffix The suffix to append to the formatted value
	 * @return string The formatted value
	 */
	function abp01_format_info_item_value(mixed $value, ?string $suffix, stdClass $settings) {
		$fieldValueHtml = '';

		if (!empty($value)) {
			if (is_array($value)) {
				$fieldValueHtml = abp01_format_info_item_multi_value($value, 
					$suffix, 
					$settings);
			} else {
				$fieldValueHtml = abp01_format_info_item_single_value($value, 
					$suffix, 
					$settings);
			}
		}

		return $fieldValueHtml;
	}
}

if (!function_exists('abp01_display_info_item')) {
	/**
	 * Render a track information item, given the main data store, the field, the label and an optional suffix.
	 * The data item is extracted from $data->info.
	 * 
	 * @see abp01_format_info_item_value
	 * @see abp01_extract_value_from_frontend_data
	 * 
	 * @param stdClass $data The main data store
	 * @param string $field The field to render
	 * @param string $fieldLabel The label to use when rendering the field
	 * @param string $suffix The suffix to use when rendering the field. Defaults to empty string.
	 * 
	 * @return void
	 */
	function abp01_display_info_item(stdClass $data, string $field, string $fieldLabel, string $suffix = ''): void {
		static $itemIndex = 0;
		$settings = $data->settings;
		$value = abp01_extract_value_from_frontend_data($data, 
			$field);

		if (!empty($value)) {
			$iconSvg = abp01_get_info_item_icon($field);
			$fieldValue = abp01_format_info_item_value($value, 
				$suffix, 
				$settings);
			
			$cssClass = 'abp01-info-item ' 
				. $field . ' ' 
				. ($itemIndex % 2 == 0 
					? 'abp01-item-even' 
					: 'abp01-item-odd');

			$isHighlighted = abp01_is_info_item_highlighted($field);
			$iconCssClass = 'abp01-info-item-icon';

			if ($isHighlighted === true) {
				$cssClass .= ' abp01-info-item-warm';
				$iconCssClass .= ' abp01-info-item-icon-warm';
			}

			$itemOutput = ('<li class="' . esc_attr($cssClass) . '">')
				. ('<div class="abp01-info-item-head">')
					. (!empty($iconSvg) 
						? '<span class="' . $iconCssClass . '">' . $iconSvg . '</span>' 
						: '')
					. ('<span class="abp01-info-label abp01-info-item-label">' 
							. esc_html($fieldLabel) 
					. '</span>')
				. ('</div>')
				. ('<div class="abp01-info-value abp01-info-item-value">' . $fieldValue . '</div>')
				. ('<div class="abp01-clear"></div>')
				. '</li>';

			$itemIndex ++;
		} else {
			$itemOutput = '';
		}

		echo $itemOutput;
	}
}

if (!function_exists('abp01_get_info_item_icon')) {
	function abp01_get_info_item_icon(string $field): ?string {
		if (empty($field)) {
			return null;
		}

		$icons = abp01_get_info_item_icons();
		$mapping = abp01_get_info_item_field_icon_mapping();

		$iconSvgContents = null;
		if (!empty($mapping[$field])) {
			$iconKey = $mapping[$field];
			if (!empty($iconKey) && !empty($icons[$iconKey])) {
				$iconSvgContents = $icons[$iconKey];
			}
		}

		if (empty($iconSvgContents)) {
			return null;
		}

		return (
			'<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#1793A3" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">' .
				$iconSvgContents . 
			'</svg>'
		);
	}
}

if (!function_exists('abp01_get_info_item_field_icon_mapping')) {
	function abp01_get_info_item_field_icon_mapping(): array {
		static $mapping = null;

		if ($mapping === null) {
			$defaultMapping = array(
				'bikeDistance' => 'distance',
				'bikeTotalClimb' => 'climb',
				'bikeDifficultyLevel' => 'gauge',
				'bikeAccess' => 'access',
				'bikeRecommendedSeasons' => 'season',
				'bikePathSurfaceType' => 'surface',
				'bikeBikeType' => 'bike',

				'hikingDistance' => 'distance',
				'hikingTotalClimb' => 'climb',
				'hikingDifficultyLevel' => 'gauge',
				'hikingAccess' => 'access',
				'hikingRecommendedSeasons' => 'season',
				'hikingSurfaceType' => 'surface',
				'hikingRouteMarkers' => 'marker',

				'trainRideDistance' => 'distance',
				'trainRideChangeNumber' => 'swap',
				'trainRideGauge' => 'gauge',
				'trainRideOperator' => 'train',
				'trainRideLineStatus' => 'line',
				'trainRideElectrificationStatus' => 'power',
				'trainRideLineType' => 'line'
			);

			$mapping = apply_filters('abp01_info_item_field_icon_mapping', 
				$defaultMapping);

			if (!is_array($mapping)) {
				$mapping = $defaultMapping;
			}
		}

		return $mapping;
	}
}

if (!function_exists('abp01_is_info_item_highlighted')) {
	function abp01_is_info_item_highlighted(string $field): bool {
		if (empty($field)) {
			return false;
		}

		$highlight = [
			'bikeTotalClimb',
			'bikeRecommendedSeasons',
			'hikingTotalClimb',
			'hikingRecommendedSeasons'
		];

		return in_array($field, $highlight);
	}
}

if (!function_exists('abp01_get_info_item_icons')) {
	function abp01_get_info_item_icons(): array {
		static $icons = null;
		if ($icons === null) {
			$defaultIcons = array(
				'distance' => '<path d="M3 8l13 13 5-5L8 3zM8 8l2 2M11 5l2 2M14 11l2 2"/>',
				'climb' => '<path d="M3 20h18M6 20l6-12 4 7 3-4"/>',
				'access' => '<path d="M12 21s-7-5.5-7-11a7 7 0 0114 0c0 5.5-7 11-7 11z"/><circle cx="12" cy="10" r="2.5"/>',
				'season' => '<circle cx="12" cy="12" r="4"/><path d="M12 2v3M12 19v3M2 12h3M19 12h3M5 5l2 2M17 17l2 2M19 5l-2 2M7 17l-2 2"/>',
				'surface' => '<path d="M4 18l4-6 4 3 4-8 4 5M3 21h18"/>',
				'bike' => '<circle cx="6" cy="17" r="3.2"/><circle cx="18" cy="17" r="3.2"/><path d="M6 17l4-7h5l3 7M9 7h3"/>',
				'marker' => '<path d="M6 3v18M6 5l9 3-9 3"/>',
				'train' => '<rect x="6" y="4" width="12" height="12" rx="3"/><path d="M6 10h12M9 20l-2 2M15 20l2 2"/><circle cx="9" cy="13" r="1"/><circle cx="15" cy="13" r="1"/>',
				'gauge' => '<path d="M12 14a2 2 0 100-4 2 2 0 000 4zM4 20a8 8 0 0116 0"/>',
				'swap' => '<path d="M7 4l-3 3 3 3M4 7h13M17 20l3-3-3-3M20 17H7"/>',
				'power' => '<path d="M13 2L4 14h7l-1 8 9-12h-7z"/>',
				'line' => '<path d="M3 12h18M6 12V6M18 12v6"/>'
			);

			/**
			 * Filters the SVG element markup used for front-end trip summary information icons.
			 *
			 * The array is keyed by icon identifier and each value contains the inner markup
			 * inserted unescaped into the common 24x24 SVG wrapper. Icon identifiers are
			 * resolved through the info item field-to-icon mapping. The filtered array is
			 * cached for the remainder of the request. A non-array result is ignored in favor
			 * of the defaults. Callbacks must provide trusted SVG element markup.
			 *
			 * @since 0.3.3
			 * @category Front-end Viewer
			 *
			 * @param array<string, string> $defaultIcons The default raw SVG inner markup keyed by icon identifier.
			 */
			$icons = apply_filters('abp01_info_item_icons',
				$defaultIcons);

			if (!is_array($icons) || empty($icons)) {
				$icons = $defaultIcons;
			}
		}

		return $icons;
	}
}

if (!function_exists('abp01_frontend_viewer_has_additional_tabs')) {
	function abp01_frontend_viewer_has_additional_tabs(stdClass $data): bool {
		$hasAdditionalTabs = !empty($data->additionalTabs) 
			&& is_array($data->additionalTabs);

		return $hasAdditionalTabs;
	}
}

if (!function_exists('abp01_count_frontend_viewer_tabs')) {
	function abp01_count_frontend_viewer_tabs(stdClass $data): int {
		$totalTabCount = 0;
		$hasAdditionalTabs = abp01_frontend_viewer_has_additional_tabs($data);

		if ($hasAdditionalTabs) {
			$totalTabCount = count($data->additionalTabs);
		}

		if ($data->track->exists) {
			$totalTabCount += 1;
		}

		if ($data->info->exists) {
			$totalTabCount += 1;
		}

		return $totalTabCount;
	}
}

if (!function_exists('abp01_frontend_viewer_maybe_full_tab_css_class')) {
	function abp01_frontend_viewer_maybe_full_tab_css_class(int|float $totalTabCount): string {
		return $totalTabCount === 1 ? 'abp01-full-tab' : '';
	}
}

if (!function_exists('abp01_frontend_determine_viewer_tab_width')) {
	function abp01_frontend_determine_viewer_tab_width(int|float $totalTabCount, int $maxTabsPerRow = 3): string {
		if ($maxTabsPerRow <= 0) {
			$maxTabsPerRow = 3;
		}

		$tabWidth = 100 / max(1, min($totalTabCount, $maxTabsPerRow));
		return 'calc(' . $tabWidth . '% - 1px)';
	}
}