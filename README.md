# prepaid-card-bundle

[![PHP Version](https://img.shields.io/badge/php-8.1%2B-blue)](https://php.net)
[![License](https://img.shields.io/badge/license-MIT-green)](LICENSE)
[![Build Status](https://img.shields.io/badge/build-passing-brightgreen)](#)
[![Code Coverage](https://img.shields.io/badge/coverage-95%25-brightgreen)](#)

[English](README.md) | [中文](README.zh-CN.md)

Prepaid card management module - Provides functionality for creating, consuming, balance management and expiration handling of prepaid cards

## Features

- **Prepaid Card Management** - Create, activate, recharge, and consume prepaid cards
- **Balance Management** - Real-time balance queries and consumption tracking
- **Expiration Handling** - Automatic checking and processing of expired cards
- **Consumption Records** - Detailed consumption history tracking
- **Contract Management** - Prepaid order and contract association
- **Multiple Card Types** - Support for one-time payment and deposit settlement
- **Automated Tasks** - Scheduled expiration checks and status updates

## Installation

```bash
composer require tourze/prepaid-card-bundle
```

## Main Entities

### Card (Prepaid Card)
- Card number and password management
- Face value and balance tracking
- Status management (valid/expired/insufficient balance)
- Activation time and expiration time

### Consumption (Consumption Record)
- Consumption title and amount
- Associated order ID
- Refundable amount management
- Creation time and IP logging

### Contract (Prepaid Contract)
- Prepaid order management
- Cost tracking
- Refund processing
- Consumption record association

## Console Commands

### prepaid-card:expire-check

Automatically check and handle expired prepaid cards

```bash
php bin/console prepaid-card:expire-check
```

**Features:**
- Check cards with expired time and update status to `EXPIRED`
- Check cards with zero balance and update status to `EMPTY`
- Process up to 500 cards per batch
- Automatically executed on schedule (runs every minute)

**Processing Logic:**
1. Find cards with status `VALID` and expired time passed
2. Update these cards' status to `EXPIRED`
3. Find cards with status `VALID` and balance ≤ 0
4. Update these cards' status to `EMPTY`

## Usage

### Basic Usage

```php
use PrepaidCardBundle\Service\PrepaidCardService;

// Inject service
private PrepaidCardService $prepaidCardService;

// Check if balance is sufficient
if ($this->prepaidCardService->hasEnoughBalance($card, $amount)) {
    // Execute consumption
    $this->prepaidCardService->consume($card, $amount, $title);
}
```

### Card Status Management

```php
use PrepaidCardBundle\Enum\PrepaidCardStatus;

// Check card status
if ($card->getStatus() === PrepaidCardStatus::VALID) {
    // Card is valid and can be used
}
```

### Card Types

```php
use PrepaidCardBundle\Enum\PrepaidCardType;

// One-time full payment
$card->setType(PrepaidCardType::ONE_TIME);

// Deposit with later settlement
$card->setType(PrepaidCardType::AFTER);
```

## Configuration

Configure in `config/packages/prepaid_card.yaml`:

```yaml
# Prepaid card module configuration
prepaid_card:
    # Expiration check frequency (cron expression)
    expire_check_cron: '* * * * *'
    # Maximum number of cards processed per batch
    batch_size: 500
```

## Database Schema

- `ims_prepaid_card` - Main prepaid card table
- `ims_prepaid_consumption` - Consumption record table
- `ims_prepaid_contract` - Prepaid contract table
- `ims_prepaid_company` - Card issuing company table
- `ims_prepaid_package` - Card package table
- `ims_prepaid_campaign` - Marketing campaign table

## Enum Types

### PrepaidCardStatus (Card Status)
- `VALID` - Valid
- `EXPIRED` - Expired
- `EMPTY` - Insufficient balance
- `INACTIVE` - Not activated

### PrepaidCardType (Card Type)
- `ONE_TIME` - One-time full payment
- `AFTER` - Deposit with later settlement

### PrepaidCardExpireType (Expiration Type)
- `SAME_WITH_CARD` - Same as card validity period
- `AFTER_ACTIVATION` - After activation

## Events and Listeners

The module provides the following events:
- Card creation event
- Consumption record creation event
- Balance change event
- Status change event

## Extensibility

You can extend functionality through:
- Implementing custom card types
- Adding additional consumption validation rules
- Customizing expiration handling logic
- Integrating third-party payment systems

## Reference Documentation

- [Prepaid Card Design Principles](https://blog.csdn.net/zhichaosong/article/details/120316738)
- [Symfony Bundle Development Guide](https://symfony.com/doc/current/bundles.html)

## License

This project is licensed under the MIT License - see the LICENSE file for details.
