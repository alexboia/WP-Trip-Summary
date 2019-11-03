<?php
/**
 * Copyright (c) 2014-2019 Alexandru Boia
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
    function abp01_extract_value_from_frontend_data($data, $field) {
        if ($data->info && isset($data->info->$field)) {
            return $data->info->$field;
        } else {
            return null;
        }
    }
}

if (!function_exists('abp01_format_info_item_value')) {
    function abp01_format_info_item_value($value, $suffix) {
        $fieldValue = '';

        if (!empty($value)) {
            if (is_array($value)) {
                $i = 0;
                $k = count($value);
                foreach ($value as $v) {
                    $fieldValue .= esc_html(is_object($v) ? $v->label : $v);
                    if ($i ++ < $k - 1) {
                        $fieldValue .= ', ';
                    }
                }
            } else {
                $fieldValue .= esc_html(is_object($value) ? $value->label : $value);
            }
            if (!empty($suffix)) {
                $fieldValue .= ' ' . $suffix;
            }
        }

        return $fieldValue;
    }
}

if (!function_exists('abp01_display_info_item')) {
    function abp01_display_info_item($data, $field, $fieldLabel, $suffix = '') {
        static $itemIndex = 0;
        $value = abp01_extract_value_from_frontend_data($data, $field);
        if (!empty($value)) {
            $fieldValue = abp01_format_info_item_value($value, $suffix);
            $itemOutput = ('<li class="abp01-info-item ' . $field . ' ' . ($itemIndex % 2 == 0 ? 'abp01-item-even' : 'abp01-item-odd') . '">')
                . ('<span class="abp01-info-label">' . esc_html($fieldLabel) . ':</span>')
                . ('<span class="abp01-info-value">' . esc_html($fieldValue) . '</span>')
                . '</li>';

            $itemIndex ++;
        } else {
            $itemOutput = '';
        }

        echo $itemOutput;
    }
}