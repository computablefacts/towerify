BeforeAll {
    . "$PSScriptRoot/Evaluate.ps1"
}
  
Describe 'Match function' {

    Context 'type registry' {
        BeforeAll {
            function registry_entry_does_exist {
                param($entry) 
                $entry -eq $expectedEntry
            }
            function registry_entry_does_not_exist {
                param($entry) 
                $entry -ne $expectedEntry
            }
            function fetch_registry_keys_return_expected_key {
                param($entry) 
                if ($entry -eq $expectedEntry) {
                    @($expectedKey, 'AnOtherKey') 
                }
                else { 
                    @('AnOtherKey') 
                }
            }
            function fetch_registry_keys_does_not_return_expected_key {
                param($entry) 
                @('AnOtherKey')
            }
            function fetch_registry_value_return_expected_value {
                param($entry, $key) 
                if ($entry -eq $expectedEntry -and $key -eq $expectedKey) {
                    $expectedReturnValue 
                }
                else { 
                    $wrongReturnValue 
                }
            }
            function fetch_registry_value_return_wrong_value {
                param($entry, $key) 
                if ($entry -eq $expectedEntry -and $key -eq $expectedKey) {
                    $wrongReturnValue
                }
                else { 
                    $expectedReturnValue 
                }
            }
        }
      
        It 'should have correct mock functions' {
            # Arrange
            $expectedEntry = 'HKEY_LOCAL_MACHINE\System\CurrentControlSet\Control\Lsa'
            $expectedKey = 'LimitBlankPasswordUse'
            $expectedReturnValue = 1
            $wrongReturnValue = 999
      
            # Assert
            registry_entry_does_exist $expectedEntry | Should -Be $true
            registry_entry_does_exist 'AnOtherEntry' | Should -Be $false
      
            registry_entry_does_not_exist $expectedEntry | Should -Be $false
            registry_entry_does_not_exist 'AnOtherEntry' | Should -Be $true
      
            fetch_registry_keys_return_expected_key $expectedEntry | Should -Contain $expectedKey
            fetch_registry_keys_return_expected_key 'AnOtherEntry' | Should -Not -Contain $expectedKey
      
            fetch_registry_keys_does_not_return_expected_key $expectedEntry | Should -Not -Contain $expectedKey
            fetch_registry_keys_does_not_return_expected_key 'AnOtherEntry' | Should -Not -Contain $expectedKey
      
            fetch_registry_value_return_expected_value $expectedEntry $expectedKey | Should -Be $expectedReturnValue
            fetch_registry_value_return_expected_value 'AnOtherEntry' $expectedKey | Should -Be $wrongReturnValue
            fetch_registry_value_return_expected_value $expectedEntry 'AnOtherKey' | Should -Be $wrongReturnValue
            fetch_registry_value_return_expected_value 'AnOtherEntry' 'AnOtherKey' | Should -Be $wrongReturnValue
      
            fetch_registry_value_return_wrong_value $expectedEntry $expectedKey | Should -Be $wrongReturnValue
            fetch_registry_value_return_wrong_value 'AnOtherEntry' $expectedKey | Should -Be $expectedReturnValue
            fetch_registry_value_return_wrong_value $expectedEntry 'AnOtherKey' | Should -Be $expectedReturnValue
            fetch_registry_value_return_wrong_value 'AnOtherEntry' 'AnOtherKey' | Should -Be $expectedReturnValue
        }

        Context 'r:HKEY_LOCAL_MACHINE\Software\Microsoft\Windows\CurrentVersion\Policies\System' {
            BeforeAll {
                $expectedEntry = 'HKEY_LOCAL_MACHINE\Software\Microsoft\Windows\CurrentVersion\Policies\System'
                
                $rule = @{
                    'match_type' = 'all'
                    'rules'      = @(
                        @{ 
                            'negate' = $false
                            'type'   = 'registry'
                            'entry'  = 'HKEY_LOCAL_MACHINE\Software\Microsoft\Windows\CurrentVersion\Policies\System'
                            'key'    = $null
                            'expr'   = $null
                        }
                    )
                }
                # Avoid warning: "The variable 'xxx' is assigned but never used."
                $expectedEntry | Out-Null
                $rule | Out-Null
            }

            It 'should return false when registry entry does NOT exist' {
                # Arrange
                $ctx = @{
                    'registry_entry_exists' = { registry_entry_does_not_exist -entry $args[0] }
                }
      
                # Act
                $result = Evaluate $ctx $rule
      
                # Assert
                $result | Should -Be $false
            }

            It 'should return true when registry entry exists' {
                # Arrange
                $ctx = @{
                    'registry_entry_exists' = { registry_entry_does_exist -entry $args[0] }
                }
      
                # Act
                $result = Evaluate $ctx $rule
      
                # Assert
                $result | Should -Be $true
            }
        }

        Context 'r:HKEY_LOCAL_MACHINE\SOFTWARE\Policies\Microsoft\Windows\System -> AllowCrossDeviceClipboard' {
            BeforeAll {
                $expectedEntry = 'HKEY_LOCAL_MACHINE\SOFTWARE\Policies\Microsoft\Windows\System'
                $expectedKey = 'AllowCrossDeviceClipboard'
                
                $rule = @{
                    'match_type' = 'all'
                    'rules'      = @(
                        @{ 
                            'negate' = $false
                            'type'   = 'registry'
                            'entry'  = 'HKEY_LOCAL_MACHINE\SOFTWARE\Policies\Microsoft\Windows\System'
                            'key'    = 'AllowCrossDeviceClipboard'
                            'expr'   = $null
                        }
                    )
                }
                # Avoid warning: "The variable 'xxx' is assigned but never used."
                $expectedEntry | Out-Null
                $expectedKey | Out-Null
                $rule | Out-Null
            }
            
            It 'should return false when registry entry does NOT exist' {
                # Arrange
                $ctx = @{
                    'registry_entry_exists' = { registry_entry_does_not_exist -entry $args[0] }
                    # 'fetch_registry_keys'   = { fetch_registry_keys_return_expected_key -entry $args[0] }
                }
      
                # Act
                $result = Evaluate $ctx $rule
      
                # Assert
                $result | Should -Be $false
            }

            It 'should return false when registry key does NOT exist' {
                # Arrange
                $ctx = @{
                    'registry_entry_exists' = { registry_entry_does_exist -entry $args[0] }
                    'fetch_registry_keys'   = { fetch_registry_keys_does_not_return_expected_key -entry $args[0] }
                }
      
                # Act
                $result = Evaluate $ctx $rule
      
                # Assert
                $result | Should -Be $false
            }

            It 'should return true when registry entry exists and key exists' {
                # Arrange
                $ctx = @{
                    'registry_entry_exists' = { registry_entry_does_exist -entry $args[0] }
                    'fetch_registry_keys'   = { fetch_registry_keys_return_expected_key -entry $args[0] }
                }
      
                # Act
                $result = Evaluate $ctx $rule
      
                # Assert
                $result | Should -Be $true
            }
        }

        Context 'Rule r:HKEY_LOCAL_MACHINE\System\CurrentControlSet\Control\Lsa -> LimitBlankPasswordUse -> 1' {
            BeforeAll {
                $expectedEntry = 'HKEY_LOCAL_MACHINE\System\CurrentControlSet\Control\Lsa'
                $expectedKey = 'LimitBlankPasswordUse'
                $expectedReturnValue = 1
                $wrongReturnValue = 999
                
                $rule = @{
                    'match_type' = 'all'
                    'rules'      = @(
                        @{ 
                            'negate' = $false
                            'type'   = 'registry'
                            'entry'  = 'HKEY_LOCAL_MACHINE\System\CurrentControlSet\Control\Lsa'
                            'key'    = 'LimitBlankPasswordUse'
                            'expr'   = @('1')
                        }
                    )
                }
                # Avoid warning: "The variable 'xxx' is assigned but never used."
                $expectedEntry | Out-Null
                $expectedKey | Out-Null
                $expectedReturnValue | Out-Null
                $wrongReturnValue | Out-Null
                $rule | Out-Null
            }

            It 'should return false when registry entry does NOT exist' {
                # Arrange
                $ctx = @{
                    'registry_entry_exists' = { registry_entry_does_not_exist -entry $args[0] }
                    # 'fetch_registry_keys'   = { fetch_registry_keys_does_not_return_expected_key -entry $args[0] }
                }
      
                # Act
                $result = Evaluate $ctx $rule
      
                # Assert
                $result | Should -Be $false
            }

            It 'should return false when registry key does NOT exist' {
                # Arrange
                $ctx = @{
                    'registry_entry_exists' = { registry_entry_does_exist -entry $args[0] }
                    'fetch_registry_keys'   = { fetch_registry_keys_does_not_return_expected_key -entry $args[0] }
                }
      
                # Act
                $result = Evaluate $ctx $rule
      
                # Assert
                $result | Should -Be $false
            }

            It 'should return false when value does NOT match' {
                # Arrange
                $ctx = @{
                    'registry_entry_exists' = { registry_entry_does_exist -entry $args[0] }
                    'fetch_registry_keys'   = { fetch_registry_keys_return_expected_key -entry $args[0] }
                    'fetch_registry_value'  = { fetch_registry_value_return_wrong_value -entry $args[0] -key $args[1] }
                }
      
                # Act
                $result = Evaluate $ctx $rule
      
                # Assert
                $result | Should -Be $false
            }

            It 'should return true when registry entry exist, key exist and value is correct' {
                # Arrange
                $ctx = @{
                    'registry_entry_exists' = { registry_entry_does_exist -entry $args[0] }
                    'fetch_registry_keys'   = { fetch_registry_keys_return_expected_key -entry $args[0] }
                    'fetch_registry_value'  = { fetch_registry_value_return_expected_value -entry $args[0] -key $args[1] }
                }
      
                # Act
                $result = Evaluate $ctx $rule
      
                # Assert
                $result | Should -Be $true
            }
        }
    }
}
