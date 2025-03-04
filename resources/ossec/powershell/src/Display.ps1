# Définition des constantes de couleur ANSI
#$ANSI_BLACK = "`e[30m"
#$ANSI_RED = "`e[31m"
$ANSI_GREEN = "`e[32m"
$ANSI_YELLOW = "`e[33m"
#$ANSI_BLUE = "`e[34m"
#$ANSI_MAGENTA = "`e[35m"
#$ANSI_CYAN = "`e[36m"
#$ANSI_WHITE = "`e[37m"
#$ANSI_BRIGHT_BLACK = "`e[90m"
$ANSI_BRIGHT_RED = "`e[91m"
$ANSI_BRIGHT_GREEN = "`e[92m"
$ANSI_BRIGHT_YELLOW = "`e[93m"
$ANSI_BRIGHT_BLUE = "`e[94m"
$ANSI_BRIGHT_MAGENTA = "`e[95m"
#$ANSI_BRIGHT_CYAN = "`e[96m"
#$ANSI_BRIGHT_WHITE = "`e[97m"
$ANSI_RESET = "`e[0m"

function Show-RuleResult {
  param (
    [bool]$testResult,
    [hashtable]$rule,
    [array]$exceptions = @()
  )

  if ($exceptions.Count -gt 0) {
    Write-Output "${ANSI_BRIGHT_YELLOW}✘ $($rule['rule_name'])${ANSI_RESET}"
    foreach ($exception in $exceptions) {
      Write-Output "${ANSI_BRIGHT_YELLOW}$($exception.Message)${ANSI_RESET}"
      Write-Output "$($exception.Exception.Message)"
    }
  }
  else {
    if ($testResult) {
      Write-Output "${ANSI_GREEN}✔ $($rule['rule_name'])${ANSI_RESET}"
    }
    else {
      Write-Output "${ANSI_BRIGHT_RED}✘ $($rule['rule_name'])${ANSI_RESET}"
    }
  }

  if ($rule.ContainsKey('cywise_link')) {
    Write-Output "  -> $($rule['cywise_link'])"
  }
}

function Show-TestResult {
  param(
    [int]$PassedCount,
    [int]$FailedCount,
    [int]$ErrorCount = 0
  )

  $TotalCount = $PassedCount + $FailedCount + $ErrorCount
  if ($TotalCount -eq 0) {
    Write-Output "${ANSI_YELLOW}Aucun test lancé.${ANSI_RESET}"
    return
  }

  $Percentage = [math]::Round(($PassedCount / $TotalCount) * 100)
  $Color = $ANSI_GREEN
  if ($Percentage -lt 25) {
    $Level = "Critique"
    $Color = $ANSI_BRIGHT_RED
  }
  elseif ($Percentage -lt 50) {
    $Level = "Médiocre"
    $Color = $ANSI_BRIGHT_MAGENTA
  }
  elseif ($Percentage -lt 75) {
    $Level = "Acceptable"
    $Color = $ANSI_BRIGHT_YELLOW
  }
  elseif ($Percentage -lt 100) {
    $Level = "Bon"
    $Color = $ANSI_GREEN
  }
  elseif ($Percentage -eq 100) {
    $Level = "Excellent"
    $Color = $ANSI_BRIGHT_GREEN
  }

  Write-Output "Tests ${ANSI_GREEN}réussis: $PassedCount, ${ANSI_BRIGHT_RED}échoués: $FailedCount, ${ANSI_BRIGHT_YELLOW}erreurs: $ErrorCount${ANSI_RESET}"
  Write-Output "${Color}Score: $Percentage/100 (${Level})${ANSI_RESET}"
}
