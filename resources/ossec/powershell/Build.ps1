$sourceScript = Get-Content -Path '.\src\Test-OssecRules.ps1' -Raw
$outputPath = '.\build\Test-OssecRules.ps1'

$pattern = '^\. "\$PSScriptRoot/(.+)"$'
$sourceScript -split "`n" | ForEach-Object {
    if ($_ -match $pattern) {
        $importPath = Join-Path './src' $Matches[1]
        Get-Content $importPath -Raw
    } else {
        $_
    }
} | Set-Content $outputPath

$outputPathWithoutRules = '.\Test-OssecRules.ps1'
$script = (Get-Content -Path $outputPath | Out-String)
$startIndex = $script.IndexOf("@'") + 3
$endIndex = $script.IndexOf("'@") - 1
$script = $script.Substring(0, $startIndex) + "__PUT_RULES_HERE__" + $script.Substring($endIndex)
Set-Content -Path $outputPathWithoutRules -Value $script
