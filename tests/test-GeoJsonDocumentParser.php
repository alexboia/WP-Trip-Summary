<?php
/**
 * Copyright (c) 2014-2024 Alexandru Boia and Contributors
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

use Yoast\PHPUnitPolyfills\Polyfills\ExpectException;

class GeoJsonDocumentParserTests extends WP_UnitTestCase {
	use ExpectException;
	use GenericTestHelpers;
	use TestDataFileHelpers;
	use RouteTrackDocumentTestHelpers;

	private static $_randomGeoJsonFilesTestInfo = array();

	public static function setUpBeforeClass(): void {
		parent::setUpBeforeClass();
		foreach (self::_getRandomFileGenerationSpec() as $fileName => $options) {
			self::_generateAndAddRandomGeoJsonFile($fileName, $options);
		}
	}

	private static function _getRandomFileGenerationSpec() {
		return array(
			'geojson/test6-2tracks-4segments-4000points-nometa.geojson' => array(
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

			'geojson/test6-2tracks-4segments-4000points-nowpt-nometa.geojson' => array(
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

			'geojson/test7-1track-1segments-1000points-nowpt-wpartialmeta.geojson' => array(
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

			'geojson/test7-1track-1segments-1000points-wpartialmeta.geojson' => array(
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

	private static function _generateAndAddRandomGeoJsonFile($fileName, $options) {
		$faker = self::_getFaker();
		$geojonDocument = $faker->geoJson(array_merge($options, array(
			'addNoPretty' => true
		)));

		$expectations = self::_saveDocumentAndDetermineExpectations($fileName, 
			$geojonDocument, 
			$options);

		self::$_randomGeoJsonFilesTestInfo = array_merge(self::$_randomGeoJsonFilesTestInfo, 
			$expectations);
	}

	public static function tearDownAfterClass(): void {
		parent::tearDownAfterClass();
		self::_clearRandomGeoJsonFiles();
	}

	private static function _clearRandomGeoJsonFiles() {
		$fileNames = array_keys(self::$_randomGeoJsonFilesTestInfo);
		self::_deleteAllDataFiles($fileNames);
		self::$_randomGeoJsonFilesTestInfo = array();
	}

	public function test_canCheckIfSupported() {
		$this->assertEquals(function_exists('json_decode'), 
			Abp01_Route_Track_DocumentParser_GeoJson::isSupported());
	}

	public function test_canParse_correctDocument() {
		$testFiles = $this->_getValidTestFilesSpec();
		$parser = new Abp01_Route_Track_DocumentParser_GeoJson();
		
		foreach ($testFiles as $fileName => $testFileSpec) {
			$fileContents = $this->_readTestDataFileContents($fileName); 
			$document = $parser->parse($fileContents);
			
			$expectedDocumentData = $this->_determineExpectedDocumentData($testFiles,
				$testFileSpec);

			if ($expectedDocumentData['document'] === true) {
				$this->assertNotNull($document);
				//$this->_assertMetadataCorrect($document, $expectedDocumentData['metadata']);
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
		$parser = new Abp01_Route_Track_DocumentParser_GeoJson();

		foreach ($testFiles as $fileName) {
			$currentException = null;
			$fileContents = $this->_readTestDataFileContents($fileName); 

			try {
				$parser->parse($fileContents);
			} catch (Exception $exc) {
				$currentException = $exc;
			}

			$this->assertNotNull($currentException);
			$this->assertInstanceOf(Abp01_Route_Track_DocumentParser_Exception::class, $currentException);
		}
	}

	/**
	 * @expectedException InvalidArgumentException
	 */
	public function test_tryParse_nullData() {
		$this->expectException(InvalidArgumentException::class);
		$parser = new Abp01_Route_Track_DocumentParser_GeoJson();
		$parser->parse(null);
	}

	/**
	 * @expectedException InvalidArgumentException
	 */
	public function test_tryParse_emptyData() {
		$this->expectException(InvalidArgumentException::class);
		$parser = new Abp01_Route_Track_DocumentParser_GeoJson();
		$parser->parse('');
	}

	private function _assertMetadataCorrect(Abp01_Route_Track_Document $actualDocument, $expectMeta) {
		$this->assertNotNull($actualDocument->getMetadata());
		$this->assertTrue($this->_isMetadataNameCorrect($actualDocument, $expectMeta));
		$this->assertTrue($this->_isMetadataDescriptionCorrect($actualDocument, $expectMeta));
		$this->assertTrue($this->_areMetadataKeywordsCorrect($actualDocument, $expectMeta));
	}

	private function _assertWaypointsCorrect(Abp01_Route_Track_Document $actualDocument, $expectWaypoints) {
		try {
			$this->assertTrue($this->_areDocumentWayPointsCorrect($actualDocument, $expectWaypoints));
		} catch (Exception $exc) {
			$this->fail($exc->getMessage());
		}
	}

	private function _assertTrackPartsCorrect(Abp01_Route_Track_Document $actualDocument, $expectTrackPartsSpec) {
		try {
			$this->assertTrue($this->_areAllTrackPartsCorrect($actualDocument, $expectTrackPartsSpec));
		} catch (Exception $exc) {
			$this->fail($exc->getMessage());
		}
	}

	private function _getValidTestFilesSpec() {
		return array_merge(array(
			'geojson/test1-bikemap-utf8-bom.geojson' => array(
				'expect' => array(
					'document' => true,
					'metadata' => array(
						'name' => 'PDM #4 - Meridionalii de Vest',
						'desc' => 'PDM #4 - Meridionalii de Vest - Description',
						'keywords' => 'kw1,kw2,k23'
					),
					'trackParts' => array(
						array(
							'name' => 'PDM #4 - Meridionalii de Vest Track Part',
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
			'geojson/test2-bikemap-utf8-no-bom.geojson' => array(
				'expect' => 'geojson/test1-bikemap-utf8-bom.geojson'
			),
			'geojson/test5-simple-shapes-featurecollection-nometa-utf8-bom.geojson' => array(
				'expect' => array(
					'document' => true,
					'metadata' => array(
						'name' => null,
						'desc' => null,
						'keywords' => null
					),
					'waypoints' => array(
						array(
							'lat' => 45.81803291052889,
							'lon' => 23.88153076171875,
							'ele' => 0,
							'delta' => 0.00
						),
						array(
							'lat' => 45.80606786701775,
							'lon' => 23.91345977783203,
							'ele' => 0,
							'delta' => 0.00
						),
						array(
							'lat' => 45.78787607781522,
							'lon' => 23.99585723876953,
							'ele' => 0,
							'delta' => 0.00
						),
						array(
							'lat' => 45.77877795608451,
							'lon' => 23.955345153808594,
							'ele' => 0,
							'delta' => 0.00
						)
					),
					'trackParts' => array(
						array(
							'name' => null,
							'trackLines' => array(
								array(
									'trackPointsCount' => 6,
									'sampleTrackPoints' => array(
										[
											'lon' => 23.861961364746094,
											'lat' => 45.83166992465285,
											'ele' => 0,
											'delta' => 0
										],
										[
											'lon' => 23.874664306640625,
											'lat' => 45.816357959181374,
											'ele' => 0,
											'delta' => 0
										],
										[
											'lon' => 23.912086486816406,
											'lat' => 45.80534988266492,
											'ele' => 0,
											'delta' => 0
										],
										[
											'lon' => 23.948822021484375,
											'lat' => 45.776622920308796,
											'ele' => 0,
											'delta' => 0
										],
										[
											'lon' => 23.99688720703125,
											'lat' => 45.7859608071541,
											'ele' => 0,
											'delta' => 0
										],
										[
											'lon' => 24.030189514160156,
											'lat' => 45.798887607064486,
											'ele' => 0,
											'delta' => 0
										]
									)
								)
							)
						),
						array(
							'name' => null,
							'trackLines' => array(
								array(
									'trackPointsCount' => 7,
									'sampleTrackPoints' => array(
										[
											'lon' => 23.857498168945312,
											'lat' => 45.88690151781579,
											'ele' => 0,
											'delta' => 0
										],
										[
											'lon' => 23.88530731201172,
											'lat' => 45.89598197962566,
											'ele' => 0,
											'delta' => 0
										],
										[
											'lon' => 24.004440307617188,
											'lat' => 45.88212173108007,
											'ele' => 0,
											'delta' => 0
										],
										[
											'lon' => 24.036026000976562,
											'lat' => 45.88690151781579,
											'ele' => 0,
											'delta' => 0
										]
									)
								),
							)
						),
						array(
							'name' => null,
							'trackLines' => array(
								array(
									'trackPointsCount' => 7,
									'sampleTrackPoints' => array(
										[
											'lon' => 23.895263671875,
											'lat' => 45.869931413475676,
											'ele' => 0,
											'delta' => 0
										],
										[
											'lon' => 23.890113830566406,
											'lat' => 45.86658458397811,
											'ele' => 0,
											'delta' => 0
										],
										[
											'lon' => 23.908653259277344,
											'lat' => 45.82664615020935,
											'ele' => 0,
											'delta' => 0
										],
										[
											'lon' => 24.003753662109375,
											'lat' => 45.80247785271482,
											'ele' => 0,
											'delta' => 0
										],
										[
											'lon' => 24.04186248779297,
											'lat' => 45.84099858850666,
											'ele' => 0,
											'delta' => 0
										],
										[
											'lon' => 23.9776611328125,
											'lat' => 45.87710251239898,
											'ele' => 0,
											'delta' => 0
										],
										[
											'lon' => 23.895263671875,
											'lat' => 45.869931413475676,
											'ele' => 0,
											'delta' => 0
										]
									)
								)
							)
						)
					)
				)
			),
			'geojson/test6-simple-shapes-featurecollection-nometa-utf8-wo-bom.geojson' => array(
				'expect' => 'geojson/test5-simple-shapes-featurecollection-nometa-utf8-bom.geojson'
			),
			'geojson/test3-empty-object-featurecollection-utf8-bom.geojson' => array(
				'expect' => array(
					'document' => true,
					'metadata' => array(
						'name' => null,
						'desc' => null,
						'keywords' => null
					),
					'waypoints' => array(),
					'trackParts' => array()
				)
			),
			'geojson/test4-empty-object-featurecollection-utf8-wo-bom.geojson' => array(
				'expect' => 'geojson/test3-empty-object-featurecollection-utf8-bom.geojson'
			),
			'geojson/test7-only-points-utf8-bom.geojson' => array(
				'expect' => array(
					'document' => true,
					'metadata' => array(
						'name' => null,
						'desc' => null,
						'keywords' => null
					),
					'waypoints' => array(
						array(
							'lat' => 45.81803291052889,
							'lon' => 23.88153076171875,
							'ele' => 0,
							'delta' => 0.00
						),
						array(
							'lat' => 45.80606786701775,
							'lon' => 23.91345977783203,
							'ele' => 0,
							'delta' => 0.00
						),
						array(
							'lat' => 45.78787607781522,
							'lon' => 23.99585723876953,
							'ele' => 0,
							'delta' => 0.00
						),
						array(
							'lat' => 45.77877795608451,
							'lon' => 23.955345153808594,
							'ele' => 0,
							'delta' => 0.00
						)
					),
					'trackParts' => array()
				)
			),
			'geojson/test8-only-points-utf8-wo-bom.geojson' => array(
				'expect' => 'geojson/test7-only-points-utf8-bom.geojson'
			)
		), self::$_randomGeoJsonFilesTestInfo);
	}

	private function _getInvalidTestFilesSpec() {
		return array(
			'geojson/test-inv1-jibberish.geojson', 
			'geojson/test-inv2-jibberish-malformed.geojson'
		);
	}

	protected static function _getRootTestsDir() {
		return __DIR__;
	}
}