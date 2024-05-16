# Security Policy for Towerify

## Table of content

- [Purpose](#purpose)
- [Supported Versions](#supported-versions)
- [Reporting a Vulnerability](#reporting-a-vulnerability)
  - [What to Include](#what-to-include)
  - [Response Process](#response-process)
- [Security Measures](#security-measures)
  - [Code Review and Merging](#code-review-and-merging)
  - [Dependencies](#dependencies)
  - [Testing and CI/CD](#testing-and-cicd)
- [Disclosure Policy](#disclosure-policy)
- [Security Updates](#security-updates)
- [Contact Information](#contact-information)

## Purpose

This Security Policy outlines the measures and processes in place to ensure the security of Towerify and describes how security concerns can be reported by contributors and users. 

## Supported Versions

Only the latest major version of Towerify receives regular patches and updates for security issues. Versions older than [N-1] are considered unsupported and may not receive security updates.

| Version | Supported          |
|---------|--------------------|     
| 0.x     | :white_check_mark: |
| < 0.x   | :x:                |

## Reporting a Vulnerability

### How to Report a Vulnerability

- Security issues should be reported via email to <a href="mailto:engineering@computablefacts.com">engineering@computablefacts.com</a>.
- Please do not report security vulnerabilities through public GitHub issues.

### What to Include

- A clear and concise description of the problem.
- Steps to reproduce the issue or a proof of concept (if applicable).
- Any relevant screenshots or supporting information.

### Response Process

- Reports will be acknowledged within 72 hours.
- The security team will review the issue and determine severity.
- Updates or patches will be issued as necessary.
- Public disclosure dates will be negotiated with the reporter.

## Security Measures

### Code Review and Merging

All code submissions go through a peer review process to identify potential security issues before being merged into the main codebase.

### Dependencies

All third-party libraries and dependencies are regularly reviewed for security vulnerabilities.

### Testing and CI/CD

Automated security scans and testing are integrated into the Continuous Integration/Continuous Deployment (CI/CD) process.

## Disclosure Policy

To encourage responsible disclosure, we promise not to pursue legal action against individuals who report vulnerabilities in good faith and not exploit them.

We will make every effort to rectify the issue before any public disclosure, aiming to ensure that a fix is in place before the vulnerability is widely known.

## Security Updates

Updates about security issues will be posted below or distributed via our mailing list. Users are encouraged to update their installations promptly to benefit from security fixes.

| Version | Date | Reporter | Issue |
|---------|------|----------|-------|     
| 0.x     | n/a  | n/a      | n/a   | 

## Contact Information

For any security concerns, please contact <a href="mailto:engineering@computablefacts.com">engineering@computablefacts.com</a>.
