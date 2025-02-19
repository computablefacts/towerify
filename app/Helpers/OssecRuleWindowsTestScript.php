<?php

namespace App\Helpers;

class OssecRuleWindowsTestScript
{

    public static function begin(): string
    {
        return <<<EOF
if (\$PSVersionTable.PSVersion.Major -lt 6) {
    Write-Host "Ce script doit être exécuté avec PowerShell 6 ou supérieur." -ForegroundColor Yellow
    Write-Host "Suivez ce lien vers la documentation officielle pour installer la dernière version de PowerShell :"
    Write-Host "  https://learn.microsoft.com/fr-fr/powershell/scripting/install/installing-powershell-on-windows#msi"
    exit 1
}
 
function DirectoryExists {
  param (
    [string]\$directoryPath
  )

  return Test-Path -Path \$directoryPath -PathType Container
}

function FileExists {
  param (
    [string]\$filePath
  )

  return Test-Path -Path \$filePath -PathType Leaf
}

function ListFiles {
  param (
    [string]\$Path
  )

  try {
    Get-ChildItem -Path \$Path -ErrorAction Stop | Select-Object -ExpandProperty Name
  }
  catch {
    throw \$_
  }
}

function FetchFile {
  param (
    [string]\$file
  )

  try {
    Get-Content -Path \$file -ErrorAction Stop
  }
  catch {
    throw \$_
  }
}

function InvokeRuleCommand {
  param(
      [string]\$command
  )
  
  \$output = Invoke-Expression -Command \$command
  return \$output -split "`n"
}

function Convert-RegistryKey {
  param (
      [string]\$Key
  )

  # Dictionnaire des remplacements
  \$replacements = @{
      "HKEY_LOCAL_MACHINE\" = "HKLM:\"
      "HKEY_CURRENT_USER\" = "HKCU:\"
      "HKEY_CLASSES_ROOT\" = "HKCR:\"
      "HKEY_USERS\" = "HKU:\"
      "HKEY_CURRENT_CONFIG\" = "HKCC:\"
  }

  foreach (\$fullKey in \$replacements.Keys) {
      if (\$Key -like "\$fullKey*") {
          return \$Key -replace [regex]::Escape(\$fullKey), \$replacements[\$fullKey]
      }
  }

  # Retourner la clé inchangée si aucun remplacement n'est trouvé
  return \$Key
}

function RegistryEntryExists {
  param (
    [string]\$registryPath
  )

  \$convertedPath = Convert-RegistryKey -Key \$registryPath

  return Test-Path -Path \$convertedPath
}

function FetchRegistryKeys {
  param (
    [string]\$entry
  )

  try {
    \$keys = (Get-Item -Path \$entry -ErrorAction Stop).Property
    return \$keys
  }
  catch {
    throw \$_
  }
}

function FetchRegistryValue {
  param (
    [string]\$entry,
    [string]\$propertyName
  )
  
  try {
    Get-ItemPropertyValue -Path \$entry -Name \$propertyName -ErrorAction Stop
  }
  catch {
    throw \$_
  }
}

# Définition des constantes de couleur ANSI
\$ansiVariables = @(
  @{ Name = "ANSI_BLACK"; Value = "`e[30m" },
  @{ Name = "ANSI_RED"; Value = "`e[31m" },
  @{ Name = "ANSI_GREEN"; Value = "`e[32m" },
  @{ Name = "ANSI_YELLOW"; Value = "`e[33m" },
  @{ Name = "ANSI_BLUE"; Value = "`e[34m" },
  @{ Name = "ANSI_MAGENTA"; Value = "`e[35m" },
  @{ Name = "ANSI_CYAN"; Value = "`e[36m" },
  @{ Name = "ANSI_WHITE"; Value = "`e[37m" },
  @{ Name = "ANSI_BRIGHT_BLACK"; Value = "`e[90m" },
  @{ Name = "ANSI_BRIGHT_RED"; Value = "`e[91m" },
  @{ Name = "ANSI_BRIGHT_GREEN"; Value = "`e[92m" },
  @{ Name = "ANSI_BRIGHT_YELLOW"; Value = "`e[93m" },
  @{ Name = "ANSI_BRIGHT_BLUE"; Value = "`e[94m" },
  @{ Name = "ANSI_BRIGHT_MAGENTA"; Value = "`e[95m" },
  @{ Name = "ANSI_BRIGHT_CYAN"; Value = "`e[96m" },
  @{ Name = "ANSI_BRIGHT_WHITE"; Value = "`e[97m" },
  @{ Name = "ANSI_RESET"; Value = "`e[0m" }
)

# Fonction pour réinitialiser les variables ANSI
function New-AnsiColorConstants {
  param (
    [Array]\$Variables
  )

  foreach (\$var in \$Variables) {
    # Supprime la variable
    if (Get-Variable -Name \$var.Name -ErrorAction SilentlyContinue | Out-Null) {
      Remove-Variable -Name \$var.Name -Force
    }

    # Crée la variable avec l'option ReadOnly
    Set-Variable -Name \$var.Name -Value \$var.Value -Option ReadOnly
  }
}

# Appel de la fonction pour créer les constantes ANSI
New-AnsiColorConstants -Variables \$ansiVariables

function Show-RuleResult {
  param (
    [bool]\$testResult,
    [hashtable]\$rule
  )

  if (\$testResult) {
    Write-Output "\${ANSI_GREEN}✔ \$(\$rule['rule_name'])\${ANSI_RESET}"
  }
  else {
    Write-Output "\${ANSI_BRIGHT_RED}✘ \$(\$rule['rule_name'])\${ANSI_RESET}"
  }

  if (\$rule.ContainsKey('cywise_link')) {
    Write-Output "  Plus d'information : \$(\$rule['cywise_link'])"
  }
}

function Show-TestResult {
  param(
    [int]\$PassedCount,
    [int]\$FailedCount
  )

  \$TotalCount = \$PassedCount + \$FailedCount
  if (\$TotalCount -eq 0) {
    Write-Output "\${ANSI_YELLOW}No tests were run.\${ANSI_RESET}"
    return
  }

  \$Percentage = [math]::Round((\$PassedCount / \$TotalCount) * 100)
  \$Color = \$ANSI_GREEN
  if (\$Percentage -lt 25) {
    \$Level = "Critique"
    \$Color = \$ANSI_BRIGHT_RED
  }
  elseif (\$Percentage -lt 50) {
    \$Level = "Médiocre"
    \$Color = \$ANSI_BRIGHT_MAGENTA
  }
  elseif (\$Percentage -lt 75) {
    \$Level = "Acceptable"
    \$Color = \$ANSI_BRIGHT_YELLOW
  }
  elseif (\$Percentage -lt 100) {
    \$Level = "Bon"
    \$Color = \$ANSI_GREEN
  }
  elseif (\$Percentage -eq 100) {
    \$Level = "Excellent"
    \$Color = \$ANSI_BRIGHT_GREEN
  }

  Write-Output "Tests Passed: \$PassedCount, Failed: \$FailedCount"
  Write-Output "\${Color}Score: \$Percentage/100 (\${Level})\${ANSI_RESET}"
}

function Evaluate {
  param (
    [hashtable]\$ctx,
    [hashtable]\$rule
  )

  \$matchType = \$rule['match_type']
  foreach (\$r in \$rule['rules']) {
    \$match_result = Match \$ctx \$r
    \$isOk = if (\$r['negate']) { -not \$match_result } else { \$match_result }
    if ((\$matchType -eq 'all' -and -not \$isOk) -or (\$matchType -eq 'none' -and \$isOk)) {
      return \$false
    }
    if (\$matchType -eq 'any' -and \$isOk) {
      return \$true
    }
  }
  return (\$matchType -eq 'all') -or (\$matchType -eq 'none')
}

function Match {
  param (
    [hashtable]\$ctx,
    [hashtable]\$rule
  )

  switch (\$rule['type']) {
    'file' {
      return (\$rule['files'] | Where-Object { \$ctx['file_exists'].Invoke(\$_) } | Where-Object {
          if (-not (\$rule.ContainsKey('expr') -and \$null -ne \$rule['expr'])) { 
            return \$true 
          }
        (\$ctx['fetch_file'].Invoke(\$_) | Where-Object { MatchExpression \$_ \$rule['expr'] } | Measure-Object | Select-Object -ExpandProperty Count) -gt 0
        } | Measure-Object | Select-Object -ExpandProperty Count) -gt 0
    }
    'directory' {
      return (\$rule['directories'] | Where-Object { \$ctx['directory_exists'].Invoke(\$_) } | Where-Object {
          if (-not (\$rule.ContainsKey('files') -and \$null -ne \$rule['files'])) { 
            return \$true 
          }
        (\$ctx['list_files'].Invoke(\$_) | Where-Object { MatchPattern \$_ \$rule['files'] } | Where-Object {
            if (-not (\$rule.ContainsKey('expr') -and \$null -ne \$rule['expr'])) { 
              return \$true 
            }  
          (\$ctx['fetch_file'].Invoke(\$_) | Where-Object { MatchExpression \$_ \$rule['expr'] } | Measure-Object | Select-Object -ExpandProperty Count) -gt 0
          } | Measure-Object | Select-Object -ExpandProperty Count) -gt 0
        } | Measure-Object | Select-Object -ExpandProperty Count) -gt 0
    }
    'registry' {
      return (@(\$rule['entry']) | Where-Object { \$ctx['registry_entry_exists'].Invoke(\$_) } | Where-Object {
          \$entry = \$_
          if (-not \$rule.ContainsKey('key') -or \$null -eq \$rule['key']) { 
            return \$true 
          }
        (\$ctx['fetch_registry_keys'].Invoke(\$_) | Where-Object { MatchPattern \$_ \$rule['key'] } | Where-Object {
            if (-not \$rule.ContainsKey('expr') -or \$null -eq \$rule['expr']) { 
              return \$true 
            }
          (\$ctx['fetch_registry_value'].Invoke(\$entry, \$_) | Where-Object { MatchExpression \$_ \$rule['expr'] } | Measure-Object | Select-Object -ExpandProperty Count) -gt 0
          } | Measure-Object | Select-Object -ExpandProperty Count) -gt 0
        } | Measure-Object | Select-Object -ExpandProperty Count) -gt 0
    }
    'command' {
      return (@(\$rule['cmd']) | Where-Object {
          if (-not \$rule.ContainsKey('expr') -or \$null -eq \$rule['expr']) { 
            return \$true
          }
        (\$ctx['execute'].Invoke(\$_) | Where-Object { MatchExpression \$_ \$rule['expr'] } | Measure-Object | Select-Object -ExpandProperty Count) -gt 0
        } | Measure-Object | Select-Object -ExpandProperty Count) -gt 0
    }
    default {
      return \$false
    }
  }
}

function MatchExpression {
  param (
    [string]\$line,
    [array]\$expr
  )

  foreach (\$e in \$expr) {
    if (-not (MatchPattern -value \$line -pattern \$e)) {
      return \$false
    }
  }
  return \$true
}

function MatchPattern {
  param (
    [string]\$value,
    [string]\$pattern
  )

  Write-Debug "Matching \$value against \$pattern"

  # Determine if the match must be negated
  \$negate = \$false
  if (\$pattern.StartsWith('!')) {
    \$negate = \$true
    \$pattern = \$pattern.Substring(1)
  }

  # Simple regex match: either it matches or it doesn't!
  if (\$pattern.StartsWith('r:')) {
    \$pattern = \$pattern.Substring(2)
    if (\$negate) {
      return -not [regex]::IsMatch(\$value, \$pattern, [System.Text.RegularExpressions.RegexOptions]::IgnoreCase)
    }
    return [regex]::IsMatch(\$value, \$pattern, [System.Text.RegularExpressions.RegexOptions]::IgnoreCase)
  }


  # TODO: Add tests for these 3 operators
  # Simple comparisons
  # Values can be preceded by:
  #  - =: (for equal) - default
  #  - >: (for strcmp greater)
  #  - <: (for strcmp  lower)
  # if (\$pattern.StartsWith('<:')) {
  #   \$pattern = \$pattern.Substring(2)
  #   return if (\$negate) { \$pattern -ge \$value } else { \$pattern -lt \$value }
  # }
  # if (\$pattern.StartsWith('>:')) {
  #   \$pattern = \$pattern.Substring(2)
  #   return if (\$negate) { \$pattern -le \$value } else { \$pattern -gt \$value }
  # }
  # if (\$pattern.StartsWith('=:')) {
  #   \$pattern = \$pattern.Substring(2)
  #   return if (\$negate) { \$pattern -ne \$value } else { \$pattern -eq \$value }
  # }

  # Extract a specific sequence from the input string then compare this sequence against a given value
  \$match_result = [regex]::Match(\$pattern, '^n:(.*)\s+compare\s+([><=!]+)\s*(.*)\$', [System.Text.RegularExpressions.RegexOptions]::IgnoreCase)
  if (\$match_result.Success) {
    \$pattern = \$match_result.Groups[1].Value.Trim()
    \$operator = \$match_result.Groups[2].Value.Trim()
    \$compareValue = [int]\$match_result.Groups[3].Value.Trim()
    \$match = [regex]::Match(\$value, \$pattern, [System.Text.RegularExpressions.RegexOptions]::IgnoreCase)
    if (-not \$match.Success) {
      \$isOk = \$negate
    }
    else {
      \$matchedValue = [int]\$match.Groups[1].Value
      switch (\$operator) {
        '>' { \$isOk = if (\$negate) { \$matchedValue -le \$compareValue } else { \$matchedValue -gt \$compareValue } }
        '<' { \$isOk = if (\$negate) { \$matchedValue -ge \$compareValue } else { \$matchedValue -lt \$compareValue } }
        '=' { \$isOk = if (\$negate) { \$matchedValue -ne \$compareValue } else { \$matchedValue -eq \$compareValue } }
        '==' { \$isOk = if (\$negate) { \$matchedValue -ne \$compareValue } else { \$matchedValue -eq \$compareValue } }
        '>=' { \$isOk = if (\$negate) { \$matchedValue -lt \$compareValue } else { \$matchedValue -ge \$compareValue } }
        '<=' { \$isOk = if (\$negate) { \$matchedValue -gt \$compareValue } else { \$matchedValue -le \$compareValue } }
        '!=' { \$isOk = if (\$negate) { \$matchedValue -eq \$compareValue } else { \$matchedValue -ne \$compareValue } }
        '<>' { \$isOk = if (\$negate) { \$matchedValue -eq \$compareValue } else { \$matchedValue -ne \$compareValue } }
        default {
          Write-Host "Unknown operation: \$pattern \$operator \$compareValue"
          \$isOk = \$false
        }
      }
    }
    return \$isOk
  }

  if (\$negate) { 
    return \$value -ne \$pattern 
  }
  else { 
    return \$value -eq \$pattern 
  } 
}


function Test-RulesList {
    param(
        [string[]]\$RulesList
    )

    \$ctx = @{
        'file_exists'           = { FileExists -filePath \$args[0] }
        'directory_exists'      = { DirectoryExists -directoryPath \$args[0] }
        'registry_entry_exists' = { RegistryEntryExists -registryPath \$args[0] }
        'fetch_file'            = { FetchFile -file \$args[0] }
        'list_files'            = { ListFiles -Path \$args[0] }
        'fetch_registry_keys'   = { FetchRegistryKeys -entry \$args[0] }
        'fetch_registry_value'  = { FetchRegistryValue -entry \$args[0] -propertyName \$args[1] }
        'execute'               = { InvokeRuleCommand -command \$args[0] }
    }
    
    \$failedCount = 0
    \$passedCount = 0
    foreach (\$rule in \$rulesList) {
        \$ruleObject = \$rule | ConvertFrom-Json -AsHashtable
        \$result = Evaluate \$ctx \$ruleObject
        if (\$result) {
            \$passedCount++
        }
        else {
            \$failedCount++
        }
        Show-RuleResult \$result \$ruleObject
    }
    Show-TestResult \$passedCount \$failedCount

}

function Test-OssecRules {
    param(
        [string]\$RulesFile
    )

    # Déclaration de la variable \$rules par défaut
    \$rules = @"
EOF;
    }

    public static function end(): string
    {
        return <<<EOF
"@
    
    if (\$RulesFile) {
        \$rulesList = Get-Content \$RulesFile | Where-Object { \$_ -match '\S' }
    }
    else {
        \$rulesList = \$rules -split "`n" | Where-Object { \$_ -match '\S' }
    }
    
    Test-RulesList \$rulesList
}

# Appel de la fonction si le script est exécuté directement
if (\$MyInvocation.InvocationName -ne '.') {
    Test-OssecRules @args
}

EOF;
    }

}
