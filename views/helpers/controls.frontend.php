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
	function abp01_extract_value_from_frontend_data($data, $field) {
		if ($data->info && isset($data->info->$field)) {
			return $data->info->$field;
		} else {
			return null;
		}
	}
}

if (!function_exists('abp01_extract_displayable_info_item_value')) {
	function abp01_extract_displayable_info_item_value($rawValue) {
		return esc_html(is_object($rawValue) 
			? $rawValue->label 
			: $rawValue);
	}
}

if (!function_exists('abp01_render_info_item_value_part')) {
	function abp01_render_info_item_value_part($fieldValue, $valueIndex, $showCount, $layoutCssClass) {
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
	function abp01_render_info_item_value_more_link($countRemaining, $layoutCssClass) {
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
	function abp01_format_info_item_single_value($value, $suffix, $settings) {
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
	function abp01_format_info_item_multi_value($value, $suffix, $settings) {
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
	function abp01_format_info_item_value($value, $suffix, $settings) {
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
	 * @return string The formmated HTML output
	 */
	function abp01_display_info_item($data, $field, $fieldLabel, $suffix = '') {
		static $itemIndex = 0;
		$settings = $data->settings;
		$value = abp01_extract_value_from_frontend_data($data, $field);
		if (!empty($value)) {
			$fieldValue = abp01_format_info_item_value($value, $suffix, $settings);
			$itemOutput = ('<li class="abp01-info-item ' . $field . ' ' . ($itemIndex % 2 == 0 ? 'abp01-item-even' : 'abp01-item-odd') . '">')
				. ('<div class="abp01-info-label">' . esc_html($fieldLabel) . ':</div>')
				. ('<div class="abp01-info-value">' . $fieldValue . '</div>')
				. ('<div class="abp01-clear"></div>')
				. '</li>';

			$itemIndex ++;
		} else {
			$itemOutput = '';
		}

		echo $itemOutput;
	}
}

if (!function_exists('abp01_frontend_viewer_has_additional_tabs')) {
	function abp01_frontend_viewer_has_additional_tabs(stdClass $data) {
		$hasAdditionalTabs = !empty($data->additionalTabs) 
			&& is_array($data->additionalTabs);

		return $hasAdditionalTabs;
	}
}

if (!function_exists('abp01_count_frontend_viewer_tabs')) {
	function abp01_count_frontend_viewer_tabs(stdClass $data) {
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
	function abp01_frontend_viewer_maybe_full_tab_css_class($totalTabCount) {
		return $totalTabCount === 1 ? 'abp01-full-tab' : '';
	}
}

if (!function_exists('abp01_frontend_determine_viewer_tab_width')) {
	function abp01_frontend_determine_viewer_tab_width($totalTabCount, $maxTabsPerRow = 3) {
		if ($maxTabsPerRow <= 0) {
			$maxTabsPerRow = 3;
		}

		$tabWidth = 100 / max(1, min($totalTabCount, $maxTabsPerRow));
		return 'calc(' . $tabWidth . '% - 1px)';
	}
}