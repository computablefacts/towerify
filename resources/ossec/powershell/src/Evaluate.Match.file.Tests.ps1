BeforeAll {
    . "$PSScriptRoot/Evaluate.ps1"
}
  
Describe 'Match function' {
  
    Context 'type file' {
        BeforeAll {
            function file_does_exist {
                param ([string]$filePath)
                $filePath -eq $expectedFilePath
            }
            function file_does_not_exist {
                param ([string]$filePath)
                $filePath -ne $expectedFilePath
            }
            function fetch_file {
                param ([string]$filePath)
                $expectedFileContent
            }
        }        

        It 'should return true if file exists' {
            # Arrange
            $expectedFilePath = 'file1.txt'
            $ctx = @{
                file_exists = { file_does_exist -file $args[0] }        
            }
            $rule = @{
                'match_type' = 'all'
                'rules'      = @(
                    @{ 
                        'negate' = $false
                        'type' = 'file'
                        'files' = @('file1.txt') 
                        'expr' = $null
                    }
                )
            }
    
            # Act
            $result = Evaluate $ctx $rule
    
            # Assert
            $result | Should -Be $true
        }

        It 'should return false if file does NOT exist' {
            # Arrange
            $expectedFilePath = 'file1.txt'
            $ctx = @{
                file_exists = { file_does_not_exist -file $args[0] }        
            }
            $rule = @{
                'match_type' = 'all'
                'rules'      = @(
                    @{ 
                        'negate' = $false
                        'type' = 'file'
                        'files' = @('file1.txt') 
                        'expr' = $null
                    }
                )
            }
    
            # Act
            $result = Evaluate $ctx $rule
    
            # Assert
            $result | Should -Be $false
        }

        It 'should return true if content match expression' {
            # Arrange
            $expectedFilePath = '/etc/hosts.deny'
            $expectedFileContent = 'ALL: ALL'
            $ctx = @{
                file_exists = { file_does_exist -file $args[0] }        
                fetch_file  = { fetch_file -file $args[0] }
            }
            $rule = @{
                'match_type' = 'all'
                'rules'      = @(
                    @{ 
                        'negate' = $false
                        'type' = 'file'
                        'files' = @('/etc/hosts.deny') 
                        'expr' = 'r:^ALL\s*:\s*ALL'
                    }
                )
            }
    
            # Act
            $result = Evaluate $ctx $rule
    
            # Assert
            $result | Should -Be $true
        }

        It 'should return false if content does NOT match expression' {
            # Arrange
            $expectedFilePath = '/etc/default/rsync'
            $expectedFileContent = 'RSYNC_ENABLE=true'
            $ctx = @{
                file_exists = { file_does_exist -file $args[0] }        
                fetch_file  = { fetch_file -file $args[0] }
            }
            # f:/etc/default/rsync -> !r:^# && r:RSYNC_ENABLE\s*\t*=\s*\t*false
            $rule = @{
                'match_type' = 'all'
                'rules'      = @(
                    @{ 
                        'negate' = $false
                        'type' = 'file'
                        'files' = @('/etc/default/rsync') 
                        'expr' = @('!r:^#', 'r:RSYNC_ENABLE\s*\t*=\s*\t*false')
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
