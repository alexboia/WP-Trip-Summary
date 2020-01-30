<?php
class AuthTests extends WP_UnitTestCase {
    private static $roleKey;

    private static $initialRoleData;

    private static $testUsers = array(
        'administrator' => null,
        'editor' => null,
        'author' => null,
        'contributor' => null,
        'subscriber' => null
    );

    private static $testRoleData = array (
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

    private static function _capExistsInTestRoleData($capCode, $roleCode) {
        $roleCaps = self::$initialRoleData[$roleCode]['capabilities'];
        return isset($roleCaps[$capCode]) 
            && $roleCaps[$capCode] === true;
    }

    public static function setUpBeforeClass() {
        parent::setUpBeforeClass();

        self::$roleKey = wp_roles()->role_key;
        self::$initialRoleData = get_option(self::$roleKey, array());

        foreach (array_keys(self::$testUsers) as $roleCode) {
            self::$testUsers[$roleCode] = self::factory()->user->create(array(
                'role' => $roleCode
            ));
        }
    }

    public function setUp() {
        parent::setUp();
        update_option(self::$roleKey, self::$testRoleData);
    }

    public function tearDown() {
        parent::tearDown();
        update_option(self::$roleKey, self::$initialRoleData);
    }

    public static function tearDownAfterClass() {
        parent::tearDownAfterClass();
        foreach (array_keys(self::$testUsers) as $roleCode) {
            self::$testUsers[$roleCode] = null;
        }
    }

    public function testCanCheckIfCapCanBeInstalledForRole() {
        $auth = Abp01_Auth::getInstance();

        foreach ($auth->getCapabilities() as $roleCode => $capCodes) {
            foreach ($capCodes as $capCode) {
                $expectedAllowed = true;
                $requiredCaps = $auth->getRequiredCapabilities($capCode);
                
                if (!empty($requiredCaps)) {
                    $expectedAllowed = self::_capExistsInTestRoleData($capCode, $roleCode);
                }

                if ($expectedAllowed) {
                    $this->assertTrue($auth->capCanBeInstalledForRole($capCode, $roleCode));
                } else {
                    $this->assertFalse($auth->capCanBeInstalledForRole($capCode, $roleCode));
                }
            }
        }
    }

    public function testCanInstallCapabilities() {
        $auth = Abp01_Auth::getInstance();
        $auth->installCapabilities();

        foreach ($auth->getCapabilities() as $roleCode => $capCodes) {
            $role = get_role($roleCode);
            foreach ($capCodes as $capCode) {
                if ($auth->capCanBeInstalledForRole($capCode, $roleCode)) {
                    $this->assertTrue($role->has_cap($capCode));
                } else {
                    $this->assertFalse($role->has_cap($capCode));
                }
            }
        }
    }

    public function testCanRemoveCapabilities() {
        $auth = Abp01_Auth::getInstance();
        $auth->removeCapabilities();

        foreach ($auth->getCapabilities() as $roleCode => $capCodes) {
            $role = get_role($roleCode);
            foreach ($capCodes as $capCode) {
                $this->assertFalse($role->has_cap($capCode));
            }
        }
    }

    public function testCanCheckIfCanManagePluginSettings() {
        $auth = Abp01_Auth::getInstance();
        $auth->installCapabilities();

        foreach ($auth->getCapabilities() as $roleCode => $capCodes) {
            $expectedCanManageTripSummaryForRole = in_array(Abp01_Auth::CAP_MANAGE_TRIP_SUMMARY, $capCodes) 
                && $auth->capCanBeInstalledForRole(Abp01_Auth::CAP_MANAGE_TRIP_SUMMARY, $roleCode);

            wp_set_current_user(self::$testUsers[$roleCode]);
            if ($expectedCanManageTripSummaryForRole) {
                $this->assertTrue($auth->canManagePluginSettings());
            } else {
                $this->assertFalse($auth->canManagePluginSettings());
            }
        }
    }
}