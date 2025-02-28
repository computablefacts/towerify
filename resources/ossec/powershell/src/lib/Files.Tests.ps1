BeforeAll {
  . "$PSScriptRoot/Files.ps1"
}

Describe 'Files library' {

  Describe 'DirectoryExists function' {

    BeforeEach {
      # Arrange
      New-Item -ItemType Directory -Path 'TestDrive:\existingDirectory' -Force
    }
  
    AfterEach {
      # Cleanup
      Remove-Item -Path 'TestDrive:\existingDirectory' -Recurse -Force -ErrorAction SilentlyContinue
    }
  
    It 'should return true if the directory exists' {
      # Act
      $result = DirectoryExists -directoryPath 'TestDrive:\existingDirectory'
  
      # Assert
      $result | Should -Be $true    
    }
  
    It 'should return false if the directory does not exist' {
      # Act
      $result = DirectoryExists -directoryPath 'TestDrive:\nonExistingDirectory'
  
      # Assert
      $result | Should -Be $false
    }
  }

  Describe 'FileExists function' {

    BeforeEach {
      # Arrange
      New-Item -ItemType Directory -Path 'TestDrive:\existingDirectory' -Force
      New-Item -ItemType File -Path 'TestDrive:\fileCreated.txt' -Force
    }
  
    AfterEach {
      # Cleanup
      Remove-Item -Path 'TestDrive:\fileCreated.txt' -Force -ErrorAction SilentlyContinue
    }
  
    It 'should return true if the file exists' {
      # Act
      $result = FileExists 'TestDrive:\fileCreated.txt'
  
      # Assert
      $result | Should -Be $true
    }
  
    It 'should return false if the file does not exist' {
      # Act
      $result = FileExists 'TestDrive:\unknowFile.txt'
  
      # Assert
      $result | Should -Be $false
    }
  }
    
  Describe 'ListFiles function' {

    Context 'When the directory contains files' {
      It 'Should return the list of files in the directory' {
        # Arrange
        $testPath = 'TestDrive:\ThreeFilesDirectory'
        $directory = New-Item -ItemType Directory -Path $testPath -Force
        $files = @('file1.txt', 'file2.txt', 'file3.txt')
        $files | ForEach-Object { 
          Set-Content -Path "$testPath\$_" -Value 'dummy'
        }
        $expectedFiles = ($files | ForEach-Object { "$directory/$_" })
  
        # Act
        $result = ListFiles -Path $testPath
  
        # Assert
        $result | Should -BeExactly $expectedFiles
  
        # Cleanup
        Remove-Item -Path $testPath -Recurse -Force -ErrorAction SilentlyContinue
      }
    }
  
    Context 'When the directory is empty' {
      It 'Should return an empty list' {
        # Arrange
        $testPath = 'TestDrive:\EmptyDirectory'
        New-Item -ItemType Directory -Path $testPath -Force
  
        # Act
        $result = ListFiles -Path $testPath
  
        # Assert
        $result | Should -BeExactly @()
      }
    }
  
    Context 'When the directory does not exist' {
      It 'Should throw an error' {
        # Arrange
        $testPath = 'TestDrive:\NonExistentDirectory'
  
        # Act & Assert
        { ListFiles -Path $testPath } | Should -Throw -ExceptionType ([System.Management.Automation.ItemNotFoundException])
      }
    }
  }

  Describe 'FetchFile function' {

    Context 'When the file exists' {
      It 'Should return an array with the lines of the file' {
        # Arrange
        $filePath = 'TestDrive:\testfile.txt'
        $expectedContent = "This is a test file`nwith multiple lines."
        Set-Content -Path $filePath -Value $expectedContent
  
        # Act
        $result = FetchFile -file $filePath
  
        # Assert
        $result | Should -HaveCount 2
        $result | Should -Be ($expectedContent -Split "`n")
  
        # Cleanup
        Remove-Item -Path $filePath
      }
    }
  
    Context 'When the file does not exist' {
      It 'Should throw an error' {
        # Arrange
        $filePath = 'TestDrive:\nonExistentFile.txt'
  
        # Act & Assert
        { FetchFile -file $filePath } | Should -Throw -ExceptionType ([System.Management.Automation.ItemNotFoundException])
      }
    }
  }
}
