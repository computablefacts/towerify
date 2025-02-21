# Test OSSEC Security Rules

## Description

A PowerShell script to check OSSEC Rules under Windows.

## Usage

Rules must be in Cywise JSON format and put in a JSONL file (one rule per line).
Then launch the command:

`.\src\Test-OssecRules.ps1 -RulesFile .\examples.rules.jsonl`

## Build

To have a single script to distribute, use the build command:

`./Build.ps1`

The resulting script will be `./build/Test-OssecRules.ps1`

## Development

Install Pester with the command:

`Install-Module -Name Pester -Force`

Then you can run the tests with: 

`./RunTests.ps1`

If you want to have the Code Coverage analysis, just add `-Coverage`:

`./RunTests.ps1 -Coverage`
