Describe 'Test-OssecRules function' {
  BeforeAll {
    . "$PSScriptRoot/Test-OssecRules.ps1"
      
    Mock Evaluate {
      param (
        [hashtable]$ctx,
        [hashtable]$rule
      )
      # True only for the 2 default rules
      return $rule.match_type -eq 'all'
    }
  }

  It 'Should use the default rules when -RulesFile is not specified' {
    # Act
    $result = Test-OssecRules

    # Assert
    # Line 3: Tests Passed: 2, Failed: 0
    $result[2] | Should -Match 'réussis: 2'
    $result[2] | Should -Match 'échoués: 0'
  }

  It 'Should load the rules from the file when -RulesFile is specified' {
    # Arrange
    $testFile = 'TestRules.jsonl'
    @(
      '{"condition": "C", "result": "Z"}',
      '{"condition": "D", "result": "W"}'
    ) | Set-Content $testFile

    # Act
    $result = Test-OssecRules -RulesFile $testFile

    # Assert
    # Line 3: Tests Passed: 0, Failed: 2
    $result[2] | Should -Match 'réussis: 0'
    $result[2] | Should -Match 'échoués: 2'

    # Cleanup
    Remove-Item $testFile
  }
}
