# Grin Module <!-- omit from toc -->

Influencer marketing for ecommerce. For more information go to https://grin.co/

## Table of Contents <!-- omit from toc -->

- [Install](#install)
- [Update](#update)
- [Deploy to Staging via GitHub Actions](#deploy-to-staging-via-github-actions)
- [Some notes](#some-notes)
- [Q\&A](#qa)
- [Local Development](#local-development)
  - [Prerequisites](#prerequisites)
  - [Port Configuration](#port-configuration)
  - [Getting Required Credentials](#getting-required-credentials)
  - [Setup Steps](#setup-steps)
  - [Development Workflow](#development-workflow)
  - [Installing Sample Data](#installing-sample-data)
  - [After Code Changes](#after-code-changes)
  - [Troubleshooting](#troubleshooting)
  - [Running Unit Tests](#running-unit-tests)
  - [Running Coding Standards](#running-coding-standards)
  - [Cleanup](#cleanup)
  - [Credits](#credits)

## Install

1. composer config repositories.grin vcs https://github.com/grininc/grin-magento-module
2. composer require grin/module
3. bin/magento setup:upgrade
4. bin/magento setup:di:compile
5. bin/magento setup:static-content:deploy
6. bin/magento cache:flush

## Update

1. composer update grin/module
2. bin/magento setup:upgrade
3. bin/magento setup:di:compile
4. bin/magento setup:static-content:deploy
5. bin/magento cache:flush

## Deploy to Staging via GitHub Actions

This repository includes a GitHub Actions workflow (.github/workflows/deploy-module-staging.yml) that automates deployment of the latest code from the main branch to staging by connecting via SSH using stored credentials, updating the module via Composer, and running required Magento deployment commands.

## Some notes

- Grin uses composer to provide their extension to the end customer.
- The extension follows semver principles.
- Once the new implementation of Grin_Affiliate is ready to be installed and tested it will get version 2.0.x.
- If an end-user has the previous version of the extension installed via https://docs.magento.com/user-guide/v2.3/system/web-setup-wizard.html or manually in app/code, the extension must be removed before new installation.

## Q&A

1. Issue: I updated the extension, but it seems like the old version is installed despite the fact that in the compose.lock file I see the updated version of the extension.

Possible solution: the extension uses a DB queue, which is run in shadow mode. So it might be that you did not kill the old process after the installation.
Try to execute `ps -aux | grep grin_module_webhook` and kill the process if any.

2. Issue: I am using Magento cloud instance and it seems like the 'webhook_grin_module' is not working.

Possible solution: in this case, according to Magento documentation, you need to make sure that the queue job is added into the /app/etc/env.php file like so:
```
...
    'cron_consumers_runner' => [
        'cron_run' => false,
        'max_messages' => 20000,
        'consumers' => [
            'consumer1',
            'consumer2',
            'grin_module_webhook',
            ...
        ]
    ],
...
```

## Local Development

This module uses Docker for local development. Follow these steps to set up your development environment:

### Prerequisites

- Docker
- Docker Compose
- PHP 8.1 or higher
- Composer

### Port Configuration

The development environment uses custom ports to avoid conflicts with other services:

| Service | Default Port | Development Port |
|---------|--------------|------------------|
| Web Server | 80 | 8081 |
| HTTPS | 443 | 8443 |
| MySQL | 3306 | 3307 |
| Redis | 6379 | 6380 |
| OpenSearch | 9200 | 9201 |
| RabbitMQ | 5672 | 5673 |
| Mailcatcher | 1025 | 1026 |

These ports are automatically configured during setup to avoid conflicts with other services like `app.grin.co`.

### Getting Required Credentials

1. **Magento Marketplace Credentials**:
   - Go to [Magento Marketplace Access Keys](https://marketplace.magento.com/customer/accessKeys/)
   - Create a new access key if you don't have one
   - Note down your Public Key and Private Key

2. **GitHub Token**:
   - Go to [GitHub Personal Access Tokens](https://github.com/settings/personal-access-tokens)
   - Create a new token with `repo` scope
   - Note down your token

### Setup Steps

1. Copy the auth template file:
   ```bash
   cp scripts/magento-auth.template scripts/magento-auth.env
   ```

2. Edit `scripts/magento-auth.env` and add your credentials:
   ```
   MAGENTO_MARKETPLACE_PUBLIC_KEY=your_public_key
   MAGENTO_MARKETPLACE_PRIVATE_KEY=your_private_key
   GITHUB_TOKEN=your_github_token
   ```

3. **First-time setup only**: Run the setup script:
   ```bash
   ./scripts/setup-first-time.sh
   ```
   This script will:
   - Set up a local Magento installation using Docker
   - Configure the necessary ports and hosts
   - Mount your module code into the Magento installation
   - Enable and configure the module
   - Start the Docker containers

   > **Note**: This script should only be run once during the initial setup. It already starts the containers, so there's no need to run `start-env.sh` after it.

4. **For subsequent development sessions**: Start the development environment:
   ```bash
   ./scripts/start-env.sh
   ```

### Development Workflow

- The module code is mounted into the Magento installation, so any changes you make to the code will be immediately reflected
- The Magento installation is available at `https://magento.grin.co.test:8443`
- Admin panel is available at `https://magento.grin.co.test:8443/admin`
  - Username: `admin`
  - Password: `admin123`

### Installing Sample Data

To populate your Magento store with sample products, categories, and other content, you can use the provided script:

```bash
./scripts/install-sample-data.sh
```

This script will:
1. Set Magento to developer mode
2. Install the sample data
3. Run setup upgrade
4. Clean and flush the cache

After running this script, you'll have:
- Sample products to test with
- Categories
- Customer accounts
- Sales rules (including coupon codes)
- And more

This makes it easier to test functionality like coupon codes and other features that require store content.

### After Code Changes

When you modify plugin code or other PHP files:
1. Clear the Magento cache:
   ```bash
   cd .docker-magento
   bin/clinotty bin/magento cache:clean
   bin/clinotty bin/magento cache:flush
   ```
2. Refresh your browser page

> **Note**: In developer mode, you don't need to run `setup:di:compile` after every change. However, if you're in production mode, you would need to run it.

### Troubleshooting

If you encounter any issues:

1. Check if the module is properly enabled:
   ```bash
   cd .docker-magento
   bin/clinotty bin/magento module:status Grin_Module
   ```

2. If the module is disabled, try re-enabling it:
   ```bash
   cd .docker-magento
   bin/clinotty bin/magento module:enable Grin_Module --force
   bin/clinotty bin/magento setup:upgrade
   bin/clinotty bin/magento cache:flush
   ```

3. Check the Magento logs in the container:
   ```bash
   cd .docker-magento
   bin/clinotty tail -f var/log/system.log
   ```

### Running Unit Tests

To run unit tests for this module, use the provided script:

```bash
./scripts/run-tests.sh
```

- This will run all tests in the `Test` directory.

To run a specific test file, provide the relative path from the module root:

```bash
./scripts/run-tests.sh Test/Unit/Plugin/Magento/SalesRule/Model/RulesApplier/ValidateByTokenTest.php
```

The script will automatically execute the tests inside the Docker container using the correct environment.

### Running Coding Standards

To check your code against Magento's coding standards, use the provided script:

```bash
./scripts/run-coding-standard.sh
```

- This will check all files in the module against Magento's coding standards.
- Generated code and Docker-related files are automatically ignored.

To check a specific file, provide the relative path from the module root:

```bash
./scripts/run-coding-standard.sh Test/Unit/Plugin/Magento/SalesRule/Model/RulesApplier/ValidateByTokenTest.php
```

The script will:
1. Run the coding standard check inside the Docker container
2. Generate a detailed report
3. Display any warnings or errors found

### Cleanup

If you want to completely remove your local Magento development environment, follow these steps:

1. **Stop and remove Docker containers and volumes:**
   ```bash
   docker compose -f $HOME/.docker-magento/compose.yaml down -v
   ```
   This will stop and remove all containers and associated Docker volumes for your Magento environment.

2. **Remove the Docker Magento workspace directory:**
   ```bash
   rm -rf $HOME/.docker-magento
   ```
   This will delete all files related to the local Docker Magento environment, including configuration, data, and any persistent files.

> **Note:** This process will remove your local Magento instance, database, and all related data. Only do this if you are sure you want a full reset.

### Credits

This local development setup is based on [markshust/docker-magento](https://github.com/markshust/docker-magento).
