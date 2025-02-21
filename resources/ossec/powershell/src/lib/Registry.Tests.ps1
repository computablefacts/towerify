BeforeAll {
  . "$PSScriptRoot/Registry.ps1"
}

Describe 'Registry library' {
  Describe 'Convert-RegistryKey function' {
    It 'should convert HKEY_LOCAL_MACHINE to HKLM' {
      # Arrange
      $key = 'HKEY_LOCAL_MACHINE\Software\Microsoft'
      $expected = 'HKLM:\Software\Microsoft'

      # Act
      $result = Convert-RegistryKey -Key $key

      # Assert
      $result | Should -Be $expected
    }

    It 'should convert HKEY_CURRENT_USER to HKCU' {
      # Arrange
      $key = 'HKEY_CURRENT_USER\Software\Microsoft'
      $expected = 'HKCU:\Software\Microsoft'

      # Act
      $result = Convert-RegistryKey -Key $key

      # Assert
      $result | Should -Be $expected
    }

    It 'should convert HKEY_CLASSES_ROOT to HKCR' {
      # Arrange
      $key = 'HKEY_CLASSES_ROOT\.txt'
      $expected = 'HKCR:\.txt'

      # Act
      $result = Convert-RegistryKey -Key $key

      # Assert
      $result | Should -Be $expected
    }

    It 'should convert HKEY_USERS to HKU' {
      # Arrange
      $key = 'HKEY_USERS\S-1-5-21-1234567890-123456789-1234567890-1001'
      $expected = 'HKU:\S-1-5-21-1234567890-123456789-1234567890-1001'

      # Act
      $result = Convert-RegistryKey -Key $key

      # Assert
      $result | Should -Be $expected
    }

    It 'should convert HKEY_CURRENT_CONFIG to HKCC' {
      # Arrange
      $key = 'HKEY_CURRENT_CONFIG\Software'
      $expected = 'HKCC:\Software'

      # Act
      $result = Convert-RegistryKey -Key $key

      # Assert
      $result | Should -Be $expected
    }

    It 'should return the key unchanged if no replacement is found' {
      # Arrange
      $key = 'HKEY_UNKNOWN\Software'
      $expected = 'HKEY_UNKNOWN\Software'

      # Act
      $result = Convert-RegistryKey -Key $key

      # Assert
      $result | Should -Be $expected
    }
  }

  Describe 'RegistryEntryExists function' {

    BeforeAll {
      function MockTestPath {
        param (
          [string]$myPath,
          [bool]$result = $true
        )
    
        # Stocker les valeurs dans des variables script pour les avoir dans le Mock
        $script:savedMyPath = $myPath
        $script:savedResult = $result
    
        Mock Test-Path {
          param ($Path)
          if ($Path -eq $script:savedMyPath) {
            return $script:savedResult
          }
          else {
            throw 'Mock Test-Path: Invalid parameters'
          }
        }
      }
    }
  
    It 'should return true if the registry entry exists' {
      # Arrange
      $key = 'TestRegistry:\TestLocation\TestKey'
      $expected = $true
      if (-not $IsWindows) {
        MockTestPath -myPath $key -result $expected
      } else {
        New-Item -Path $key -Force | Out-Null
      }
  
      # Act
      $result = RegistryEntryExists $key
      
      # Assert
      $result | Should -Be $expected
    }
  
    It 'should return false if the registry entry does not exist' {
      # Arrange
      $key = 'TestRegistry:\UnknownLocation\UnknownKey'
      $expected = $false
      if (-not $IsWindows) {
        MockTestPath -myPath $key -result $expected
      }
  
      # Act
      $result = RegistryEntryExists $key
  
      # Assert
      $result | Should -Be $expected
    }
  }  

  Describe 'FetchRegistryKeys function' {
  
    BeforeAll {
      function MockGetItem {
        param (
          [string]$myPath,
          [object]$result
        )
    
        # Stocker les valeurs dans des variables script pour les avoir dans le Mock
        $script:savedMyPath = $myPath
        $script:savedResult = $result
    
        Mock Get-Item {
          param ($Path)
          if ($Path -eq $script:savedMyPath) {
            return $script:savedResult
          }
          else {
            throw 'Mock Get-Item: Invalid parameters'
          }
        }
      }
    }
    
    Context 'When entry contains keys' {
      It 'should return all keys' {
        # Arrange
        $entry = 'TestRegistry:\ThreeKeysEntry'
        $expectedKeys = @('key1', 'key2', 'key3')
        if (-not $IsWindows) {
          MockGetItem -myPath $entry -result ([PSCustomObject]@{Property = $expectedKeys })
        } else {
          New-Item -Path $entry -Force | Out-Null
          foreach ($key in $expectedKeys) {
            New-ItemProperty -Path $entry -Name $key -Value 'TestValue' -PropertyType String -Force | Out-Null
          }
        }
      
        # Act
        $result = FetchRegistryKeys -Entry $entry
    
        # Assert
        $result | Should -Be $expectedKeys
      }
    }
  
    Context 'When entry has no key' {
      It 'should return an empty array' {
        # Arrange
        $entry = 'TestRegistry:\EmptyEntry'
        $expectedKeys = @()
        if (-not $IsWindows) {
          MockGetItem -myPath $entry -result ([PSCustomObject]@{Property = $expectedKeys })
        } else {
          New-Item -Path $entry -Force | Out-Null
        }
      
        # Act
        $result = FetchRegistryKeys -Entry $entry
    
        # Assert
        $result | Should -Be $expectedKeys
      }
    }
  
    Context 'When entry does not exist' {
      It 'Should throw an error' {
        # Arrange
        $entry = 'TestRegistry:\NonExistentEntry'
  
        # Act & Assert
        { FetchRegistryKeys -Entry $entry } | Should -Throw
      }
    }
  }

  Describe 'FetchRegistryValue function' {

    BeforeAll {
      function MockGetItemPropertyValue {
        param (
          [string]$myPath,
          [string]$myName,
          [object]$result
        )
    
        # Stocker les valeurs dans des variables script pour les avoir dans le Mock
        $script:savedMyPath = $myPath
        $script:savedMyName = $myName
        $script:savedResult = $result
    
        Mock Get-ItemPropertyValue {
          param ($Path, $Name)
          if ($Path -eq $script:savedMyPath -and $Name -eq $script:savedMyName) {
            return $script:savedResult
          }
          else {
            throw 'Mock Get-ItemPropertyValue: Invalid parameters'
          }
        }
      }
    }

    Context 'When the registry entry and property exist' {
      It 'should return the property value' {
        # Arrange
        $entry = 'TestRegistry:\TestLocation\TestKey'
        $propertyName = 'TestProperty'
        $expectedValue = 'TestValue'
        if (-not $IsWindows) {
          MockGetItemPropertyValue -myPath $entry -myName $propertyName -result $expectedValue
        } else {
          New-Item -Path $entry -Force | Out-Null
          New-ItemProperty -Path $entry -Name $propertyName -Value $expectedValue -PropertyType String -Force | Out-Null
        }
      
        # Act
        $result = FetchRegistryValue -Entry $entry -PropertyName $propertyName
    
        # Assert
        $result | Should -Be $expectedValue
      }
    }

    Context 'When the registry entry exists but the property does not' {
      It 'should throw an error' {
        # Arrange
        $entry = 'TestRegistry:\TestLocation\TestKey'
        $propertyName = 'NonExistentProperty'
      
        # Act & Assert
        { FetchRegistryValue -Entry $entry -PropertyName $propertyName } | Should -Throw
      }
    }

    Context 'When the registry entry does not exist' {
      It 'should throw an error' {
        # Arrange
        $entry = 'TestRegistry:\NonExistentEntry'
        $propertyName = 'TestProperty'
      
        # Act & Assert
        { FetchRegistryValue -Entry $entry -PropertyName $propertyName } | Should -Throw
      }
    }
  }
}
