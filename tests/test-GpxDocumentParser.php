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

 class GpxDocumentParserTests extends WP_UnitTestCase {
	use GenericTestHelpers;
	use TestDataFileHelpers;
	use RouteTrackDocumentTestHelpers;

	private static $_randomGpxFilesTestInfo = array();

	public static function setUpBeforeClass() {
		parent::setUpBeforeClass();
		foreach (self::_getRandomFileGenerationSpec() as $fileName => $options) {
			self::_generateAndAddRandomGpxFile($fileName, $options);
		}
	}

	public static function tearDownAfterClass() {
		parent::tearDownAfterClass();
		self::_clearRandomGpxFiles();
	}

	private static function _generateAndAddRandomGpxFile($fileName, $options) {
		$faker = self::_getFaker();
		$gpx = $faker->gpx(array_merge($options, array(
			'addNoPretty' => true
		)));

		$data = $gpx['data'];
		$deltaPoint = self::_computeDeltaPoint($options);

		$expectMetadata = self::_determineExpectedMetadataInfo($data);
		$expectTrackParts = self::_determineExpectedTrackParts($data, $deltaPoint);
		$expectSampleWaypoints = self::_determineExpectedWaypoints($data, $deltaPoint);

		$unformatteGpxFileName = self::_computeUnformattedGpxFileName($fileName);

		self::$_randomGpxFilesTestInfo[$fileName] = array(
			'expect' => array(
				'document' => true,
				'metadata' => $expectMetadata,
				'trackParts' => $expectTrackParts,
				'waypoints' => $expectSampleWaypoints
			)
		);

		self::$_randomGpxFilesTestInfo[$unformatteGpxFileName] = array(
			'expect' => $fileName
		);

		self::_writeTestDataFileContents($fileName, 
			$gpx['content']['text']);
		self::_writeTestDataFileContents($unformatteGpxFileName, 
			$gpx['content']['textNoPretty']);
	}

	private static function _computeDeltaPoint($options) {
		return 1 / pow(10, $options['precision']);
	}

	private static function _determineExpectedMetadataInfo($generatedGpxData) {
		return array(
			'name' => !empty($generatedGpxData['metadata']['name']) 
				? $generatedGpxData['metadata']['name'] 
				: null,
			'desc' => !empty($generatedGpxData['metadata']['desc']) 
				? $generatedGpxData['metadata']['desc'] 
				: null,
			'keywords' => !empty($generatedGpxData['metadata']['keywords']) 
				? $generatedGpxData['metadata']['keywords'] 
				: null
		);
	}

	private static function _determineExpectedTrackParts($generatedGpxData, $deltaPoint) {
		$expectTrackParts = array();

		foreach ($generatedGpxData['content']['tracks'] as $track) {
			$expectTrackLines = array();
			foreach ($track['segments'] as $segment) {
				$samplePoints = array();
				foreach ($segment['points'] as $point) {
					$samplePoints[] = array_merge($point, array(
						'delta' => $deltaPoint
					));
				}
				$expectTrackLines[] = array(
					'trackPointsCount' => count($segment['points']),
					'sampleTrackPoints' => $samplePoints
				);
			}

			$expectTrackParts[] = array(
				'name' => !empty($track['name']) ? $track['name'] : null,
				'trackLines' => $expectTrackLines
			);
		}

		return $expectTrackParts;
	}

	private static function _determineExpectedWaypoints($generatedGpxData, $deltaPoint) {
		$expectSampleWaypoints = array();

		foreach ($generatedGpxData['content']['waypoints']['waypoints'] as $waypoint) {
			$expectSampleWaypoints[] = array_merge($waypoint, array(
				'delta' => $deltaPoint
			));
		}

		return $expectSampleWaypoints;
	}

	private static function _computeUnformattedGpxFileName($fileName) {
		return str_ireplace('.gpx', '-unformatted.gpx', 
			$fileName);
	}

	private static function _clearRandomGpxFiles() {
		foreach (array_keys(self::$_randomGpxFilesTestInfo) as $fileName) {
			unlink(self::_determineDataFilePath($fileName));
		}
		self::$_randomGpxFilesTestInfo = array();
	}

	public function test_canCheckIfSupported() {
		$this->assertEquals(function_exists('simplexml_load_string') && function_exists('simplexml_load_file'), 
			Abp01_Route_Track_DocumentParser_Gpx::isSupported());
	}

	public function test_canParse_correctDocument() {
		$testFiles = $this->_getValidTestFilesSpec();
		$parser = new Abp01_Route_Track_DocumentParser_Gpx();
		
		foreach ($testFiles as $fileName => $testFileSpec) {
			$fileContents = $this->_readTestDataFileContents($fileName); 
			$document = $parser->parse($fileContents);
			
			$expectedDocumentData = $this->_determineExpectedDocumentData($testFiles,
				 $testFileSpec);

			if ($expectedDocumentData['document'] === true) {
				$this->assertNotNull($document);
				$this->_assertMetadataCorrect($document, $expectedDocumentData['metadata']);
				$this->_assertTrackPartsCorrect($document, $expectedDocumentData['trackParts']);

				if (!empty($expectedDocumentData['waypoints'])) {
					$this->_assertWaypointsCorrect($document, $expectedDocumentData['waypoints']);
				}
			} else {
				$this->assertNull($document);
			}
		}
	}

	public function test_tryParse_incorrectDocument() {
		$testFiles = $this->_getInvalidTestFilesSpec();
		$parser = new Abp01_Route_Track_DocumentParser_Gpx();

		foreach ($testFiles as $fileName) {
			$fileContents = $this->_readTestDataFileContents($fileName); 
			$document = $parser->parse($fileContents);
			$this->assertNull($document);
			$this->assertTrue($parser->hasErrors());
			$this->assertNotEmpty($parser->getLastErrors());
		}
	}

	/**
	 * @expectedException InvalidArgumentException
	 */
	public function test_tryParse_nullData() {
		$parser = new Abp01_Route_Track_DocumentParser_Gpx();
		$parser->parse(null);
	}

	/**
	 * @expectedException InvalidArgumentException
	 */
	public function test_tryParse_emptyData() {
		$parser = new Abp01_Route_Track_DocumentParser_Gpx();
		$parser->parse('');
	}

	private function _assertMetadataCorrect(Abp01_Route_Track_Document $actualDocument, $expectMeta) {
		$this->assertNotNull($actualDocument->getMetadata());
		$this->assertTrue($this->_isMetadataNameCorrect($actualDocument, $expectMeta));
		$this->assertTrue($this->_isMetadataDescriptionCorrect($actualDocument, $expectMeta));
		$this->assertTrue($this->_areMetadataKeywordsCorrect($actualDocument, $expectMeta));
	}

	private function _assertWaypointsCorrect(Abp01_Route_Track_Document $actualDocument, $expectWaypoints) {
		$this->assertTrue($this->_areDocumentWayPointsCorrect($actualDocument, $expectWaypoints));
	}

	private function _assertTrackPartsCorrect(Abp01_Route_Track_Document $actualDocument, $expectTrackPartsSpec) {
		$this->assertTrue($this->_areAllTrackPartsCorrect($actualDocument, $expectTrackPartsSpec));
	}

	private static function _getRandomFileGenerationSpec() {
		return array(
			'test6-2tracks-4segments-4000points-nometa.gpx' => array(
				'precision' => 4,
				'tracks' => array(
					'count' => 2
				),
				'segments' => array(
					'count' => 4,
				),
				'points' => array(
					'count' => 4000
				),
				'metadata' => false
			),

			'test6-2tracks-4segments-4000points-nowpt-nometa.gpx' => array(
				'precision' => 4,
				'tracks' => array(
					'count' => 2
				),
				'segments' => array(
					'count' => 4,
				),
				'points' => array(
					'count' => 4000
				),
				'waypoints' => false,
				'metadata' => false
			),

			'test7-1track-1segments-1000points-nowpt-wpartialmeta.gpx' => array(
				'precision' => 4,
				'tracks' => array(
					'count' => 1
				),
				'segments' => array(
					'count' => 1,
				),
				'points' => array(
					'count' => 1000
				),
				'waypoints' => false,
				'metadata' => array(
					'name' => true,
					'desc' => true,
					'keywords' => false,
					'author' => false,
					'copyright' => false,
					'link' => false,
					'time' => false,
					'bounds' => false
				)
			),

			'test7-1track-1segments-1000points-wpartialmeta.gpx' => array(
				'precision' => 4,
				'tracks' => array(
					'count' => 1
				),
				'segments' => array(
					'count' => 1,
				),
				'points' => array(
					'count' => 1000
				),
				'waypoints' => true,
				'metadata' => array(
					'name' => true,
					'desc' => true,
					'keywords' => false,
					'author' => false,
					'copyright' => false,
					'link' => false,
					'time' => false,
					'bounds' => false
				)
			)
		);
	}

	private function _getValidTestFilesSpec() {
		return array_merge(array(
			'test1-garmin-desktop-app-utf8-bom.gpx' => array(
				'expect' => array(
					'document' => true,
					'metadata' => array(
						'name' => null,
						'desc' => null,
						'keywords' => null
					),
					'trackParts' => array(
						array(
							'name' => 'POGGIOLO-LAGO DI RIDRACOLI-CA\' DI SOPRA-POGGIO PALESTRINA-PASSO VINCO-MONTEPEZZOLO-IL MONTE 2020-01-08 16:25:54',
							'trackLines' => array(
								array(
									'trackPointsCount' => 2718,
									'sampleTrackPoints' => array(
										//Pick some points at the start of the line
										array(
											'lat' => 43.915864191949368,
											'lon' => 11.864037103950977,
											'ele' => 380.33999999999997,
											'delta' => 0.00
										),
										array(
											'lat' => 43.915851535275578,
											'lon' => 11.864051101729274,
											'ele' => 380.81999999999999,
											'delta' => 0.00
										),

										//Pick some points somewhere in the middle of the line
										array(
											'lat' => 43.914778651669621,
											'lon' => 11.861909944564104,
											'ele' => 408.69999999999999,
											'delta' => 0.00
										),
										array(
											'lat' => 43.914694245904684,
											'lon' => 11.861764518544078,
											'ele' => 410.13999999999999,
											'delta' => 0.00
										),

										//Pick some points at the end of the line
										array(
											'lat' => 43.915900150313973,
											'lon' => 11.864040205255151,
											'ele' => 380.33999999999997,
											'delta' => 0.00
										),
										array(
											'lat' => 43.915909621864557,
											'lon' => 11.864063674584031,
											'ele' => 379.86000000000001,
											'delta' => 0.00
										),
									)
								)
							)
						)
					)
				)
			),
			'test1-garmin-desktop-app-utf8-wo-bom.gpx' => array(
				'expect' => 'test1-garmin-desktop-app-utf8-bom.gpx'
			),
			'test3-bikemap-utf8-bom.gpx' => array(
				'expect' => array(
					'document' => true,
					'metadata' => array(
						'name' => 'PDM #4 - Meridionalii de Vest',
						'desc' => null,
						'keywords' => null
					),
					'trackParts' => array(
						array(
							'name' => 'PDM #4 - Meridionalii de Vest',
							'trackLines' => array(
								array(
									'trackPointsCount' => 7115,
									'sampleTrackPoints' => array(
										//Pick some points at the start of the line
										array(
											'lat' => 45.0391,
											'lon' => 23.26416,
											'ele' => 201,
											'delta' => 0.00
										),
										array(
											'lat' => 45.03761,
											'lon' => 23.25927,
											'ele' => 200,
											'delta' => 0.00
										),

										//Pick some points somewhere in the middle of the line
										array(
											'lat' => 45.04968,
											'lon' => 23.22269,
											'ele' => 217,
											'delta' => 0.00
										),
										array(
											'lat' => 45.04986,
											'lon' => 23.22244,
											'ele' => 218,
											'delta' => 0.00
										),

										//Pick some points at the end of the line
										array(
											'lat' => 44.85752,
											'lon' => 22.38765,
											'ele' => 139,
											'delta' => 0.00
										),
										array(
											'lat' => 44.85767,
											'lon' => 22.38772,
											'ele' => 140,
											'delta' => 0.00
										),
									)
								)
							)
						)
					)
				)
			),
			'test3-bikemap-utf8-wo-bom.gpx' => array(
				'expect' => 'test3-bikemap-utf8-bom.gpx'
			),
			'test2-strava-utf8-bom.gpx' => array(
				'expect' => array(
					'document' => true,
					'metadata' => array(
						'name' => null,
						'desc' => null,
						'keywords' => null
					),
					'trackParts' => array(
						array(
							'name' => 'B4M Day 1, 05/06/2014 Câmpulung, AG, Romania',
							'trackLines' => array(
								array(
									'trackPointsCount' => 2539,
									'sampleTrackPoints' => array(
										//Pick some points at the start of the line
										array(
											'lat' => 45.2737100,
											'lon' => 25.0463070,
											'ele' => 600.9,
											'delta' => 0.00
										),
										array(
											'lat' => 45.2741060,
											'lon' => 25.0465750,
											'ele' => 601.9,
											'delta' => 0.00
										),

										//Pick some points somewhere in the middle of the line
										array(
											'lat' => 45.2646360,
											'lon' => 25.1682900,
											'ele' => 750.6,
											'delta' => 0.00
										),
										array(
											'lat' => 45.2644980,
											'lon' => 25.1683710,
											'ele' => 746.6,
											'delta' => 0.00
										),

										//Pick some points at the end of the line
										array(
											'lat' => 45.2615810,
											'lon' => 25.1737230,
											'ele' => 575.4,
											'delta' => 0.00
										),
										array(
											'lat' => 45.2615530,
											'lon' => 25.1737240,
											'ele' => 575.4,
											'delta' => 0.00
										)
									)
								)
							)
						)
					)
				)
			),
			'test2-strava-utf8-wo-bom.gpx' => array(
				'expect' => 'test2-strava-utf8-bom.gpx'
			),
			'test4-empty-utf8-bom.gpx' => array(
				'expect' => array(
					'document' => true,
					'metadata' => array(
						'name' => null,
						'desc' => null,
						'keywords' => null
					),
					'trackParts' => array()
				)
			),
			'test4-empty-utf8-wo-bom.gpx' => array(
				'expect' => 'test4-empty-utf8-bom.gpx'
			),
			'test5-empty-wmeta-wtrkroot-utf8-bom.gpx' => array(
				'expect' => array(
					'document' => true,
					'metadata' => array(
						'name' => 'PDM #4 - Meridionalii de Vest',
						'desc' => 'PDM #4 - Meridionalii de Vest DESC',
						'keywords' => 'kw1,kw2,kw3'
					),
					'trackParts' => array(
						array(
							'name' => null,
							'trackLines' => array()
						)
					)
				)
			),
			'test5-empty-wmeta-wtrkroot-utf8-wo-bom.gpx' => array(
				'expect' => 'test5-empty-wmeta-wtrkroot-utf8-bom.gpx'
			)
		), self::$_randomGpxFilesTestInfo);
	}

	private function _getInvalidTestFilesSpec() {
		return array(
			'test-inv1-jibberish.gpx', 
			'test-inv2-jibberish-malformed.gpx'
		);
	}

	protected static function _getRootTestsDir() {
		return __DIR__;
	}
 }