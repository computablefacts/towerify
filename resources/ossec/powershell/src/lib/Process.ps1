function InvokeRuleCommand {
  param(
      [string]$command
  )
  
  $output = Invoke-Expression -Command $command
  return $output -split "`n"
}
