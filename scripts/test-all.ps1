$ErrorActionPreference='Stop'
$phpExe=(Get-Command php).Source
$extensionDirectory=Join-Path (Split-Path $phpExe) 'ext'
function Run-Php([string]$script,[string[]]$arguments=@()) {
    & $phpExe -n -d "extension_dir=$extensionDirectory" -d extension=pdo_mysql -d extension=mbstring $script @arguments
    if($LASTEXITCODE -ne 0){throw "Failed: $script"}
}
function Run-Node([string]$script) { node $script; if($LASTEXITCODE -ne 0){throw "Failed: $script"} }
Run-Php 'scripts/test-builder.php'
$previousDatabase=$env:DB_NAME
try {
    $env:DB_NAME=(Get-Content -Raw '.local/builder-unit-fixture.json' | ConvertFrom-Json).database
    Run-Php 'scripts/skill_sync_test.php'
} finally { $env:DB_NAME=$previousDatabase }
Run-Node 'scripts/test-builder-http.mjs'
Run-Node 'scripts/test-saas.mjs'
Run-Php 'scripts/prepare-design-parity.php' @('--editor')
Run-Node 'scripts/test-editor-ui.mjs'
Run-Node 'scripts/test-editor-workspace.mjs'
Run-Node 'scripts/test-panel-drag.mjs'
Run-Node 'scripts/test-block-move.mjs'
Run-Node 'scripts/test-structure-ui.mjs'
Run-Node 'scripts/test-collections-ui.mjs'
Run-Node 'scripts/test-settings-ui.mjs'
Run-Node 'scripts/test-section-context.mjs'
Run-Php 'scripts/prepare-design-parity.php'
Run-Node 'scripts/test-design-parity.mjs'
Write-Output 'All suites passed. Evidence is in test-results/.'
