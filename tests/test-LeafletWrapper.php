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

use PHPUnit\Framework\TestCase;

/**
 * Runs the actual wrapper in separate PHP processes, without WordPress or a web server.
 * Temporary fixtures stay inside this checkout; junctions/symlinks are removed without recursion.
 */
class LeafletWrapperTests extends TestCase {
	private const MARKER = 'WPTS dynamic wrapper fixture';

	private static string $_pluginRoot;
	private static string $_fixtureRoot;
	private static string $_outsideRoot;
	private static array $_directories = array();
	private static array $_files = array();
	private static array $_links = array();
	private static bool $_isWindows = false;

	public static function setUpBeforeClass(): void {
		self::$_pluginRoot = dirname(__DIR__);
		self::$_isWindows = PHP_OS_FAMILY === 'Windows';

		$suffix = bin2hex(random_bytes(8));
		
		self::$_fixtureRoot = self::$_pluginRoot 
			. '/media/js/3rdParty/leaflet-plugins/wpts-test-' 
			. $suffix;

		self::$_outsideRoot = self::$_pluginRoot 
			. '/media/js/3rdParty/leaflet-plugins-wpts-test-' 
			. $suffix;

		try {
			$createDirs = array(
				self::$_fixtureRoot, 
				self::$_fixtureRoot . '/target', 
				self::$_outsideRoot
			);

			foreach ($createDirs as $directory) {
				if (!mkdir($directory)) {
					throw new RuntimeException('Could not create fixture directory: ' . $directory);
				}
				self::$_directories[] = $directory;
			}

			$createFiles = array(
				self::$_fixtureRoot . '/target/marker.js', 
				self::$_fixtureRoot . '/target/marker.css', 
				self::$_outsideRoot . '/marker.js'
			);

			foreach ($createFiles as $file) {
				self::$_files[] = $file;
				if (file_put_contents($file, '/* ' . self::MARKER . ' */') === false) {
					throw new RuntimeException('Could not write fixture: ' . $file);
				}
			}

			$linkTargets = array(
				'outside-inner' => self::$_outsideRoot,
				'outside-outer' => self::$_fixtureRoot . '/outside-inner',
				'inside-inner' => self::$_fixtureRoot . '/target',
				'inside-outer' => self::$_fixtureRoot . '/inside-inner'
			);

			if (self::$_isWindows) {
				$target = self::$_fixtureRoot . '/target';
				for ($step = 0; $step < 33; $step++) {
					$linkTargets['depth-' . $step] = $target;
					$target = self::$_fixtureRoot . '/depth-' . $step;
				}
			}
			self::_createLinks($linkTargets);
		} catch (Throwable $error) {
			self::_removeFixtures();
			throw $error;
		}
	}

	private static function _createLinks(array $targets): void {
		$commands = array('$ErrorActionPreference = \'Stop\'');
		foreach ($targets as $name => $target) {
			$link = self::$_fixtureRoot . '/' . $name;
			self::$_links[] = $link;

			if (self::$_isWindows) {
				$commands[] = 'New-Item -ItemType Junction ' 
						. '-Path ' . self::_quotePowerShell($link) 
						. ' -Target ' . self::_quotePowerShell($target) 
					. ' | Out-Null';

			} else if (!symlink($target, $link)) {
				throw new RuntimeException('Could not create fixture link: ' . $link);
			}
		}

		if (self::$_isWindows) {
			self::_runProcess(array(
				'powershell.exe', 
				'-NoProfile', 
				'-NonInteractive', 
				'-Command', 
				implode("\n", $commands)
			), self::$_pluginRoot);
		}
	}

	private static function _quotePowerShell(string $value): string {
		return "'" . str_replace("'", "''", $value) . "'";
	}

	public static function tearDownAfterClass(): void {
		self::_removeFixtures();
	}

	private static function _removeFixtures(): void {
		foreach (array_reverse(self::$_links) as $link) {
			// Do not follow the target: broken/deep Windows junctions can fail is_dir()/is_link().
			$entries = scandir(dirname($link));
			if ($entries !== false && in_array(basename($link), $entries, true)) {
				if (PHP_OS_FAMILY === 'Windows') {
					rmdir($link);
				} else {
					unlink($link);
				}
			}
		}

		foreach (self::$_files as $file) {
			if (is_file($file)) {
				unlink($file);
			}
		}

		foreach (array_reverse(self::$_directories) as $directory) {
			rmdir($directory);
		}

		self::$_links = self::$_files = self::$_directories = array();
	}

	private static function _runProcess(array $command, string $cwd): string {
		$process = proc_open($command,
			array(
				0 => array('pipe', 'r'), 
				1 => array('pipe', 'w'), 
				2 => array('pipe', 'w')
			),
			$pipes,
			$cwd,
			null,
			array(
				'bypass_shell' => true, 
				'create_no_window' => true
			));
		if (!is_resource($process)) {
			throw new RuntimeException('Could not start fixture process.');
		}

		fclose($pipes[0]);
		
		$output = stream_get_contents($pipes[1]);
		$error = stream_get_contents($pipes[2]);
		
		fclose($pipes[1]);
		fclose($pipes[2]);
		
		if (proc_close($process) !== 0) {
			throw new RuntimeException('Fixture process failed: ' . $error . $output);
		}
		
		return $output;
	}

	private function _request(string $path, ?string $cwd = null): string {
		$code = '$_GET = ' . var_export(array('load' => $path), true) . ';' 
			. '$_SERVER["REQUEST_URI"] = "/abp01-plugin-leaflet-plugins-wrapper.php";' 
			. '$_SERVER["SERVER_PROTOCOL"] = "HTTP/1.1";' 
			. 'require ' . var_export(self::$_pluginRoot . '/abp01-plugin-leaflet-plugins-wrapper.php', true) 
			. ';';
		
		return self::_runProcess(array(
				PHP_BINARY, 
				'-d', 
				'xdebug.mode=off', 
				'-d', 
				'xdebug.log=', 
				'-r', 
				$code),
			$cwd ?? self::$_pluginRoot);
	}

	private function _fixtureUrl(string $suffix): string {
		return substr(self::$_fixtureRoot, strlen(self::$_pluginRoot) + 1) . '/' . $suffix;
	}

	public function test_canServeDynamicScript_fromAnotherWorkingDirectory(): void {
		$fixtureUrl = $this->_fixtureUrl('target/marker.js');

		$this->assertStringContainsString(
			self::MARKER,
			$this->_request($fixtureUrl, sys_get_temp_dir())
		);
	}

	public function test_canServeRootRelativeScriptPath(): void {
		$fixtureUrl = $this->_fixtureUrl('target/marker.js');
		$this->assertStringContainsString(
			self::MARKER,
			$this->_request('/wp-content/plugins/' . basename(self::$_pluginRoot) . '/' . $fixtureUrl)
		);
	}

	public function test_canServeChainedLinks_withInternalTarget(): void {
		$fixtureUrl = $this->_fixtureUrl('inside-outer/marker.js');
		$this->assertStringContainsString(
			self::MARKER, 
			$this->_request($fixtureUrl)
		);
	}

	public function test_rejectsChainedLinks_withExternalTarget(): void {
		$fixtureUrl = $this->_fixtureUrl('outside-outer/marker.js');
		$this->assertSame(
			'', 
			$this->_request($fixtureUrl)
		);
	}

	public function test_rejectsSingleLink_withExternalTarget(): void {
		$fixtureUrl = $this->_fixtureUrl('outside-inner/marker.js');
		$this->assertSame(
			'', 
			$this->_request($fixtureUrl)
		);
	}

	public function test_rejectsSiblingDirectory_withSimilarPrefix(): void {
		$path = substr(self::$_outsideRoot, strlen(self::$_pluginRoot) + 1) . '/marker.js';
		$this->assertSame('', $this->_request($path));
	}

	public function test_rejectsOriginalTraversalPayload(): void {
		$this->assertSame(
			'', 
			$this->_request('media/js/3rdParty/leaflet-plugins/../../abp01-map.js')
		);
	}

	public function test_rejectsMissingFile(): void {
		$fixtureUrl = $this->_fixtureUrl('target/missing.js');
		$this->assertSame(
			'', 
			$this->_request($fixtureUrl)
		);
	}

	public function test_rejectsDirectory(): void {
		$fixtureUrl = $this->_fixtureUrl('target/');
		$this->assertSame(
			'', 
			$this->_request($fixtureUrl)
		);
	}

	public function test_rejectsNonJavaScriptFile(): void {
		$fixtureUrl = $this->_fixtureUrl('target/marker.css');
		$this->assertSame(
			'', 
			$this->_request($fixtureUrl)
		);
	}

	public function test_rejectsWindowsChain_exceedingResolutionLimit(): void {
		if (!self::$_isWindows) {
			$this->markTestSkipped('Windows expands directory links one level at a time.');
		}

		$this->assertSame(
			'', 
			$this->_request($this->_fixtureUrl('depth-32/marker.js'))
		);
	}
}
