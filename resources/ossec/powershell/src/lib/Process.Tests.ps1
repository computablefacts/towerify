BeforeAll {
  . "$PSScriptRoot/Process.ps1"
}

Describe 'Process library' {
  Describe "InvokeRuleCommand Tests" {

    It "Should return output as array of strings" {
      $command = "echo 'Hello, World!'"
      $result = InvokeRuleCommand -command $command
      $result | Should -Be @('Hello, World!')
    }

    It "Should handle multiple lines of output" {
      $command = "echo 'Line1'; echo 'Line2'"
      $result = InvokeRuleCommand -command $command
      $result | Should -Be @('Line1', 'Line2')
    }

    It "Should return empty array for no output" {
      $command = "echo 'Dummy' | Out-Null"
      $result = InvokeRuleCommand -command $command
      $result | Should -Be @()
    }

    It "Should execute complex command" {
      $command = "Get-Process | Select-Object -First 1 | Format-Table -HideTableHeaders"
      $result = InvokeRuleCommand -command $command
      $result.Length | Should -BeGreaterThan 0
    }
  }
}
