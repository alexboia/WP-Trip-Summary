param([Parameter(ValueFromRemainingArguments = $true)][string[]]$HyperframesArguments)
$ErrorActionPreference = 'Stop'
$campaignFfmpeg = Join-Path $PSScriptRoot 'tooling/node_modules/ffmpeg-static'
$campaignFfprobe = Join-Path (Split-Path $PSScriptRoot -Parent) 'brag-output/tooling/node_modules/@ffprobe-installer/win32-x64'
$env:PATH = "$campaignFfmpeg;$campaignFfprobe;" + $env:PATH
$env:HYPERFRAMES_SKIP_SKILLS = '1'
$env:HYPERFRAMES_TELEMETRY_DISABLED = '1'
Push-Location (Join-Path $PSScriptRoot 'composition')
try {
    & npx.cmd --yes hyperframes@0.8.183 @HyperframesArguments
    exit $LASTEXITCODE
} finally { Pop-Location }
