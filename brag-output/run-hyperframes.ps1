param(
	[Parameter(ValueFromRemainingArguments = $true)]
	[string[]]$HyperframesArguments
)

$ErrorActionPreference = 'Stop'
$taskBin = Join-Path $PSScriptRoot 'tooling\node_modules'
$env:PATH = "D:\Tools\ffmpeg;" + (Join-Path $taskBin '@ffprobe-installer\win32-x64') + ';' + $env:PATH
$env:HYPERFRAMES_SKIP_SKILLS = '1'
$env:HYPERFRAMES_TELEMETRY_DISABLED = '1'
$env:HYPERFRAMES_RUN_ID = 'wpts-brag-20261008'
Push-Location (Join-Path $PSScriptRoot 'composition')
try {
	& npx.cmd --yes hyperframes@0.8.141 @HyperframesArguments
	exit $LASTEXITCODE
} finally {
	Pop-Location
}
