# serve-doctor.ps1
# Diagnoses duplicate `php artisan serve` instances and optionally kills all of
# them so you can restart cleanly with a single instance.
#
# Usage:
#   powershell -File scripts/serve-doctor.ps1          # inspect only
#   powershell -File scripts/serve-doctor.ps1 -Fix     # inspect + kill all serve instances
#
# Recommended single-instance command:
#   php artisan serve --host=127.0.0.1 --port=8000 --no-reload

param(
    [switch]$Fix,
    [int]$Port = 8000
)

$ErrorActionPreference = 'SilentlyContinue'

Write-Host ""
Write-Host "=== Serve Doctor: port $Port ==="

# 1) Count netstat listeners on the port.
$listeners = @(netstat -ano | Select-String ":$Port\s" | Select-String "LISTENING")
Write-Host "  Port $Port listeners: $($listeners.Count)"

# 2) Count `artisan serve` parent processes (including ones that lost the port race).
$serveParents = @(Get-CimInstance Win32_Process -Filter "Name like '%php%'" | Where-Object {
    $_.CommandLine -match 'artisan serve'
})
Write-Host "  artisan serve parents: $($serveParents.Count)"

# 3) Count `php -S` inner workers bound to the port (the actual listeners).
$phpS = @(Get-CimInstance Win32_Process -Filter "Name like '%php%'" | Where-Object {
    $_.CommandLine -match 'server\.php' -and $_.CommandLine -match ":$Port"
})
Write-Host "  php -S inner workers (on :$Port): $($phpS.Count)"

Write-Host ""
if ($listeners.Count -le 1 -and $serveParents.Count -le 1) {
    Write-Host "  Single instance - all good."
    Write-Host ""
    exit 0
}

Write-Host "  WARNING: multiple serve processes detected."
Write-Host "  This causes intermittent upload failures - requests hit the stale server."
Write-Host ""

foreach ($p in $serveParents) {
    Write-Host "    artisan serve parent PID $($p.ProcessId): $($p.CommandLine)"
}
foreach ($w in $phpS) {
    Write-Host "    inner php -S PID $($w.ProcessId): $($w.CommandLine)"
}

if ($Fix) {
    Write-Host ""
    Write-Host "  Killing ALL serve processes (parents, cmd shims, and inner workers)..."
    $targets = @()
    # artisan serve parents
    foreach ($p in $serveParents) { $targets += $p.ProcessId }
    # cmd.exe shims that wrap php -S on the port
    $shims = @(Get-CimInstance Win32_Process | Where-Object {
        $_.Name -match 'cmd' -and $_.CommandLine -match 'server\.php' -and $_.CommandLine -match ":$Port"
    })
    foreach ($s in $shims) { $targets += $s.ProcessId }
    # inner php -S workers
    foreach ($w in $phpS) { $targets += $w.ProcessId }
    # Also kill netstat listener PIDs not already covered
    foreach ($line in $listeners) {
        $pid = ($line -split '\s+')[-1]
        if ($pid -match '^\d+$') { $targets += [int]$pid }
    }

    $targets = @($targets | Sort-Object -Unique)
    foreach ($t in $targets) {
        Stop-Process -Id $t -Force
        Write-Host "    Killed PID $t"
    }

    Start-Sleep -Seconds 2
    Write-Host ""
    Write-Host "  Port $Port is now free. Restart with:"
    Write-Host "    php artisan serve --host=127.0.0.1 --port=$Port --no-reload"
} else {
    Write-Host ""
    Write-Host "  Run with -Fix to kill all serve instances:"
    Write-Host "    powershell -File scripts/serve-doctor.ps1 -Fix"
    Write-Host ""
    Write-Host "  Then restart a single instance:"
    Write-Host "    php artisan serve --host=127.0.0.1 --port=$Port --no-reload"
}
Write-Host ""
