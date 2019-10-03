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

class Abp01_Auth {
    const CAP_MANAGE_TOUR_SUMMARY = 'abp01.cap.manageTourSummary';

    const CAP_EDIT_TOUR_SUMMARY = 'abp01.cap.editTourSummary';

    private $_capabilities = array();

    private static $_instance = null;

    public static function getInstance() {
        if (self::$_instance == null) {
            self::$_instance = new self();
        }
        return self::$_instance;
    }

    private function __construct() {
        $this->_capabilities = array(
            'administrator' => array(
                self::CAP_MANAGE_TOUR_SUMMARY,
                self::CAP_EDIT_TOUR_SUMMARY
            ),
            'editor' => array(
                self::CAP_EDIT_TOUR_SUMMARY
            )
        );
    }

    public function installCapabilities() {
        foreach ($this->_capabilities as $roleName => $caps) {
            $role = get_role($roleName);
            if ($role) {
                foreach ($caps as $cap) {
                    if (!$role->has_cap($cap)) {
                        $role->add_cap($cap);
                    }
                }
            }
        }
    }

    public function removeCapabilities() {
        foreach ($this->_capabilities as $roleName => $caps) {
            $role = get_role($roleName);
            if ($role) {
                foreach ($caps as $cap) {
                    if ($role->has_cap($cap)) {
                        $role->remove_cap($cap);
                    }
                }
            }
        }
    }

    public function canEditTourSummary($postId) {
        return current_user_can(self::CAP_EDIT_TOUR_SUMMARY) && current_user_can('edit_post', $postId);
    }

    public function canManageTourSummary() {
        return current_user_can(self::CAP_MANAGE_TOUR_SUMMARY);
    }

    public function canManagePluginSettings() {
        return current_user_can(self::CAP_MANAGE_TOUR_SUMMARY);
    }
}
