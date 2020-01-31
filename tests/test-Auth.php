<?php
class AuthTests extends WP_UnitTestCase {
    private static $_roleKey;

    private static $_initialRoleData;

    private static $_testPosts = array();

    private static $_testUsers = array();

    private static $_testRoleData = array (
        'administrator' => 
            array (
                'name' => 'Administrator',
                'capabilities' => 
                    array (
                        'switch_themes' => true,
                        'edit_themes' => true,
                        'activate_plugins' => true,
                        'edit_plugins' => true,
                        'edit_users' => true,
                        'edit_files' => true,
                        'manage_options' => true,
                        'moderate_comments' => true,
                        'manage_categories' => true,
                        'manage_links' => true,
                        'upload_files' => true,
                        'import' => true,
                        'unfiltered_html' => true,
                        'edit_posts' => true,
                        'edit_others_posts' => true,
                        'edit_published_posts' => true,
                        'publish_posts' => true,
                        'edit_pages' => true,
                        'read' => true,
                        'level_10' => true,
                        'level_9' => true,
                        'level_8' => true,
                        'level_7' => true,
                        'level_6' => true,
                        'level_5' => true,
                        'level_4' => true,
                        'level_3' => true,
                        'level_2' => true,
                        'level_1' => true,
                        'level_0' => true,
                        'edit_others_pages' => true,
                        'edit_published_pages' => true,
                        'publish_pages' => true,
                        'delete_pages' => true,
                        'delete_others_pages' => true,
                        'delete_published_pages' => true,
                        'delete_posts' => true,
                        'delete_others_posts' => true,
                        'delete_published_posts' => true,
                        'delete_private_posts' => true,
                        'edit_private_posts' => true,
                        'read_private_posts' => true,
                        'delete_private_pages' => true,
                        'edit_private_pages' => true,
                        'read_private_pages' => true,
                        'delete_users' => true,
                        'create_users' => true,
                        'unfiltered_upload' => true,
                        'edit_dashboard' => true,
                        'update_plugins' => true,
                        'delete_plugins' => true,
                        'install_plugins' => true,
                        'update_themes' => true,
                        'install_themes' => true,
                        'update_core' => true,
                        'list_users' => true,
                        'remove_users' => true,
                        'promote_users' => true,
                        'edit_theme_options' => true,
                        'delete_themes' => true,
                        'export' => true,
                    ),
            ),
        'editor' => 
            array (
                'name' => 'Editor',
                'capabilities' => 
                    array (
                        'moderate_comments' => true,
                        'manage_categories' => true,
                        'manage_links' => true,
                        'upload_files' => true,
                        'unfiltered_html' => true,
                        'edit_posts' => true,
                        'edit_others_posts' => true,
                        'edit_published_posts' => true,
                        'publish_posts' => true,
                        'edit_pages' => true,
                        'read' => true,
                        'level_7' => true,
                        'level_6' => true,
                        'level_5' => true,
                        'level_4' => true,
                        'level_3' => true,
                        'level_2' => true,
                        'level_1' => true,
                        'level_0' => true,
                        'edit_others_pages' => true,
                        'edit_published_pages' => true,
                        'publish_pages' => true,
                        'delete_pages' => true,
                        'delete_others_pages' => true,
                        'delete_published_pages' => true,
                        'delete_posts' => true,
                        'delete_others_posts' => true,
                        'delete_published_posts' => true,
                        'delete_private_posts' => true,
                        'edit_private_posts' => true,
                        'read_private_posts' => true,
                        'delete_private_pages' => true,
                        'edit_private_pages' => true,
                        'read_private_pages' => true,
                    ),
            ),
        'author' => 
            array (
                'name' => 'Author',
                'capabilities' => 
                    array (
                        'upload_files' => true,
                        'edit_posts' => true,
                        'edit_published_posts' => true,
                        'publish_posts' => true,
                        'read' => true,
                        'level_2' => true,
                        'level_1' => true,
                        'level_0' => true,
                        'delete_posts' => true,
                        'delete_published_posts' => true,
                    ),
            ),
        'contributor' => 
            array (
                'name' => 'Contributor',
                'capabilities' => 
                    array (
                        'edit_posts' => true,
                        'read' => true,
                        'level_1' => true,
                        'level_0' => true,
                        'delete_posts' => true,
                    ),
            ),
        'subscriber' => 
            array (
                'name' => 'Subscriber',
                'capabilities' => 
                    array (
                        'read' => true,
                        'level_0' => true,
                    ),
            ),
    );

    private static function _capabilityExistsInRoleData($capCode, $roleData) {
        $roleCaps = $roleData['capabilities'];
        return isset($roleCaps[$capCode]) 
            && $roleCaps[$capCode] === true;
    }

    private static function _capabilitiesExistsInRoleData($capCodes, $roleData) {
        $exist = true;
        foreach ($capCodes as $capCode) {
            if (!self::_capabilityExistsInRoleData($capCode, $roleData)) {
                $exist = false;
                break;
            }
        }
        return $exist;
    }

    private static function _capabilityExistsInTestRole($capCode, $roleName) {
        return self::_capabilityExistsInRoleData($capCode, self::$_testRoleData[$roleName]);
    }

    private static function _capabilitiesExistInTestRole($capCodes, $roleName) {
        return self::_capabilitiesExistsInRoleData($capCodes, self::$_testRoleData[$roleName]);
    }

    public static function setUpBeforeClass() {
        parent::setUpBeforeClass();

        self::$_roleKey = wp_roles()->role_key;
        self::$_initialRoleData = get_option(self::$_roleKey, array());

        foreach (self::$_testRoleData as $roleName => $roleData) {
            $userId = self::factory()->user->create(array(
                'role' => $roleName
            ));

            if (self::_capabilityExistsInRoleData('edit_posts', $roleData)) {
                //Avoid this: https://core.trac.wordpress.org/ticket/44416
                error_reporting(E_ALL & ~E_NOTICE);
                
                self::$_testPosts[$userId] = self::factory()->post->create(array(
                    'ID' => 0,
                    'post_type' => 'post',
                    'post_author' => $userId
                ));

                //Restore error reporting
                error_reporting(E_ALL);
            }

            self::$_testUsers[$roleName] = $userId;
        }
    }

    public function setUp() {
        parent::setUp();
        update_option(self::$_roleKey, self::$_testRoleData);
        wp_roles()->for_site();
    }

    public function tearDown() {
        parent::tearDown();
        update_option(self::$_roleKey, self::$_initialRoleData);
        wp_roles()->for_site();
    }

    public static function tearDownAfterClass() {
        parent::tearDownAfterClass();
        
        self::$_testUsers = array();
        self::$_testPosts = array();
        self::$_testRoleData = array();
        self::$_initialRoleData = array();
    }

    public function testCanCheckIfCapCanBeInstalledForRole_ourCapabilities() {
        $auth = $this->_getAuth();

        foreach ($auth->getCapabilities() as $roleName => $capCodes) {
            foreach ($capCodes as $capCode) {
                $expectedAllowed = true;
                $requiredCaps = $auth->getRequiredCapabilities($capCode);
                
                if (!empty($requiredCaps)) {
                    $expectedAllowed = self::_capabilitiesExistInTestRole($requiredCaps, $roleName);
                }

                if ($expectedAllowed) {
                    $this->assertTrue($auth->capCanBeInstalledForRole($capCode, $roleName));
                } else {
                    $this->assertFalse($auth->capCanBeInstalledForRole($capCode, $roleName));
                }
            }
        }
    }

    public function testTryCheckIfCapCanBeInstalledForRole_otherCapabilities() {
        $auth = $this->_getAuth();

        foreach (self::$_testRoleData as $roleName => $roleData) {
            $capabilities = $roleData['capabilities'];
            foreach ($capabilities as $capCode => $enabled) {
                $this->assertFalse($auth->capCanBeInstalledForRole($capCode, $roleName));
            }
        }
    }

    public function testCanInstallCapabilities() {
        $auth = $this->_getAuth();
        $auth->installCapabilities();

        foreach ($auth->getCapabilities() as $roleName => $capCodes) {
            $role = get_role($roleName);
            foreach ($capCodes as $capCode) {
                if ($auth->capCanBeInstalledForRole($capCode, $roleName)) {
                    $this->assertTrue($role->has_cap($capCode));
                } else {
                    $this->assertFalse($role->has_cap($capCode));
                }
            }
        }
    }

    public function testCanRemoveCapabilities() {
        $auth = $this->_getAuth();
        
        $auth->installCapabilities();
        $auth->removeCapabilities();

        foreach ($auth->getCapabilities() as $roleName => $capCodes) {
            $role = get_role($roleName);
            foreach ($capCodes as $capCode) {
                $this->assertFalse($role->has_cap($capCode));
            }
        }
    }

    public function testCanCheckIfCanManagePluginSettings_whenCapabilitiesInstalled() {
        $auth = $this->_getAuth();
        $auth->installCapabilities();

        foreach ($auth->getCapabilities() as $roleName => $capCodes) {
            $userId = self::$_testUsers[$roleName];

            $expectedCanManageTripSummary = 
                in_array(Abp01_Auth::CAP_MANAGE_TRIP_SUMMARY, $capCodes) 
                && $auth->capCanBeInstalledForRole(Abp01_Auth::CAP_MANAGE_TRIP_SUMMARY, $roleName);

            $this->_assertCanCheckIfCanManagePluginSettings($auth, 
                $userId, 
                $expectedCanManageTripSummary);
        }
    }

    public function testTryCheckIfCanManagePluginSettings_whenCapabilitiesNotInstalled() {
        $auth = $this->_getAuth();

        foreach ($auth->getCapabilities() as $roleName => $capCodes) {
            $userId = self::$_testUsers[$roleName];
            $this->_assertCanCheckIfCanManagePluginSettings($auth, 
                $userId, 
                false);
        }
    }

    public function testCanCheckIfCanEditTripSummary_whenCapabilitiesInstalled_ownPosts() {
        $auth = $this->_getAuth();
        $auth->installCapabilities();

        foreach (self::$_testUsers as $roleName => $userId) {
            if (isset(self::$_testPosts[$userId])) {
                $postId = self::$_testPosts[$userId];
                $expectedCanEditTripSummary = 
                    self::_capabilityExistsInTestRole('edit_posts', $roleName)
                    && $auth->capCanBeInstalledForRole(Abp01_Auth::CAP_EDIT_TRIP_SUMMARY, $roleName);

                $this->_assertCanEditTripSummary($auth, 
                    $userId, 
                    $postId, 
                    $expectedCanEditTripSummary);
            }
        }
    }

    public function testCanCheckIfCanEditTripSummary_whenCapabilitiesInstalled_othersPosts() {
        $auth = $this->_getAuth();
        $auth->installCapabilities();

        foreach (self::$_testUsers as $roleName => $userId) {
            if (isset(self::$_testPosts[$userId])) {
                $ownPostId = self::$_testPosts[$userId];

                foreach (self::$_testPosts as $postId) {
                    if ($postId != $ownPostId) {
                        $expectedCanEditTripSummary = 
                            self::_capabilityExistsInTestRole('edit_others_posts', $roleName)
                            && $auth->capCanBeInstalledForRole(Abp01_Auth::CAP_EDIT_TRIP_SUMMARY, $roleName);

                        $this->_assertCanEditTripSummary($auth, 
                            $userId, 
                            $postId, 
                            $expectedCanEditTripSummary);
                    }
                }
            }
        }
    }

    public function testTryCheckIfCanEditTripSummary_whenCapabilitiesNotInstalled_ownPosts() {
        $auth = $this->_getAuth();

        foreach (self::$_testUsers as $roleName => $userId) {
            if (isset(self::$_testPosts[$userId])) {
                $postId = self::$_testPosts[$userId];
                $this->_assertCanEditTripSummary($auth, 
                    $userId, 
                    $postId, 
                    false);
            }
        }
    }

    public function testTryCheckIfCanEditTripSummary_whenCapabilitiesNotInstalled_othersPosts() {
        $auth = $this->_getAuth();

        foreach (self::$_testUsers as $roleName => $userId) {
            if (isset(self::$_testPosts[$userId])) {
                $ownPostId = self::$_testPosts[$userId];

                foreach (self::$_testPosts as $postId) {
                    if ($postId != $ownPostId) {
                        $this->_assertCanEditTripSummary($auth, 
                            $userId, 
                            $postId, 
                            false);
                    }
                }
            }
        }
    }

    private function _assertCanCheckIfCanManagePluginSettings($auth, $userId, $expectedCanManageTripSummary) {
        wp_set_current_user($userId);

        if ($expectedCanManageTripSummary) {
            $this->assertTrue($auth->canManagePluginSettings());
        } else {
            $this->assertFalse($auth->canManagePluginSettings());
        }
    }

    private function _assertCanEditTripSummary($auth, $userId, $postId, $expectedCanEditTripSummary) {
        wp_set_current_user($userId);

        if ($expectedCanEditTripSummary) {
            $this->assertTrue($auth->canEditTripSummary($postId));
        } else {
            $this->assertFalse($auth->canEditTripSummary($postId));
        }
    }

    private function _getAuth() {
        return Abp01_Auth::getInstance();
    }
}