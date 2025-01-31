<?php
/**
 * Copyright (c) 2014-2025 Alexandru Boia and Contributors
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

trait TestAuthDataHelpers {
	protected function _getBuiltInRoleIds() {
		return array_keys(wp_roles()->get_names());
	}

	protected function _getActualCapabilitiesForUser($userId, $capabilities) {
		$actualCapabilities = array();
		foreach ($capabilities as $cap) {
			$primitiveCaps = map_meta_cap($cap, $userId);
			if (!in_array('do_not_allow', $primitiveCaps, true)) {
				$actualCapabilities = array_merge($actualCapabilities, $primitiveCaps);
			}
		}
		return array_unique($actualCapabilities);
	}

	protected function _getBuiltInCapabilitiesInRole($roleId) {
		$capabilityIds = array();
		$capabilityPairs = get_role($roleId)->capabilities;
		foreach ($capabilityPairs as $id => $has) {
			if ($has) {
				$capabilityIds[] = $id;
			}
		}
		return $capabilityIds;
	}

	protected function _getAllAvailableBuiltInCapabilities() {
		$capabilities = array();
		$roles = $this->_getBuiltInRoleIds();

		foreach ($roles as $roleId) {
			$capabilities = array_merge($capabilities, array_keys(get_role($roleId)->capabilities));
		}

		return array_unique($capabilities);
	}

	protected function _getTestRoleData() {
		return array (
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
	}
}