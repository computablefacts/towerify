$sourceScript = Get-Content -Path '.\src\Test-OssecRules.ps1' -Raw
$outputPath = '.\build\Test-OssecRules.ps1'

$pattern = '^\. "\$PSScriptRoot/(.+)"$'
$sourceScript -split "`r`n" | ForEach-Object {
    if ($_ -match $pattern) {
        $importPath = Join-Path './src' $Matches[1]
        Get-Content $importPath -Raw
    } else {
        $_
    }
} | Set-Content $outputPath
