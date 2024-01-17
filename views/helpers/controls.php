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

if (!function_exists('abp01_render_partial_view')) {
	function abp01_render_partial_view($view, $data) {
		ob_start();
		$viewFile = abp01_get_env()->getViewFilePath($view);
		require $viewFile;
		return ob_get_clean();
	}
}

if (!function_exists('abp01_extract_value_from_data')) {
	function abp01_extract_value_from_data($data, $field) {
		if ($data->tourInfo && isset($data->tourInfo[$field])) {
			return $data->tourInfo[$field];
		} else {
			return null;
		}
	}
}

if (!function_exists('abp01_render_difficulty_level_options')) {
	function abp01_render_difficulty_level_options(array $difficultyLevels, $selected) {
		$content = '';
		foreach ($difficultyLevels as $option) {
			$content .= '<option value="' . esc_attr($option->id) . '" '. ($selected == $option->id ? 'selected="selected"' : '') . '>' . esc_html($option->label) . '</option>';
		}
		echo $content;
	}
}

if (!function_exists('abp01_render_checkbox_option')) {
	function abp01_render_checkbox_option($option, $fieldName, $selected) {
		$content = '';
		$id = 'ctrl_abp01_' . $fieldName . '_' . $option->id;
		$checked = ($selected == $option->id || (is_array($selected) && in_array($option->id, $selected)));
		$name = 'ctrl_abp01_' . $fieldName;

		$content .= '<span class="abp01-optionContainer">';
		$content .= '<input type="checkbox" name="' . $name . '[]" id="' . $id . '" ' . ($checked ? 'checked="checked"' : '') . ' value="' . esc_attr($option->id) . '" />';
		$content .= '<label for="' . $id . '" class="abp01-option-label">' . esc_html($option->label) . '</label>';
		$content .= '</span>';

		echo $content;
	}
}

if (!function_exists('abp01_render_select_option')) {
	function abp01_render_select_option($option, $selectedValue) {
		$selected = ($selectedValue == $option->id || (is_array($selectedValue) && in_array($option->id, $selectedValue)));
		echo '<option value="' . esc_attr($option->id) . '" '. ($selected ? 'selected="selected"' : '') . '>' . esc_html($option->label) . '</option>';
	}
}

if (!function_exists('abp01_render_checkbox_options')) {
	function abp01_render_checkbox_options(array $options, $fieldName, $data) {
		$selected = abp01_extract_value_from_data($data, $fieldName);
		foreach ($options as $option) {
			abp01_render_checkbox_option($option, $fieldName, $selected);
		}
	}
}

if (!function_exists('abp01_render_select_options')) {
	function abp01_render_select_options(array $options, $fieldName, $data) {
		$selectedValue = abp01_extract_value_from_data($data, $fieldName);
		foreach ($options as $option) {
			abp01_render_select_option($option, $selectedValue);
		}
	}
}

if (!function_exists('abp01_get_editor_difficulty_level_label')) {
	function abp01_get_editor_difficulty_level_label() {
		return abp01_get_lookup_type_label(Abp01_Lookup::DIFFICULTY_LEVEL);
	}
}

if (!function_exists('abp01_get_editor_recommended_seasons_label')) {
	function abp01_get_editor_recommended_seasons_label() {
		return abp01_get_lookup_type_label(Abp01_Lookup::RECOMMEND_SEASONS);
	}
}

if (!function_exists('abp01_get_editor_path_surface_type_label')) {
	function abp01_get_editor_path_surface_type_label() {
		return abp01_get_lookup_type_label(Abp01_Lookup::PATH_SURFACE_TYPE);
	}
}

if (!function_exists('abp01_get_editor_bike_type_label')) {
	function abp01_get_editor_bike_type_label() {
		return abp01_get_lookup_type_label(Abp01_Lookup::BIKE_TYPE);
	}
}

if (!function_exists('abp01_get_editor_railroad_operator_label')) {
	function abp01_get_editor_railroad_operator_label() {
		return abp01_get_lookup_type_label(Abp01_Lookup::RAILROAD_OPERATOR);
	}
}

if (!function_exists('abp01_get_editor_railroad_line_status_label')) {
	function abp01_get_editor_railroad_line_status_label() {
		return abp01_get_lookup_type_label(Abp01_Lookup::RAILROAD_LINE_STATUS);
	}
}

if (!function_exists('abp01_get_editor_railroad_electrification_status_label')) {
	function abp01_get_editor_railroad_electrification_status_label() {
		return abp01_get_lookup_type_label(Abp01_Lookup::RAILROAD_ELECTRIFICATION);
	}
}

if (!function_exists('abp01_get_editor_railroad_line_type_label')) {
	function abp01_get_editor_railroad_line_type_label() {
		return abp01_get_lookup_type_label(Abp01_Lookup::RAILROAD_LINE_TYPE);
	}
}