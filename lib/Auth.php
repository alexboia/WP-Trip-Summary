<?php
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
            'administrator' => array(self::CAP_MANAGE_TOUR_SUMMARY, self::CAP_EDIT_TOUR_SUMMARY),
            'editor' => array(self::CAP_EDIT_TOUR_SUMMARY)
        );
    }

    public function installCapabilities() {
        foreach ($this->_capabilities as $roleName => $caps) {
            $role = get_role($roleName);
            if  ($role) {
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
        return current_user_can(self::CAP_EDIT_TOUR_SUMMARY) &&
            current_user_can('edit_post', $postId);
    }

    public function canManageTourSummary() {
        return current_user_can(self::CAP_MANAGE_TOUR_SUMMARY);
    }
}