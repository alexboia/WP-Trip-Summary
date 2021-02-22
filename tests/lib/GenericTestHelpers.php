<?php
/**
 * Copyright (c) 2014-2021 Alexandru Boia
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

trait GenericTestHelpers {
    private static $_faker = null;

    protected function _getEnqueuedStyleUrl($handle) {
        global $wp_styles;
        return $wp_styles->registered[$handle]->src;
    }

    protected function _ensureDirExists($dir) {
        if (!is_dir($dir)) {
            @mkdir($dir);
        }
    }

    protected function _recursiveCopyDirectory($source, $destination) {
        $entries = @scandir($source);
        if ($entries === false) {
            return;
        }

        if (!is_dir($destination)) {
            @mkdir($destination, 0777);
        }

        foreach ($entries as $entry) {
            if ($entry != '.' && $entry != '..') {
                $sourceEntry = $source . '/' . $entry;
                $destinationEntry = $destination . '/' . $entry;

                if (is_dir($sourceEntry)) {
                    if (!is_dir($destinationEntry)) {
                        @mkdir($destinationEntry, 0777);
                    }

                    $this->_recursiveCopyDirectory($sourceEntry, $destinationEntry);
                } else {
                    copy($sourceEntry, $destinationEntry);
                }
            }
        }

    }

    protected function _removeDirectoryRecursively($target) {
        $entries = @scandir($target, SCANDIR_SORT_ASCENDING);
        if ($entries === false) {
            return;
        }

        foreach ($entries as $entry) {
            if ($entry != '.' && $entry != '..') {
                $toRemove = $target . '/' . $entry;
                if (is_dir($toRemove)) {
                    $this->_removeDirectoryRecursively($toRemove);
                } else {
                    @unlink($toRemove);
                }
            }
        }

        @rmdir($target);
    }

    /**
     * @return \Faker\Generator
     */
    protected static function _getFaker() {
        if (self::$_faker == null) {
            self::$_faker = Faker\Factory::create();
            self::$_faker->addProvider(new GpxDocumentFakerDataProvider(self::$_faker, 0.1));
        }

        return self::$_faker;
    }

    protected function _assertFileNotEmpty($filePath) {
        return filesize($filePath) > 0;
    }

    protected function _removeAllFiles($targetDir, $globPattern) {
        $files = glob($targetDir . '/' . $globPattern);
        if (is_array($files)) {
            foreach ($files as $file) {
                @unlink($file);
            }
        }
    }

    protected function _createWpPosts($postIds) {
        $db = $this->_getDb();
        $postsTableName = $this->_getEnv()->getWpPostsTableName();

        foreach ($postIds as $postId) {
            $db->insert($postsTableName, $this->_generateWpPostData($postId));
        }
    }

    protected function _generateWpPostData($postId) {
        $faker = self::_getFaker();
        return array(
            'ID' => $postId,
            'post_title' => $faker->words(3, true),
            'post_content' => $faker->words(10, true),
            'guid' => $faker->uuid
        );
    }

    protected function _countObjectVars($obj) {
        return count(get_object_vars($obj));
    }

    public static function emptyValuesProvider() {
        return array(array(
            ''
        ), array(
            null
        ));
    }

    protected function _getRouteManager() {
        return Abp01_Route_Manager::getInstance();
    }

    protected function _getEnv() {
        return Abp01_Env::getInstance();
    }

    protected function _getDb() {
        return $this->_getEnv()->getDb();
    }

    protected function _getInstaller() {
        return new Abp01_Installer();
    }
}