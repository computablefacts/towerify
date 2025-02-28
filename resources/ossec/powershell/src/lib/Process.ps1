function InvokeRuleCommand {
  param(
      [string]$command
  )
  
  $output = Invoke-Expression -Command "$command 2>&1"
  return $output -split "`n"
}
