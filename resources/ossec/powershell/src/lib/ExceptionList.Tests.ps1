BeforeAll {
  . "$PSScriptRoot/ExceptionList.ps1"
}

Describe "ExceptionList Tests" {
  BeforeEach {
    Clear-ExceptionList
  }

  It "should add an exception to the list" {
    try {
      throw [System.Exception]::new("Test Exception")
    }
    catch {
      Add-Exception -Exception $_.Exception -Message "Test Message"
    }

    $exceptions = Get-ExceptionList
    $exceptions.Count | Should -Be 1
    $exceptions[0].Exception.Message | Should -Be "Test Exception"
    $exceptions[0].Message | Should -Be "Test Message"
  }

  It "should return an empty list after clearing exceptions" {
    try {
      throw [System.Exception]::new("Test Exception")
    }
    catch {
      Add-Exception -Exception $_.Exception -Message "Test Message"
    }
    $exceptions = Get-ExceptionList
    $exceptions.Count | Should -Be 1

    Clear-ExceptionList
    $exceptions = Get-ExceptionList
    $exceptions.Count | Should -Be 0
  }

  It "should return the correct list of exceptions" {
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
    $exceptions.Count | Should -Be 2
    $exceptions[0].Exception.Message | Should -Be "Exception 1"
    $exceptions[0].Message | Should -Be "Message 1"
    $exceptions[1].Exception.Message | Should -Be "Exception 2"
    $exceptions[1].Message | Should -Be "Message 2"
  }
}