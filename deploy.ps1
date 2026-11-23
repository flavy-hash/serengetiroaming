<#
    Deploys this project to the live site (serengetiroaming.mileletechnologies.com).

    Paste into PowerShell:
        powershell -ExecutionPolicy Bypass -File C:\Users\hp\serengetiroaming\deploy.ps1

    Options:
        -All      send every file, not just the ones changed since the last deploy
        -DryRun   build and package, list the files, but don't upload anything

    It builds the CSS/JS, packages the changed files (never .env, vendor, node_modules,
    storage or public/hot), uploads them over SSH, unpacks them and runs
    scripts/after-deploy.sh on the server (migrations + cache refresh).
    You'll be asked for your SSH password twice: once to upload, once to apply.
#>
param(
    [switch]$All,
    [switch]$DryRun
)

$ErrorActionPreference = 'Stop'

$Server    = 'milekhhp@business153.web-hosting.com'
$Port      = 21098
$RemoteDir = '~/serengetiroaming.mileletechnologies.com'

$Root     = $PSScriptRoot
$Stamp    = Join-Path $Root '.deploy-stamp'
$Archive  = Join-Path $env:TEMP 'serengeti-deploy.tar.gz'
$ListFile = Join-Path $env:TEMP 'serengeti-deploy-files.txt'
$Tar      = Join-Path $env:SystemRoot 'System32\tar.exe'

# Runs an external tool (npm, tar, scp, ssh). They print progress on stderr, which
# PowerShell would otherwise treat as an error, so success is judged by the exit code.
function Invoke-Tool([string]$failMessage, [scriptblock]$command) {
    $ErrorActionPreference = 'Continue'
    & $command
    if ($LASTEXITCODE -ne 0) { throw $failMessage }
}

Set-Location $Root

# --- 1. Build CSS / JavaScript ------------------------------------------------
Write-Host '1/4  Building CSS and JavaScript...' -ForegroundColor Cyan
Invoke-Tool 'npm run build failed - fix the error above and run the deploy again.' { npm run build }

# --- 2. Pick the files to send -------------------------------------------------
$startedAt = Get-Date

$paths = @(
    'app', 'bootstrap\app.php', 'bootstrap\providers.php', 'config', 'database',
    'public', 'resources', 'routes', 'scripts',
    'artisan', 'composer.json', 'composer.lock', '.htaccess'
)
# Never sent: the dev-server pointer, the uploads folder link, caches, local databases, logs.
$skip = '^(public\\hot$|public\\storage\\|bootstrap\\cache\\)|\.sqlite$|\.log$'

$since = $null
if (-not $All -and (Test-Path $Stamp)) {
    $since = [datetime]::Parse((Get-Content $Stamp -Raw).Trim(), $null, [Globalization.DateTimeStyles]::RoundtripKind)
}

$files = foreach ($p in $paths) {
    $full = Join-Path $Root $p
    if (Test-Path $full -PathType Leaf) { Get-Item $full -Force }
    elseif (Test-Path $full) { Get-ChildItem $full -Recurse -File -Force }
}

$selected = @($files | ForEach-Object {
    $rel = $_.FullName.Substring($Root.Length + 1)
    if ($rel -notmatch $skip -and ($null -eq $since -or $_.LastWriteTime -gt $since)) { $rel }
} | Sort-Object -Unique)

if ($selected.Count -eq 0) {
    Write-Host 'Nothing has changed since the last deploy.' -ForegroundColor Green
    exit 0
}

if ($since) { $what = "changed since $($since.ToString('d MMM yyyy HH:mm'))" } else { $what = 'full upload (first deploy, or -All)' }
Write-Host "2/4  Packaging $($selected.Count) files - $what" -ForegroundColor Cyan
$selected | Select-Object -First 40 | ForEach-Object { Write-Host "       $_" }
if ($selected.Count -gt 40) { Write-Host "       ... and $($selected.Count - 40) more" }

$utf8 = New-Object System.Text.UTF8Encoding $false
[IO.File]::WriteAllLines($ListFile, [string[]]($selected | ForEach-Object { $_ -replace '\\', '/' }), $utf8)
Invoke-Tool 'Packaging failed.' { & $Tar --format ustar -czf $Archive -T $ListFile }
Write-Host ("       Package: {0:N1} MB" -f ((Get-Item $Archive).Length / 1MB))

if ($DryRun) {
    Write-Host "Dry run - nothing uploaded. Package left at $Archive" -ForegroundColor Yellow
    exit 0
}

# PHP packages only need installing on the server when composer.json/lock changed.
$runComposer = 0
if ($selected -contains 'composer.json' -or $selected -contains 'composer.lock') { $runComposer = 1 }

# --- 3. Upload -----------------------------------------------------------------
Write-Host '3/4  Uploading (enter your SSH password)...' -ForegroundColor Cyan
Invoke-Tool 'Upload failed.' { scp -P $Port $Archive "${Server}:~/serengeti-deploy.tar.gz" }

# --- 4. Unpack and apply on the server -----------------------------------------
Write-Host '4/4  Applying on the server (enter your SSH password again)...' -ForegroundColor Cyan
$remote = "cd $RemoteDir && tar -xzf ~/serengeti-deploy.tar.gz && rm -f ~/serengeti-deploy.tar.gz && tr -d '\r' < scripts/after-deploy.sh | RUN_COMPOSER=$runComposer bash -s"
Invoke-Tool 'The server steps failed - see the messages above. The files were uploaded; fix the issue and run the deploy again.' { ssh -p $Port $Server $remote }

Set-Content -Path $Stamp -Value $startedAt.ToString('o') -Encoding ASCII
Remove-Item $Archive, $ListFile -ErrorAction SilentlyContinue
Write-Host 'Done - https://serengetiroaming.mileletechnologies.com is up to date.' -ForegroundColor Green
