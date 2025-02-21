BeforeAll {
    . "$PSScriptRoot/Evaluate.ps1"
}
  
Describe 'Match function' {
  
    Context 'type directory' {
        BeforeAll {
            function directory_does_exist {
                param ([string]$directoryPath)
                $directoryPath -eq $expectedDirectoryPath
            }
            function directory_does_not_exist {
                param ([string]$directoryPath)
                $directoryPath -ne $expectedDirectoryPath
            }
            function list_files {
                param ([string]$Path)
                if ($path -eq $expectedDirectoryPath) {
                    return $expectedListOfFiles
                }
                else {
                    return @()
                }
            }
            function fetch_file {
                param ([string]$file)
                if ($file -eq $expectedFilePath) {
                    return $expectedFileContent
                }
                else {
                    return 'File content if file path is not expected'
                }
            }
        }        

        It 'should return true if directory exists' {
            # Arrange
            $expectedDirectoryPath = '/etc/audit'
            $ctx = @{
                directory_exists = { directory_does_exist -directoryPath $args[0] }        
            }
            # d:/etc/audit
            $rule = @{
                'match_type' = 'all'
                'rules'      = @(
                    @{ 
                        'negate'      = $false
                        'type'        = 'directory'
                        'directories' = @('/etc/audit') 
                        'files'       = $null
                        'expr'        = $null
                    }
                )
            }
    
            # Act
            $result = Evaluate $ctx $rule
    
            # Assert
            $result | Should -Be $true

            # Avoid warning: "The variable 'xxx' is assigned but never used."
            $expectedDirectoryPath | Out-Null
        }

        It 'should return false if directory does NOT exist' {
            # Arrange
            $expectedDirectoryPath = '/etc/audit'
            $ctx = @{
                directory_exists = { directory_does_not_exist -directoryPath $args[0] }        
            }
            # d:/etc/audit
            $rule = @{
                'match_type' = 'all'
                'rules'      = @(
                    @{ 
                        'negate'      = $false
                        'type'        = 'directory'
                        'directories' = @('/etc/audit') 
                        'files'       = $null
                        'expr'        = $null
                    }
                )
            }
    
            # Act
            $result = Evaluate $ctx $rule
    
            # Assert
            $result | Should -Be $false

            # Avoid warning: "The variable 'xxx' is assigned but never used."
            $expectedDirectoryPath | Out-Null
        }

        It 'should return true if at least one file name matches expression' {
            # Arrange
            $expectedDirectoryPath = '/etc/profile.d'
            $expectedListOfFiles = @('file1.sh', 'file2.backup')
            $ctx = @{
                directory_exists = { directory_does_exist -directoryPath $args[0] }
                list_files       = { list_files -Path $args[0] }
            }
            # d:/etc/profile.d -> .sh
            $rule = @{
                'match_type' = 'all'
                'rules'      = @(
                    @{ 
                        'negate'      = $false
                        'type'        = 'directory'
                        'directories' = @('/etc/profile.d')
                        'files'       = @('r:.sh')
                        'expr'        = $null
                    }
                )
            }
    
            # Act
            $result = Evaluate $ctx $rule
    
            # Assert
            $result | Should -Be $true

            # Avoid warning: "The variable 'xxx' is assigned but never used."
            $expectedDirectoryPath | Out-Null
            $expectedListOfFiles | Out-Null
        }

        It 'should return true if at least one file content matches expression' {
            # Arrange
            $expectedDirectoryPath = '/etc/profile.d'
            $expectedListOfFiles = @('file1.conf', 'file2.conf')
            $expectedFilePath = 'file1.conf'
            $expectedFileContent = 'Defaults use_pty'
            $ctx = @{
                directory_exists = { directory_does_exist -directoryPath $args[0] }
                list_files       = { list_files -Path $args[0] }
                fetch_file       = { fetch_file -file $args[0] }
            }
            # d:/etc/sudoers.d -> r:\.* -> r:^\s*\t*Defaults\s*\t*use_pty;
            $rule = @{
                'match_type' = 'all'
                'rules'      = @(
                    @{ 
                        'negate'      = $false
                        'type'        = 'directory'
                        'directories' = @('/etc/profile.d')
                        'files'       = @('r:\.*')
                        'expr'        = 'r:^\s*\t*Defaults\s*\t*use_pty'
                    }
                )
            }
    
            # Act
            $result = Evaluate $ctx $rule
    
            # Assert
            $result | Should -Be $true

            # Avoid warning: "The variable 'xxx' is assigned but never used."
            $expectedDirectoryPath | Out-Null
            $expectedListOfFiles | Out-Null
            $expectedFilePath | Out-Null
            $expectedFileContent | Out-Null
        }
    }
}
