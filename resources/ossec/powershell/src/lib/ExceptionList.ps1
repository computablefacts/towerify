# Define a global variable to store the exceptions list
$global:ExceptionList = @()

# Function to add an exception and a message in the list
function Add-Exception {
  param (
    [Parameter(Mandatory = $true)]
    [System.Exception]$Exception,
    [Parameter(Mandatory = $true)]
    [string]$Message
  )
  $global:ExceptionList += [PSCustomObject]@{
    Exception = $Exception
    Message   = $Message
  }
}

# Function to get the exceptions list
function Get-ExceptionList {
  return $global:ExceptionList
}

# Function to remove all exceptions from the list
function Clear-ExceptionList {
  $global:ExceptionList = @()
}
