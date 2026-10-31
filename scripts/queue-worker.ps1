# queue-worker.ps1
# Starts (or stops) a background Laravel queue worker. The portal scan
# ("Imbas Borang Bantuan MAIJ") processes OCR asynchronously via the queue,
# so a worker MUST be running or uploads stay stuck at "processing".
#
# Usage:
#   powershell -File scripts/queue-worker.ps1          # start (or no-op if already running)
#   powershell -File scripts/queue-worker.ps1 -Stop    # stop the background worker
#   powershell -File scripts/queue-worker.ps1 -Foreground   # run in foreground (Ctrl+C to stop)

param(
    [switch]$Stop,
    [switch]$Foreground,
    [int]$Tries = 2,
    [int]$Timeout = 300
)

$ErrorActionPreference = 'Stop'

$scriptDir = Split-Path -Parent $MyInvocation.MyCommand.Path
$projectRoot = Split-Path -Parent $scriptDir
$php = 'php'
if (Test-Path 'C:\php-8.5.4\php.exe') { $php = 'C:\php-8.5.4\php.exe' }

$logDir = Join-Path $projectRoot 'storage\logs'
$logFile = Join-Path $logDir 'queue-worker.log'
$pidFile = Join-Path $logDir 'queue-worker.pid'

if ($Stop) {
    if (Test-Path $pidFile) {
        $pid = Get-Content $pidFile
        if ($pid -match '^\d+$') {
            Stop-Process -Id $pid -Force -ErrorAction SilentlyContinue
            Write-Host "Stopped queue worker (PID $pid)."
        }
        Remove-Item $pidFile -Force -ErrorAction SilentlyContinue
    } else {
        Write-Host "No queue worker PID file found. Nothing to stop."
    }
    exit 0
}

if (Test-Path $pidFile) {
    $existing = Get-Content $pidFile
    if ($existing -match '^\d+$' -and (Get-Process -Id $existing -ErrorAction SilentlyContinue)) {
        Write-Host "Queue worker already running (PID $existing)."
        exit 0
    }
    Remove-Item $pidFile -Force -ErrorAction SilentlyContinue
}

$args = @(
    'artisan', 'queue:work',
    '--queue=ocr,default',
    "--tries=$Tries",
    "--timeout=$Timeout",
    '--sleep=1',
    '--verbose'
)

if ($Foreground) {
    Push-Location $projectRoot
    & $php @args
    Pop-Location
    exit 0
}

Write-Host "Starting queue worker (tries=$Tries timeout=${Timeout}s)..."
Write-Host "Logs: $logFile"

$process = Start-Process -FilePath $php -ArgumentList $args -WorkingDirectory $projectRoot -RedirectStandardOutput $logFile -RedirectStandardError "$logFile.err" -WindowStyle Hidden -PassThru
Set-Content -Path $pidFile -Value $process.Id

Write-Host "Queue worker started (PID $($process.Id))."
Write-Host ""
Write-Host "To stop it later:  powershell -File scripts/queue-worker.ps1 -Stop"