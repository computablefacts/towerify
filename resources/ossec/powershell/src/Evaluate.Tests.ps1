BeforeAll {
  . "$PSScriptRoot/Evaluate.ps1"
}

Describe 'Evaluate function' {

  Context 'match_type' {

    BeforeAll {
      function Match {
        param (
          [hashtable]$ctx,
          [hashtable]$rule
        )
        return $false
      }
    }

    Context 'all' {
      It 'should return true when all rules are true' {
        # Arrange
        $ctx = @{}
        $rule = @{
          'match_type' = 'all'
          'rules'      = @(
            @{ 'negate' = $true },
            @{ 'negate' = $true },
            @{ 'negate' = $true }
          )
        }
  
        # Act
        $result = Evaluate $ctx $rule
  
        # Assert
        $result | Should -Be $true
      }
  
      It 'should return false when one rule is false' {
        # Arrange
        $ctx = @{}
        $rule = @{
          'match_type' = 'all'
          'rules'      = @(
            @{ 'negate' = $true },
            @{ 'negate' = $false },
            @{ 'negate' = $true }
          )
        }
  
        # Act
        $result = Evaluate $ctx $rule
  
        # Assert
        $result | Should -Be $false
      }  
    }

    Context 'any' {
      It 'should return true when on rule is true' {
        # Arrange
        $ctx = @{}
        $rule = @{
          'match_type' = 'any'
          'rules'      = @(
            @{ 'negate' = $false },
            @{ 'negate' = $false },
            @{ 'negate' = $true }
          )
        }
  
        # Act
        $result = Evaluate $ctx $rule
  
        # Assert
        $result | Should -Be $true
      }
  
      It 'should return false when all rules are false' {
        # Arrange
        $ctx = @{}
        $rule = @{
          'match_type' = 'any'
          'rules'      = @(
            @{ 'negate' = $false },
            @{ 'negate' = $false },
            @{ 'negate' = $false }
          )
        }
  
        # Act
        $result = Evaluate $ctx $rule
  
        # Assert
        $result | Should -Be $false
      }  
    }

    Context 'none' {
      It 'should return true when all rules are false' {
        # Arrange
        $ctx = @{}
        $rule = @{
          'match_type' = 'none'
          'rules'      = @(
            @{ 'negate' = $false },
            @{ 'negate' = $false },
            @{ 'negate' = $false }
          )
        }
  
        # Act
        $result = Evaluate $ctx $rule
  
        # Assert
        $result | Should -Be $true
      }
  
      It 'should return false when one rule is true' {
        # Arrange
        $ctx = @{}
        $rule = @{
          'match_type' = 'none'
          'rules'      = @(
            @{ 'negate' = $true },
            @{ 'negate' = $false },
            @{ 'negate' = $false }
          )
        }
  
        # Act
        $result = Evaluate $ctx $rule
  
        # Assert
        $result | Should -Be $false
      }  
    }
  }
}

Describe 'Match function' {
  Context 'Unknown type' {
    It 'should return false if type is unknown' {
      # Arrange
      $ctx = @{}
      $rule = @{
        'match_type' = 'all'
        'rules'      = @(
          @{ 
            'negate' = $false
            'type'   = 'inconnu'
          }
        )
      }

      # Act
      $result = Evaluate $ctx $rule

      # Assert
      $result | Should -Be $false
    }
  }
}

Describe 'MatchPattern function' {
  Context 'Unknown compare operator' {
    It 'should return false if type is unknown' {
      # Arrange
      $pattern = 'n:^\s*clientalivecountmax\s*\t*(\d+) compare === 0'

      # Assert
      MatchPattern 'clientalivecountmax 0' $pattern | Should -Be $false
    }
  }
}
