BeforeAll {
  . "$PSScriptRoot/Display.ps1"
  . "$PSScriptRoot/lib/ExceptionList.ps1"
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
        rule_name   = 'Test Rule 3'
        cywise_link = 'https://cywise_link/'
      }
      $testResult = $true
  
      # Act
      $output = Show-RuleResult -testResult $testResult -rule $rule
  
      # Assert
      $output | Should -Contain "  Plus d'information : https://cywise_link/"
    }  

    It 'Should display the link to Cywise if exist with an error' {
      # Arrange
      $rule = @{ 
        rule_name   = 'Test Rule 3'
        cywise_link = 'https://cywise_link/'
      }
      $testResult = $true
      Clear-ExceptionList
      try {
        throw [System.Exception]::new("Exception 1")
      }
      catch {
        Add-Exception -Exception $_.Exception -Message "Message 1"
      }
      $exceptions = Get-ExceptionList

      # Act
      $output = Show-RuleResult -testResult $testResult -rule $rule -exceptions $exceptions
  
      # Assert
      $output | Should -Contain "  Plus d'information : https://cywise_link/"
    }  

    It 'Should display exceptions if they exist' {
      # Arrange
      $rule = @{ rule_name = 'Test Rule 4' }
      $testResult = $false
      Clear-ExceptionList
      try {
        throw [System.Exception]::new("Exception 1")
      }
      catch {
        Add-Exception -Exception $_.Exception -Message "Message 1"
      }
      try {
        throw [System.Exception]::new("Exception 2")
      }
      catch {
        Add-Exception -Exception $_.Exception -Message "Message 2"
      }
      $exceptions = Get-ExceptionList

      # Act
      $output = Show-RuleResult -testResult $testResult -rule $rule -exceptions $exceptions
  
      # Assert
      $output | Should -Contain "${ANSI_BRIGHT_YELLOW}✘ Test Rule 4${ANSI_RESET}"
      $output | Should -Contain "${ANSI_BRIGHT_YELLOW}Message 1${ANSI_RESET}"
      $output | Should -Contain 'Exception 1'
      $output | Should -Contain "${ANSI_BRIGHT_YELLOW}Message 2${ANSI_RESET}"
      $output | Should -Contain 'Exception 2'
    }
  }  

  Describe 'Show-TestResult function' {
    It "Should display 'Aucun test lancé.' when no tests are run" {
      # Arrange
      $PassedCount = 0
      $FailedCount = 0
      $ErrorCount = 0
  
      # Act
      $output = Show-TestResult -PassedCount $PassedCount -FailedCount $FailedCount -ErrorCount $ErrorCount
  
      # Assert
      $output | Should -Contain "${ANSI_YELLOW}Aucun test lancé.${ANSI_RESET}"
    }

    It 'Should display the correct results and color for perfect score' {
      # Arrange
      $PassedCount = 10
      $FailedCount = 0
      $ErrorCount = 0
  
      # Act
      $output = Show-TestResult -PassedCount $PassedCount -FailedCount $FailedCount -ErrorCount $ErrorCount
  
      # Assert
      $output | Should -Contain "Tests ${ANSI_GREEN}réussis: 10, ${ANSI_BRIGHT_RED}échoués: 0, ${ANSI_BRIGHT_YELLOW}erreurs: 0${ANSI_RESET}"
      $output | Should -Contain "${ANSI_BRIGHT_GREEN}Score: 100/100 (Excellent)${ANSI_RESET}"
    }

    It 'Should display the correct results and color for scores between 75 and 99' {
      # Arrange
      $PassedCount = 75
      $FailedCount = 25
  
      # Act
      $output = Show-TestResult -PassedCount $PassedCount -FailedCount $FailedCount
  
      # Assert
      $output | Should -Contain "Tests ${ANSI_GREEN}réussis: 75, ${ANSI_BRIGHT_RED}échoués: 25, ${ANSI_BRIGHT_YELLOW}erreurs: 0${ANSI_RESET}"
      $output | Should -Contain "${ANSI_GREEN}Score: 75/100 (Bon)${ANSI_RESET}"
    }

    It 'Should display the correct results and color for scores between 75 and 99 with errors' {
      # Arrange
      $PassedCount = 75
      $FailedCount = 15
      $ErrorCount = 10
  
      # Act
      $output = Show-TestResult -PassedCount $PassedCount -FailedCount $FailedCount -ErrorCount $ErrorCount
  
      # Assert
      $output | Should -Contain "Tests ${ANSI_GREEN}réussis: 75, ${ANSI_BRIGHT_RED}échoués: 15, ${ANSI_BRIGHT_YELLOW}erreurs: 10${ANSI_RESET}"
      $output | Should -Contain "${ANSI_GREEN}Score: 75/100 (Bon)${ANSI_RESET}"
    }

    It 'Should display the correct results and color for scores between 50 and 74' {
      # Arrange
      $PassedCount = 50
      $FailedCount = 50
  
      # Act
      $output = Show-TestResult -PassedCount $PassedCount -FailedCount $FailedCount
  
      # Assert
      $output | Should -Contain "Tests ${ANSI_GREEN}réussis: 50, ${ANSI_BRIGHT_RED}échoués: 50, ${ANSI_BRIGHT_YELLOW}erreurs: 0${ANSI_RESET}"
      $output | Should -Contain "${ANSI_BRIGHT_YELLOW}Score: 50/100 (Acceptable)${ANSI_RESET}"
    }

    It 'Should display the correct results and color for scores between 50 and 74 with errors' {
      # Arrange
      $PassedCount = 50
      $FailedCount = 47
      $ErrorCount = 3
  
      # Act
      $output = Show-TestResult -PassedCount $PassedCount -FailedCount $FailedCount -ErrorCount $ErrorCount
  
      # Assert
      $output | Should -Contain "Tests ${ANSI_GREEN}réussis: 50, ${ANSI_BRIGHT_RED}échoués: 47, ${ANSI_BRIGHT_YELLOW}erreurs: 3${ANSI_RESET}"
      $output | Should -Contain "${ANSI_BRIGHT_YELLOW}Score: 50/100 (Acceptable)${ANSI_RESET}"
    }

    It 'Should display the correct results and color for scores between 25 and 49' {
      # Arrange
      $PassedCount = 25
      $FailedCount = 75
  
      # Act
      $output = Show-TestResult -PassedCount $PassedCount -FailedCount $FailedCount
  
      # Assert
      $output | Should -Contain "Tests ${ANSI_GREEN}réussis: 25, ${ANSI_BRIGHT_RED}échoués: 75, ${ANSI_BRIGHT_YELLOW}erreurs: 0${ANSI_RESET}"
      $output | Should -Contain "${ANSI_BRIGHT_MAGENTA}Score: 25/100 (Médiocre)${ANSI_RESET}"
    }

    It 'Should display the correct results and color for scores between 25 and 49 with errors' {
      # Arrange
      $PassedCount = 25
      $FailedCount = 58
      $ErrorCount = 17
  
      # Act
      $output = Show-TestResult -PassedCount $PassedCount -FailedCount $FailedCount -ErrorCount $ErrorCount
  
      # Assert
      $output | Should -Contain "Tests ${ANSI_GREEN}réussis: 25, ${ANSI_BRIGHT_RED}échoués: 58, ${ANSI_BRIGHT_YELLOW}erreurs: 17${ANSI_RESET}"
      $output | Should -Contain "${ANSI_BRIGHT_MAGENTA}Score: 25/100 (Médiocre)${ANSI_RESET}"
    }

    It 'Should display the correct results and color for scores between 0 and 24' {
      # Arrange
      $PassedCount = 0
      $FailedCount = 100
  
      # Act
      $output = Show-TestResult -PassedCount $PassedCount -FailedCount $FailedCount
  
      # Assert
      $output | Should -Contain "Tests ${ANSI_GREEN}réussis: 0, ${ANSI_BRIGHT_RED}échoués: 100, ${ANSI_BRIGHT_YELLOW}erreurs: 0${ANSI_RESET}"
      $output | Should -Contain "${ANSI_BRIGHT_RED}Score: 0/100 (Critique)${ANSI_RESET}"
    }
  }

  It 'Should display the correct results and color for scores between 0 and 24 with errors' {
    # Arrange
    $PassedCount = 0
    $FailedCount = 99
    $ErrorCount = 1

    # Act
    $output = Show-TestResult -PassedCount $PassedCount -FailedCount $FailedCount -ErrorCount $ErrorCount

    # Assert
    $output | Should -Contain "Tests ${ANSI_GREEN}réussis: 0, ${ANSI_BRIGHT_RED}échoués: 99, ${ANSI_BRIGHT_YELLOW}erreurs: 1${ANSI_RESET}"
    $output | Should -Contain "${ANSI_BRIGHT_RED}Score: 0/100 (Critique)${ANSI_RESET}"
  }
}
