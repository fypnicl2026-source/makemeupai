# MakemeupAI backend first-time setup (Windows PowerShell)
$ErrorActionPreference = "Stop"
Set-Location $PSScriptRoot

Write-Host "Installing Composer dependencies..."
composer install

if (-not (Test-Path ".env")) {
    Copy-Item ".env.example" ".env"
    Write-Host "Created .env from .env.example"
}

Write-Host "Generating application key..."
php artisan key:generate

if (-not (Test-Path "database\database.sqlite")) {
    New-Item -ItemType File -Path "database\database.sqlite" -Force | Out-Null
    Write-Host "Created database/database.sqlite"
}

Write-Host "Running migrations..."
php artisan migrate --force

$storageDirs = @(
    "storage\framework\views",
    "storage\framework\sessions",
    "storage\framework\cache\data",
    "storage\logs"
)
foreach ($dir in $storageDirs) {
    if (-not (Test-Path $dir)) {
        New-Item -ItemType Directory -Path $dir -Force | Out-Null
        Write-Host "Created $dir"
    }
    $gitkeep = Join-Path $dir ".gitkeep"
    if (-not (Test-Path $gitkeep)) {
        New-Item -ItemType File -Path $gitkeep -Force | Out-Null
    }
}

# Linux/macOS equivalent after clone: chmod -R 775 storage bootstrap/cache

Write-Host "Clearing caches..."
php artisan config:clear
php artisan cache:clear
php artisan view:clear

Write-Host ""
Write-Host "Setup complete. Start the API with:"
Write-Host "  php artisan serve"
