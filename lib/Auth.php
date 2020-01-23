<?php
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

class Abp01_Auth {
    const CAP_WP_UPLOAD_FILES = 'upload_files';

    const CAP_MANAGE_TRIP_SUMMARY = 'abp01.cap.manageTourSummary';

    const CAP_EDIT_TRIP_SUMMARY = 'abp01.cap.editTourSummary';

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
                self::CAP_MANAGE_TRIP_SUMMARY,
                self::CAP_EDIT_TRIP_SUMMARY
            ),
            'editor' => array(
                self::CAP_EDIT_TRIP_SUMMARY
            ),
            'author' => array(
                self::CAP_EDIT_TRIP_SUMMARY
            ),
            'contributor' => array(
                self::CAP_EDIT_TRIP_SUMMARY
            )
        );
    }

    public function installCapabilities() {
        foreach ($this->_capabilities as $roleName => $caps) {
            $role = get_role($roleName);
            if ($role) {
                foreach ($caps as $cap) {
                    //If the role does not have the capability $cap
                    if (!$role->has_cap($cap)) {
                        //It is being assigned that capability only 
                        //  if it can upload files as well (has the capability 'upload_files'), since
                        //      a) the media buttons are not being added 
                        //      if the role does not have 'upload_files' (hence no 'media_buttons' hook)
                        //      b) we cannot forcibly add the 'upload_files' capability to the role, 
                        //      since it might break the way the site is expected to work
                        if ($role->has_cap(self::CAP_WP_UPLOAD_FILES)) {
                            $role->add_cap($cap);
                        }
                    } else if (!$role->has_cap(self::CAP_WP_UPLOAD_FILES)) {
                        //If the role does not have 'upload_files' but it DOES have the capability $cap
                        //  remove it, since it's pointless to have it assigned
                        $role->remove_cap($cap);
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

    public function canEditTripSummary($postId) {
        return current_user_can(self::CAP_EDIT_TRIP_SUMMARY) && current_user_can('edit_post', $postId);
    }

    public function canManageTripSummary() {
        return current_user_can(self::CAP_MANAGE_TRIP_SUMMARY);
    }

    public function canManagePluginSettings() {
        return current_user_can(self::CAP_MANAGE_TRIP_SUMMARY);
    }
}
