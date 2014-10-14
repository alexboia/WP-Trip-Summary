<?php
if (!defined('ABP01_LOADED')) {
    die;
}

function extractValueFromFrontendData($data, $field) {
    if ($data->info && isset($data->info->$field)) {
        return $data->info->$field;
    } else {
        return null;
    }
}

function displayInfoItem($data, $field, $fieldLabel, $suffix = '') {
    static $itemIndex = 0;
    $value = extractValueFromFrontendData($data, $field);
    if (!empty($value)) {
        $output = '<li class="abp01-info-item ' . $field . ' ' . ($itemIndex % 2 == 0 ? 'abp01-item-even' : 'abp01-item-odd') . '">';
        $output .= '<span class="abp01-info-label">' . $fieldLabel . ':</span>';
        $output .= '<span class="abp01-info-value">';
        if (is_array($value)) {
            $i = 0;
            $k = count($value);
            foreach ($value as $v) {
                $output .= (is_object($v) ? $v->label : $v);
                if ($i ++ < $k - 1) {
                    $output .= ', ';
                }
            }
        } else {
            $output .= (is_object($value) ? $value->label : $value);
        }
        if (!empty($suffix)) {
            $output .= ' ' . $suffix;
        }
        $output .= '</span>';
        $output .= '</li>';
        $itemIndex ++;
    } else {
        $output = '';
    }
    echo $output;
}