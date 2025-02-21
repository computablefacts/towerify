BeforeAll {
  . "$PSScriptRoot/Display.ps1"
}

Describe 'Display library' {

  Describe 'Show-RuleResult function' {
    It 'Should display a green check mark for a passing rule' {
      # Arrange
      $rule = @{ rule_name = 'Test Rule 1' }
      $testResult = $true
  
      # Act
      $output = Show-RuleResult -testResult $testResult -rule $rule
  
      # Assert
      $output | Should -Contain "${ANSI_GREEN}✔ Test Rule 1${ANSI_RESET}"
    }
  
    It 'Should display a red cross mark for a failing rule' {
      # Arrange  
      $rule = @{ rule_name = 'Test Rule 2' }
      $testResult = $false
  
      # Act
      $output = Show-RuleResult -testResult $testResult -rule $rule
  
      # Assert
      $output | Should -Contain "${ANSI_BRIGHT_RED}✘ Test Rule 2${ANSI_RESET}"
    }

    It 'Should display the link to Cywise if exist' {
      # Arrange
      $rule = @{ 
        rule_name = 'Test Rule 3'
        cywise_link = 'https://cywise_link/'
      }
      $testResult = $true
  
      # Act
      $output = Show-RuleResult -testResult $testResult -rule $rule
  
      # Assert
      $output | Should -Contain "  Plus d'information : https://cywise_link/"
    }  
  }  

  Describe 'Show-TestResult function' {
    It "Should display 'No tests were run.' when no tests are run" {
      # Arrange
      $PassedCount = 0
      $FailedCount = 0
  
      # Act
      $output = Show-TestResult -PassedCount $PassedCount -FailedCount $FailedCount
  
      # Assert
      $output | Should -Contain "${ANSI_YELLOW}No tests were run.${ANSI_RESET}"
    }

    It 'Should display the correct results and color for perfect score' {
      # Arrange
      $PassedCount = 10
      $FailedCount = 0
  
      # Act
      $output = Show-TestResult -PassedCount $PassedCount -FailedCount $FailedCount
  
      # Assert
      $output | Should -Contain 'Tests Passed: 10, Failed: 0'
      $output | Should -Contain "${ANSI_BRIGHT_GREEN}Score: 100/100 (Excellent)${ANSI_RESET}"
    }

    It 'Should display the correct results and color for scores between 75 and 99' {
      # Arrange
      $PassedCount = 75
      $FailedCount = 25
  
      # Act
      $output = Show-TestResult -PassedCount $PassedCount -FailedCount $FailedCount
  
      # Assert
      $output | Should -Contain 'Tests Passed: 75, Failed: 25'
      $output | Should -Contain "${ANSI_GREEN}Score: 75/100 (Bon)${ANSI_RESET}"
    }

    It 'Should display the correct results and color for scores between 50 and 74' {
      # Arrange
      $PassedCount = 50
      $FailedCount = 50
  
      # Act
      $output = Show-TestResult -PassedCount $PassedCount -FailedCount $FailedCount
  
      # Assert
      $output | Should -Contain 'Tests Passed: 50, Failed: 50'
      $output | Should -Contain "${ANSI_BRIGHT_YELLOW}Score: 50/100 (Acceptable)${ANSI_RESET}"
    }

    It 'Should display the correct results and color for scores between 25 and 49' {
      # Arrange
      $PassedCount = 25
      $FailedCount = 75
  
      # Act
      $output = Show-TestResult -PassedCount $PassedCount -FailedCount $FailedCount
  
      # Assert
      $output | Should -Contain 'Tests Passed: 25, Failed: 75'
      $output | Should -Contain "${ANSI_BRIGHT_MAGENTA}Score: 25/100 (Médiocre)${ANSI_RESET}"
    }

    It 'Should display the correct results and color for scores between 0 and 24' {
      # Arrange
      $PassedCount = 0
      $FailedCount = 100
  
      # Act
      $output = Show-TestResult -PassedCount $PassedCount -FailedCount $FailedCount
  
      # Assert
      $output | Should -Contain 'Tests Passed: 0, Failed: 100'
      $output | Should -Contain "${ANSI_BRIGHT_RED}Score: 0/100 (Critique)${ANSI_RESET}"
    }
  }
}
