#!/usr/bin/env pwsh
# k6 Load Testing Script for Roary
# Usage: .\run-k6-test.ps1 -Scenario <scenario> [-Target <url>] [-VUs <number>] [-Duration <time>] [-SessionId <id>] [-Validate]

param(
    [Parameter(Mandatory=$true)]
    [ValidateSet('doom-scroll', 'live-ticker', 'shout-out')]
    [string]$Scenario,
    
    [string]$Target = 'http://localhost',
    
    [int]$VUs = 0,
    
    [string]$Duration = '',
    
    [string]$SessionId = '',
    
    [switch]$Validate
)

# Build environment variables
$envVars = @("-e", "BASE_URL=$Target")

if ($SessionId) {
    $envVars += @("-e", "PHPSESSID=$SessionId")
}

if ($Validate) {
    $envVars += @("-e", "VALIDATE=true")
}

# Build k6 options
$k6Options = @()

if ($VUs -gt 0) {
    $k6Options += @("--vus", $VUs)
}

if ($Duration) {
    $k6Options += @("--duration", $Duration)
}

Write-Host "Running k6 test: $Scenario" -ForegroundColor Cyan
Write-Host "Target: $Target" -ForegroundColor Gray
if ($VUs -gt 0) { Write-Host "Virtual Users: $VUs" -ForegroundColor Gray }
if ($Duration) { Write-Host "Duration: $Duration" -ForegroundColor Gray }
if ($Validate) { Write-Host "Validation: Enabled" -ForegroundColor Green }
Write-Host ""

# Run k6 test
docker-compose run --rm $envVars k6 run $k6Options "/scripts/$Scenario.js"
