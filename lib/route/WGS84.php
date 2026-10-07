<?php
/**
 * Copyright (c) 2014-2026 Alexandru Boia and Contributors
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

declare(strict_types=1);

namespace WpTripSummary\Route {

    use Abp01_Route_Track;
    use Abp01_Route_Track_Document;
    use Abp01_Route_Track_Point;

	class WGS84 {
		public function isValidWgs84Lat(float $lat): bool {
			return $lat >= -90 && $lat <= 90;
		}

		public function isValidWgs84Lng(float $lng): bool {
			return $lng >= -180 && $lng <= 180;
		}

		public function isValidWgs84Document(Abp01_Route_Track_Document $document): bool {
			if ($document->parts !== null) {
				foreach ($document->parts as $part) {
					if ($part->lines === null) {
						continue;
					}

					foreach ($part->lines as $line) {
						if ($line->trackPoints === null) {
							continue;
						}

						foreach ($line->trackPoints as $point) {
							if (!$this->isvalidWgs84Point($point)) {
								return false;
							}
						}
					}
				}
			}

			if ($document->waypoints !== null) {
				foreach ($document->waypoints as $wpt) {
					if (!$this->isvalidWgs84Point($wpt)) {
						return false;
					}
				}
			}

			return true;
		}

		public function isvalidWgs84Point(Abp01_Route_Track_Point $point): bool {
			return $point->coordinate != null 
				&& $this->isValidWgs84Lat($point->coordinate->lat)
				&& $this->isValidWgs84Lng($point->coordinate->lng);
		}
	}
}