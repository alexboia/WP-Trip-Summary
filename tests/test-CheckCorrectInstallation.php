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

class CheckCorrectInstallation extends WP_UnitTestCase {
    use LookupDataTestHelpers;

    private static $_checkLookupData = array(
        'difficultyLevel' => array(
            array(
                'default' => 'Easy',
                'translations' => array(
                    'ro_RO' => 'Ușor'
                )
            ),
            array(
                'default' => 'Medium',
                'translations' => array(
                    'ro_RO' => 'Mediu'
                )
            ),
            array(
                'default' => 'Hard',
                'translations' => array(
                    'ro_RO' => 'Dificil'
                )
            ),
            array(
                'default' => 'Medieval torture',
                'translations' => array(
                    'ro_RO' => 'Tortură medievală'
                )
            )
        ),

        'pathSurfaceType' => array(
            array(
                'default' => 'Asphalt',
                'translations' => array(
                    'ro_RO' => 'Asfalt'
                )
            ),
            array(
                'default' => 'Concrete',
                'translations' => array(
                    'ro_RO' => 'Plăci de beton'
                )
            ),
            array(
                'default' => 'Dust or dirt',
                'translations' => array(
                    'ro_RO' => 'Pământ'
                )
            ),
            array(
                'default' => 'Grass',
                'translations' => array(
                    'ro_RO' => 'Iarbă'
                )
            ),
            array(
                'default' => 'Stone pavement/Gravel',
                'translations' => array(
                    'ro_RO' => 'Macadam'
                )
            ),
            array(
                'default' => 'Loose rocks',
                'translations' => array(
                    'ro_RO' => 'Piatră neașezată'
                )
            )
        ),

        'bikeType' => array(
            array(
                'default' => 'MTB',
                'translations' => array(
                    'ro_RO' => 'MTB'
                )
            ),
            array(
                'default' => 'Road bike',
                'translations' => array(
                    'ro_RO' => 'Cursieră'
                )
            ),
            array(
                'default' => 'Trekking',
                'translations' => array(
                    'ro_RO' => 'Trekking'
                )
            ),
            array(
                'default' => 'City bike',
                'translations' => array(
                    'ro_RO' => 'Bicicletă de oraș'
                )
            )
        ),

        'railroadLineType' => array(
            array(
                'default' => 'Simple line',
                'translations' => array(
                    'ro_RO' => 'Linie simplă'
                )
            ),
            array(
                'default' => 'Double line',
                'translations' => array(
                    'ro_RO' => 'Linie dublă'
                )
            )
        ),

        'railroadOperator' => array(),

        'railroadLineStatus' => array(
            array(
                'default' => 'In production',
                'translations' => array(
                    'ro_RO' => 'În exploatare'
                )
            ),
            array(
                'default' => 'Closed',
                'translations' => array(
                    'ro_RO' => 'Închisă'
                )
            ),
            array(
                'default' => 'Disbanded',
                'translations' => array(
                    'ro_RO' => 'Desființată'
                )
            ),
            array(
                'default' => 'In rehabilitation',
                'translations' => array(
                    'ro_RO' => 'În reabilitare'
                )
            )
        ),

        'recommendSeasons' => array(
            array(
                'default' => 'Spring',
                'translations' => array(
                    'ro_RO' => 'Primăvara'
                )
            ),
            array(
                'default' => 'Summer',
                'translations' => array(
                    'ro_RO' => 'Vara'
                )
            ),
            array(
                'default' => 'Autumn',
                'translations' => array(
                    'ro_RO' => 'Toamna'
                )
            ),
            array(
                'default' => 'Winter',
                'translations' => array(
                    'ro_RO' => 'Iarna'
                )
            )
        ),

        'railroadElectrificationStatus' => array(
            array(
                'default' => 'Electrified',
                'translations' => array(
                    'ro_RO' => 'Electrificată'
                )
            ),
            array(
                'default' => 'Not electrified',
                'translations' => array(
                    'ro_RO' => 'Neelectrificată'
                )
            ),
            array(
                'default' => 'Partially electrified',
                'translations' => array(
                    'ro_RO' => 'Partial electrificată'
                )
            )
        )
    );

    public function testDBTablesArePresent() {
        $env = $this->_getEnv();

        $checkTables = array(
            $env->getLookupTableName(),
            $env->getLookupLangTableName(),
            $env->getRouteDetailsTableName(),
            $env->getRouteTrackTableName(),
            $env->getRouteDetailsLookupTableName()
        );

        $db = $env->getMetaDb();

        $db->where('TABLE_SCHEMA', $env->getDbName())
            ->where('TABLE_NAME', $checkTables, 'IN');

        $checkedTables = $db->get('TABLES', null, 'TABLE_NAME');

        $this->assertEquals(count($checkTables), 
            count($checkedTables));

        foreach ($checkedTables as $table) {
            $this->assertContains($table['TABLE_NAME'], $checkTables, '', true);
        }
    }

    public function testStorageDirectoriesArePresent() {
        $env = $this->_getEnv();

        $checkDirs = array(
            $env->getRootStorageDir(),
            $env->getCacheStorageDir(),
            $env->getTracksStorageDir()
        );

        foreach ($checkDirs as $dir) {
            $this->assertDirectoryIsReadable($dir);

            $guardIndexPhpFile = $dir . DIRECTORY_SEPARATOR . 'index.php';
            $this->assertFileIsReadable($guardIndexPhpFile);

            if ($dir != $env->getRootStorageDir()) {
                $guardHtaccessfile = $dir . DIRECTORY_SEPARATOR . '.htaccess';
                $this->assertFileIsReadable($guardHtaccessfile);
            }
        }
    }

    public function testCorrectVersionNumber() {
        $expectedVersion = $this->_getEnv()->getVersion();
        $actualVersion = get_option(Abp01_Installer::OPT_VERSION);

        $this->assertEquals(ABP01_VERSION, 
            $expectedVersion);

        $this->assertEquals($expectedVersion, 
            $actualVersion);
    }

    public function testInitialLookupDataItemsArePresent() {
        $env = $this->_getEnv();
        $db = $env->getDb();

        foreach (self::$_checkLookupData as $category => $expectedData) {
            $db->where('lookup_category', $category);
            $dbData = $db->get($env->getLookupTableName());

            if (!empty($dbData)) {
                $this->assertEquals(count($expectedData), 
                    count($dbData));

                foreach ($dbData as $row) {
                    $id  = intval($row['ID']);
                    $actualDefaultLabel = $row['lookup_label'];

                    $actualTranslations = $this->_getTranslations($id);

                    $this->_assertExpectedDataContains($expectedData, 
                        $actualDefaultLabel, 
                        $actualTranslations);
                }
            } else {
                $this->assertEmpty($expectedData);
            }
        }
    }

    private function _assertExpectedDataContains($expectedData, $actualDefaultLabel, $actualTranslations) {
        $expectedDataItem = null;

        foreach ($expectedData as $item) {
            if (strtolower($item['default']) == strtolower($actualDefaultLabel)) {
                $expectedDataItem = $item;
            }
        }

        $this->assertNotNull($expectedDataItem);

        $this->assertEquals(count($expectedDataItem['translations']), 
            count($actualTranslations));

        foreach ($expectedDataItem['translations'] as $expectedLang => $expectedLabel) {
            $this->assertTrue(!empty($actualTranslations[$expectedLang]));
            $this->assertEquals($expectedLabel, $actualTranslations[$expectedLang]);
        }
    }

    private function _getTranslations($lookupId) {
        $env = $this->_getEnv();
        $db = $env->getDb();
        $db->where('ID', $lookupId);

        $translations = array();
        $dbTranslations = $db->get($env->getLookupLangTableName());

        foreach ($dbTranslations as $row) {
            $translations[$row['lookup_lang']] = $row['lookup_label'];
        }

        return $translations;
    }

    protected function _getEnv() {
        return Abp01_Env::getInstance();
    }

    protected function _getInstaller() {
        return new Abp01_Installer();
    }
 }