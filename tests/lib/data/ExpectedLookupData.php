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

class ExpectedLookupData {
	private static $_checkLookupData = array(
		'difficultyLevel' => array(
			array(
				'default' => 'Easy',
				'translations' => array(
					'ro_RO' => 'Ușor',
					'fr_FR' => 'Facile'
				)
			),
			array(
				'default' => 'Medium',
				'translations' => array(
					'ro_RO' => 'Mediu',
					'fr_FR' => 'Moyen'
				)
			),
			array(
				'default' => 'Hard',
				'translations' => array(
					'ro_RO' => 'Dificil',
					'fr_FR' => 'Difficile'
				)
			),
			array(
				'default' => 'Medieval torture',
				'translations' => array(
					'ro_RO' => 'Tortură medievală',
					'fr_FR' => 'Torture médiévale'
				)
			)
		),

		'pathSurfaceType' => array(
			array(
				'default' => 'Asphalt',
				'translations' => array(
					'ro_RO' => 'Asfalt',
					'fr_FR' => 'Asphalte'
				)
			),
			array(
				'default' => 'Concrete',
				'translations' => array(
					'ro_RO' => 'Plăci de beton',
					'fr_FR' => 'Dalles de béton'
				)
			),
			array(
				'default' => 'Dust or dirt',
				'translations' => array(
					'ro_RO' => 'Pământ',
					'fr_FR' => 'Terre'
				)
			),
			array(
				'default' => 'Grass',
				'translations' => array(
					'ro_RO' => 'Iarbă',
					'fr_FR' => 'Végétation'
				)
			),
			array(
				'default' => 'Stone pavement/Gravel',
				'translations' => array(
					'ro_RO' => 'Macadam',
					'fr_FR' => 'Macadam/gravier'
				)
			),
			array(
				'default' => 'Loose rocks',
				'translations' => array(
					'ro_RO' => 'Piatră neașezată',
					'fr_FR' => 'Pierre déstabilisé'
				)
			)
		),

		'bikeType' => array(
			array(
				'default' => 'MTB',
				'translations' => array(
					'ro_RO' => 'MTB',
					'fr_FR' => 'VTT'
				)
			),
			array(
				'default' => 'Road bike',
				'translations' => array(
					'ro_RO' => 'Cursieră',
					'fr_FR' => 'Vélo de route'
				)
			),
			array(
				'default' => 'Trekking',
				'translations' => array(
					'ro_RO' => 'Trekking',
					'fr_FR' => 'Vélo de trekking'
				)
			),
			array(
				'default' => 'City bike',
				'translations' => array(
					'ro_RO' => 'Bicicletă de oraș',
					'fr_FR' => 'Vélo de ville'
				)
			)
		),

		'railroadLineType' => array(
			array(
				'default' => 'Simple line',
				'translations' => array(
					'ro_RO' => 'Linie simplă',
					'fr_FR' => 'Ligne de chemin de fer simple'
				)
			),
			array(
				'default' => 'Double line',
				'translations' => array(
					'ro_RO' => 'Linie dublă',
					'fr_FR' => 'Ligne de chemin de fer double'
				)
			)
		),

		'railroadOperator' => array(),

		'railroadLineStatus' => array(
			array(
				'default' => 'In production',
				'translations' => array(
					'ro_RO' => 'În exploatare',
					'fr_FR' => 'En fonctionnement'
				)
			),
			array(
				'default' => 'Closed',
				'translations' => array(
					'ro_RO' => 'Închisă',
					'fr_FR' => 'Hors service'
				)
			),
			array(
				'default' => 'Disbanded',
				'translations' => array(
					'ro_RO' => 'Desființată',
					'fr_FR' => 'Chemin de fer démantelé'
				)
			),
			array(
				'default' => 'In rehabilitation',
				'translations' => array(
					'ro_RO' => 'În reabilitare',
					'fr_FR' => 'En réhabilitation'
				)
			)
		),

		'recommendSeasons' => array(
			array(
				'default' => 'Spring',
				'translations' => array(
					'ro_RO' => 'Primăvara',
					'fr_FR' => 'Printemps'
				)
			),
			array(
				'default' => 'Summer',
				'translations' => array(
					'ro_RO' => 'Vara',
					'fr_FR' => 'Été'
				)
			),
			array(
				'default' => 'Autumn',
				'translations' => array(
					'ro_RO' => 'Toamna',
					'fr_FR' => 'L\'automne'
				)
			),
			array(
				'default' => 'Winter',
				'translations' => array(
					'ro_RO' => 'Iarna',
					'fr_FR' => 'L\'hiver'
				)
			)
		),

		'railroadElectrificationStatus' => array(
			array(
				'default' => 'Electrified',
				'translations' => array(
					'ro_RO' => 'Electrificată',
					'fr_FR' => 'Électrifié'
				)
			),
			array(
				'default' => 'Not electrified',
				'translations' => array(
					'ro_RO' => 'Neelectrificată',
					'fr_FR' => 'Non électrifié'
				)
			),
			array(
				'default' => 'Partially electrified',
				'translations' => array(
					'ro_RO' => 'Partial electrificată',
					'fr_FR' => 'Partiellement électrifié'
				)
			)
		)
	);

	public static function getLookupDataToCheck() {
		return self::$_checkLookupData;
	}
}