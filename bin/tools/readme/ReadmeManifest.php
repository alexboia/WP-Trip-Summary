<?php
declare(strict_types=1);

/**
 * The central readme manifest (readme/manifest.json).
 * Paths are resolved against the plugin root when the manifest is loaded.
 */
final readonly class ReadmeManifest {
	/**
	 * @param array<string, string> $variables Variables available to the Makefile and to section fragments
	 */
	public function __construct(
		public string $rootDirectory,
		public string $makefilePath,
		public ReadmeManifestPluginHeader $pluginHeader,
		public ReadmeManifestPaths $paths,
		public array $variables
	) {
		return;
	}

	public static function fromFile(string $manifestPath, string $rootDirectory): self {
		$json = ReadmeJsonObject::fromFile($manifestPath, 
			'manifest');

		$makefilePath = $json->requirePath('makefile', 
			$rootDirectory);

		$pluginHeader = ReadmeManifestPluginHeader::fromJson(
			$json->requireObject('pluginHeader'), 
			$rootDirectory
		);

		$paths = ReadmeManifestPaths::fromJson($json->requireObject('paths'), 
			$rootDirectory);

		$manifest = new self(
			$rootDirectory,
			$makefilePath,
			$pluginHeader,
			$paths,
			$json->requireVariableMap('variables')
		);

		$json->assertNoUnknownKeys();
		return $manifest;
	}
}

/**
 * Where to read plugin metadata from, and which header fields become variables.
 */
final readonly class ReadmeManifestPluginHeader {
	/**
	 * @param array<string, string> $variables Variable names mapped to plugin header field names
	 */
	public function __construct(
		public string $file,
		public array $variables
	) {
	}

	public static function fromJson(ReadmeJsonObject $json, string $rootDirectory): self {
		$pluginHeader = new self(
			$json->requirePath('file', $rootDirectory),
			$json->requireVariableMap('variables')
		);

		$json->assertNoUnknownKeys();
		return $pluginHeader;
	}
}

final readonly class ReadmeManifestPaths {
	public function __construct(
		public string $sections,
		public string $screenshots,
		public string $changelog
	) {
	}

	public static function fromJson(ReadmeJsonObject $json, string $rootDirectory): self {
		$paths = new self(
			$json->requirePath('sections', 
				$rootDirectory),
			$json->requirePath('screenshots', 
				$rootDirectory),
			$json->requirePath('changelog', 
				$rootDirectory)
		);

		$json->assertNoUnknownKeys();
		return $paths;
	}
}
