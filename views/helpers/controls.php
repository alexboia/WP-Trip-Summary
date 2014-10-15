<?php
if (!defined('ABP01_LOADED')) {
    die;
}

function extractValueFromData($data, $field) {
    if ($data->tourInfo && isset($data->tourInfo[$field])) {
        return $data->tourInfo[$field];
    } else {
        return null;
    }
}

function renderDifficultyLevelOptions(array $difficultyLevels, $selected) {
    $content = '';
    foreach ($difficultyLevels as $option) {
        $content .= '<option value="' . $option->id . '" '. ($selected == $option->id ? 'selected="selected"' : '') . '>' . $option->label . '</option>';
    }
    echo $content;
}

function renderCheckboxOption($option, $fieldName, $selected) {
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

function renderCheckboxOptions(array $options, $fieldName, $data) {
    $selected = extractValueFromData($data, $fieldName);
    foreach ($options as $option) {
        renderCheckboxOption($option, $fieldName, $selected);
    }
}