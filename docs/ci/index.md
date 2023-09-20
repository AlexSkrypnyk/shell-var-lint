---
title: CI
layout: default
has_children: true
nav_order: 5
---

# CI

Continuous Integration (CI) is a software development practice where developers
regularly merge their code changes into a central repository. After a merge,
automated builds and tests are run to catch bugs early and to validate that the
new changes integrate well with the existing codebase. This practice encourages
frequent code updates, enhances code quality, and enables early detection of
issues. It's an essential part of modern DevOps processes and serves as a
foundation for Continuous Deployment and Continuous Delivery.

Read more about CI:

- [Wikipedia](https://en.wikipedia.org/wiki/Continuous_integration).
- [GitHub](https://resources.github.com/ci-cd/).
- [Atlassian](https://www.atlassian.com/continuous-delivery/continuous-integration).

## GitHub Actions

GitHub Actions is a separate but closely related service provided by GitHub to
implement CI/CD workflows. You can define custom workflows for building,
testing, and deploying your code directly within your GitHub repository. These
workflows are written in YAML files and can be triggered by various GitHub
events like pushes, pull requests, or scheduled events.

Workflows are defined using YAML files that you place in aa `.github/workflows`
directory in your repository.

In essence, GitHub Actions help you automate testing, building, and deploying
your code right from your GitHub repository, without the need for external
tools. This enables you to catch issues faster and streamline your development
process.

This project provides several GitHub Actions workflows to help you get started.
