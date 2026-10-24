param(
    [int]$RetentionDays = 30
)

$ErrorActionPreference = 'Stop'

$scriptDir = Split-Path -Parent $MyInvocation.MyCommand.Path
$projectRoot = Split-Path -Parent $scriptDir
$backupDir = Join-Path $projectRoot 'storage\backups'
$date = Get-Date -Format 'yyyyMMdd-HHmmss'
$backupFile = Join-Path $backupDir "j-zakat-$date.sql"

if (!(Test-Path $backupDir)) {
    New-Item -ItemType Directory -Path $backupDir -Force | Out-Null
}

$mysqlPath = 'C:\xampp\mysql\bin\mysqldump.exe'
if (!(Test-Path $mysqlPath)) {
    $mysqlPath = 'mysqldump'
}

& $mysqlPath -u root --databases j-zakat > $backupFile 2>&1

if ($LASTEXITCODE -ne 0) {
    Write-Error "Backup failed"
    exit 1
}

Compress-Archive -Path $backupFile -DestinationPath "$backupFile.zip" -Force
Remove-Item $backupFile

Write-Output "Backup created: $backupFile.zip"

Get-ChildItem $backupDir -Filter '*.zip' |
    Where-Object { $_.LastWriteTime -lt (Get-Date).AddDays(-$RetentionDays) } |
    Remove-Item -Force

exit 0
