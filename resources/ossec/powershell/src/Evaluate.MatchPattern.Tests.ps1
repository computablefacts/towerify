BeforeAll {
  . "$PSScriptRoot/Evaluate.ps1"
}

Describe 'MatchPattern function' {

  Context 'Simple' {

    It 'should match simple pattern' {
      # Arrange
      $text = 'example'
      $pattern = 'example'

      # Act
      $result = MatchPattern $text $pattern

      # Assert
      $result | Should -Be $true
    }

    It 'should not match simple pattern' {
      # Arrange
      $text = 'example'
      $pattern = 'different'

      # Act
      $result = MatchPattern $text $pattern

      # Assert
      $result | Should -Be $false
    }

    It 'should match negated simple pattern' {
      # Arrange
      $text = 'example'
      $pattern = '!different'

      # Act
      $result = MatchPattern $text $pattern

      # Assert
      $result | Should -Be $true
    }

    It 'should not match negated simple pattern' {
      # Arrange
      $text = 'example'
      $pattern = '!example'

      # Act
      $result = MatchPattern $text $pattern

      # Assert
      $result | Should -Be $false
    }
  }

  Context 'Regex' {

    It 'should match a substring' {
      # Arrange
      $text = 'LSARPC, NETLOGON, SAMR'
      $pattern = 'r:LSARPC'

      # Act
      $result = MatchPattern $text $pattern

      # Assert
      $result | Should -Be $true
    }

    It 'should not match a substring' {
      # Arrange
      $text = 'LSARPC, NETLOGON, SAMR'
      $pattern = 'r:BROWSER'

      # Act
      $result = MatchPattern $text $pattern

      # Assert
      $result | Should -Be $false
    }

    It 'should match a substring with negation' {
      # Arrange
      $text = 'LSARPC, NETLOGON, SAMR'
      $pattern = '!r:BROWSER'

      # Act
      $result = MatchPattern $text $pattern

      # Assert
      $result | Should -Be $true
    }

    It 'should not match a substring with negation' {
      # Arrange
      $text = 'LSARPC, NETLOGON, SAMR'
      $pattern = '!r:LSARPC'

      # Act
      $result = MatchPattern $text $pattern

      # Assert
      $result | Should -Be $false
    }

    It 'should match a word list' {
      # Arrange
      $text = 'Lorem ipsum dolor sit amet'
      $pattern = 'r:\w+'

      # Act
      $result = MatchPattern $text $pattern

      # Assert
      $result | Should -Be $true
    }

    It 'should match a string without spaces' {
      # Arrange
      $text = 'a3f5ge'
      $pattern = 'r:\S+'

      # Act
      $result = MatchPattern $text $pattern

      # Assert
      $result | Should -Be $true
    }

    It 'should match particular regexs' {
      # Assert
      MatchPattern "User name                    Guest`nAccount active               No`nAccount expires              Never" 'r:Account active\s+No' | Should -Be $true
      MatchPattern "The user name could not be found.`nMore help is available by typing NET HELPMSG 2221.`n`n" 'r:The user name could not be found.' | Should -Be $true
      MatchPattern '0' 'r:^0$|^1$' | Should -Be $true
      MatchPattern '1' 'r:^0$|^1$' | Should -Be $true
      MatchPattern '2' 'r:^0$|^1$' | Should -Be $false
      MatchPattern 'RSYNC_ENABLE=true' '!r:^#' | Should -Be $true
      MatchPattern 'RSYNC_ENABLE=true' 'r:RSYNC_ENABLE\s*\t*=\s*\t*false' | Should -Be $false
    }
  }

  Context 'Specific sequence with compare' {

    It 'should compare greater than' {
      # Arrange
      $pattern = 'n:^(\d+)\s*profiles are loaded compare > 0'

      # Assert
      MatchPattern '10 profiles are loaded' $pattern | Should -Be $true
      MatchPattern '0 profiles are loaded' $pattern | Should -Be $false
    }

    It 'should compare greater than with negate' {
      # Arrange
      $pattern = '!n:^(\d+)\s*profiles are loaded compare > 0'

      # Assert
      MatchPattern '10 profiles are loaded' $pattern | Should -Be $false
      MatchPattern '0 profiles are loaded' $pattern | Should -Be $true
    }

    It 'should compare less than' {
      # Arrange
      $pattern = 'n:remember\s*\t*=\s*\t*(\d+) compare < 5'

      # Assert
      MatchPattern 'remember = 4' $pattern | Should -Be $true
      MatchPattern 'remember=12' $pattern | Should -Be $false
    }

    It 'should compare less than with negate' {
      # Arrange
      $pattern = '!n:remember\s*\t*=\s*\t*(\d+) compare < 5'

      # Assert
      MatchPattern 'remember = 4' $pattern | Should -Be $false
      MatchPattern 'remember=12' $pattern | Should -Be $true
    }

    It 'should compare equal (=)' {
      # Arrange
      $pattern = 'n:dictcheck\s*\t*=\s*\t*(\d+) compare = 1'

      # Assert
      MatchPattern 'dictcheck = 1' $pattern | Should -Be $true
      MatchPattern 'dictcheck = 0' $pattern | Should -Be $false
    }

    It 'should compare equal (=) with negate' {
      # Arrange
      $pattern = '!n:dictcheck\s*\t*=\s*\t*(\d+) compare = 1'

      # Assert
      MatchPattern 'dictcheck = 1' $pattern | Should -Be $false
      MatchPattern 'dictcheck = 0' $pattern | Should -Be $true
    }

    It 'should compare equal (==)' {
      # Arrange
      $pattern = 'n:^\s*clientalivecountmax\s*\t*(\d+) compare == 0'

      # Assert
      MatchPattern 'clientalivecountmax 0' $pattern | Should -Be $true
      MatchPattern 'clientalivecountmax 1' $pattern | Should -Be $false
    }

    It 'should compare equal (==) with negate' {
      # Arrange
      $pattern = '!n:^\s*clientalivecountmax\s*\t*(\d+) compare == 0'

      # Assert
      MatchPattern 'clientalivecountmax 0' $pattern | Should -Be $false
      MatchPattern 'clientalivecountmax 1' $pattern | Should -Be $true
    }

    It 'should compare greater than or equal' {
      # Arrange
      $pattern = 'n:minlen\s*\t*=\s*\t*(\d+) compare >= 24'

      # Assert
      MatchPattern 'minlen = 123' $pattern | Should -Be $true
      MatchPattern 'minlen = 24' $pattern | Should -Be $true
      MatchPattern 'minlen=3' $pattern | Should -Be $false
    }

    It 'should compare greater than or equal with negate' {
      # Arrange
      $pattern = '!n:minlen\s*\t*=\s*\t*(\d+) compare >= 24'

      # Assert
      MatchPattern 'minlen = 123' $pattern | Should -Be $false
      MatchPattern 'minlen = 24' $pattern | Should -Be $false
      MatchPattern 'minlen=3' $pattern | Should -Be $true
    }

    It 'should compare less than or equal' {
      # Arrange
      $pattern = 'n:^\s*\t*PASS_MAX_DAYS\s*\t*(\d+) compare <= 365'

      # Assert
      MatchPattern 'PASS_MAX_DAYS 234' $pattern | Should -Be $true
      MatchPattern 'PASS_MAX_DAYS 365' $pattern | Should -Be $true
      MatchPattern 'PASS_MAX_DAYS 366' $pattern | Should -Be $false
    }

    It 'should compare less than or equal with negate' {
      # Arrange
      $pattern = '!n:^\s*\t*PASS_MAX_DAYS\s*\t*(\d+) compare <= 365'

      # Assert
      MatchPattern 'PASS_MAX_DAYS 234' $pattern | Should -Be $false
      MatchPattern 'PASS_MAX_DAYS 365' $pattern | Should -Be $false
      MatchPattern 'PASS_MAX_DAYS 366' $pattern | Should -Be $true
    }

    It 'should compare not equal' {
      # Arrange
      $pattern = 'n:umask \d\d(\d) compare != 7'

      # Assert
      MatchPattern 'umask 022' $pattern | Should -Be $true
      MatchPattern 'umask 007' $pattern | Should -Be $false

      # Arrange
      $pattern = 'n:umask \d\d(\d) compare <> 7'

      # Assert
      MatchPattern 'umask 022' $pattern | Should -Be $true
      MatchPattern 'umask 007' $pattern | Should -Be $false
    }

    It 'should compare not equal with negate' {
      # Arrange
      $pattern = '!n:umask \d\d(\d) compare != 7'

      # Assert
      MatchPattern 'umask 022' $pattern | Should -Be $false
      MatchPattern 'umask 007' $pattern | Should -Be $true

      # Arrange
      $pattern = '!n:umask \d\d(\d) compare <> 7'

      # Assert
      MatchPattern 'umask 022' $pattern | Should -Be $false
      MatchPattern 'umask 007' $pattern | Should -Be $true
    }

    It 'should compare with negation' {
      # Arrange
      $pattern = '!n:audit=(\d+) compare == 1'

      # Assert
      MatchPattern 'audit=1' $pattern | Should -Be $false
      MatchPattern 'audit=0' $pattern | Should -Be $true

      # Arrange
      $pattern = '!n:audit_backlog_limit=(\d+) compare >= 8192'

      # Assert
      MatchPattern 'audit_backlog_limit=8192' $pattern | Should -Be $false
      MatchPattern 'audit_backlog_limit=8193' $pattern | Should -Be $false
      MatchPattern 'audit_backlog_limit=2048' $pattern | Should -Be $true
    }   
  }
}
