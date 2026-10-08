$ErrorActionPreference = "Stop"

$venvPath = Join-Path $PSScriptRoot ".venv"
$requirementsPath = Join-Path $PSScriptRoot "requirements.txt"
$pythonPath = Join-Path $venvPath "Scripts\python.exe"

if (-not (Test-Path -LiteralPath $pythonPath)) {
    py -m venv $venvPath

    if ($LASTEXITCODE -ne 0) {
        throw "Cannot setup virtual environment."
    }
}

& $pythonPath -m pip install -r $requirementsPath

if ($LASTEXITCODE -ne 0) {
    throw "Cannot install dependencies"
}

Write-Host "Init completed."
Write-Host "Activate environment: .\.venv\Scripts\Activate.ps1"