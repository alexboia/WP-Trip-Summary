<?php
if (!defined('ABP01_LOADED')) {
    die;
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
            $content .= '<option value="' . $option->id . '" '. ($selected == $option->id ? 'selected="selected"' : '') . '>' . $option->label . '</option>';
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
        $content .= '<input type="checkbox" name="' . $name . '[]" id="' . $id . '" ' . ($checked ? 'checked="checked"' : '') . ' value="' . $option->id . '" />';
        $content .= '<label for="' . $id . '" class="abp01-option-label">' . $option->label . '</label>';
        $content .= '</span>';

        echo $content;
    }
}

if (!function_exists('abp01_render_select_option')) {
	function abp01_render_select_option($option, $selectedValue) {
		$selected = ($selectedValue == $option->id || (is_array($selectedValue) && in_array($option->id, $selectedValue)));
		echo '<option value="' . $option->id . '" '. ($selected ? 'selected="selected"' : '') . '>' . $option->label . '</option>';
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