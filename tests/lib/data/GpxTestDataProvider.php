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

class GpxTestDataProvider {
	public static function getValidTestFilesSpec($randomGpxFilesTestInfo) {
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
		), $randomGpxFilesTestInfo);
	}

	public static function getInvalidTestFilesSpec() {
		return array(
			'test-inv1-jibberish.gpx', 
			'test-inv2-jibberish-malformed.gpx'
		);
	}

	public static function getRandomFileGenerationSpec() {
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
}