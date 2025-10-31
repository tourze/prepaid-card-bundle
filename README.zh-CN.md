# prepaid-card-bundle

[![PHP Version](https://img.shields.io/badge/php-8.1%2B-blue)](https://php.net)
[![License](https://img.shields.io/badge/license-MIT-green)](LICENSE)
[![Build Status](https://img.shields.io/badge/build-passing-brightgreen)](#)
[![Code Coverage](https://img.shields.io/badge/coverage-95%25-brightgreen)](#)

[English](README.md) | [中文](README.zh-CN.md)

预付卡管理模块 - 提供预付卡的创建、消费、余额管理和过期处理功能

## 功能特性

- **预付卡管理** - 创建、激活、充值、消费预付卡
- **余额管理** - 实时余额查询、消费记录跟踪
- **过期处理** - 自动检查和处理过期卡片
- **消费记录** - 详细的消费历史记录
- **合同管理** - 预付订单和合同关联
- **多种卡类型** - 支持一次性付款和定金后期结算
- **自动化任务** - 定时过期检查和状态更新

## 安装

```bash
composer require tourze/prepaid-card-bundle
```

## 主要实体

### Card (预付卡)
- 卡号和卡密管理
- 面值和余额跟踪
- 状态管理 (有效/过期/余额不足)
- 激活时间和过期时间

### Consumption (消费记录)
- 消费标题和金额
- 关联订单ID
- 退款金额管理
- 创建时间和IP记录

### Contract (预付合同)
- 预付订单管理
- 费用追踪
- 退款处理
- 消费记录关联

## 控制台命令

### prepaid-card:expire-check

自动检查和处理过期的预付卡

```bash
php bin/console prepaid-card:expire-check
```

**功能：**
- 检查过期时间已到的卡片，将状态更新为 `EXPIRED`
- 检查余额为0的卡片，将状态更新为 `EMPTY`
- 每次处理最多500张卡片
- 自动定时执行 (每分钟运行一次)

**处理逻辑：**
1. 查找状态为 `VALID` 且过期时间已过的卡片
2. 将这些卡片状态更新为 `EXPIRED`
3. 查找状态为 `VALID` 且余额≤0的卡片
4. 将这些卡片状态更新为 `EMPTY`

## 使用方法

### 基本使用

```php
use PrepaidCardBundle\Service\PrepaidCardService;

// 注入服务
private PrepaidCardService $prepaidCardService;

// 检查余额是否足够
if ($this->prepaidCardService->hasEnoughBalance($card, $amount)) {
    // 执行消费
    $this->prepaidCardService->consume($card, $amount, $title);
}
```

### 卡片状态管理

```php
use PrepaidCardBundle\Enum\PrepaidCardStatus;

// 检查卡片状态
if ($card->getStatus() === PrepaidCardStatus::VALID) {
    // 卡片有效，可以使用
}
```

### 卡片类型

```php
use PrepaidCardBundle\Enum\PrepaidCardType;

// 一次性全额付款
$card->setType(PrepaidCardType::ONE_TIME);

// 定金后期结算
$card->setType(PrepaidCardType::AFTER);
```

## 配置

在 `config/packages/prepaid_card.yaml` 中配置：

```yaml
# 预付卡模块配置
prepaid_card:
    # 过期检查频率 (cron 表达式)
    expire_check_cron: '* * * * *'
    # 单次处理的最大卡片数量
    batch_size: 500
```

## 数据库表结构

- `ims_prepaid_card` - 预付卡主表
- `ims_prepaid_consumption` - 消费记录表
- `ims_prepaid_contract` - 预付合同表
- `ims_prepaid_company` - 发卡公司表
- `ims_prepaid_package` - 卡片套餐表
- `ims_prepaid_campaign` - 营销活动表

## 枚举类型

### PrepaidCardStatus (卡片状态)
- `VALID` - 有效
- `EXPIRED` - 已过期
- `EMPTY` - 余额不足
- `INACTIVE` - 未激活

### PrepaidCardType (卡片类型)
- `ONE_TIME` - 一次性全额付款
- `AFTER` - 定金后期结算

### PrepaidCardExpireType (过期类型)
- `SAME_WITH_CARD` - 同卡有效期
- `AFTER_ACTIVATION` - 激活后

## 事件和监听器

模块提供以下事件：
- 卡片创建事件
- 消费记录创建事件
- 余额变更事件
- 状态变更事件

## 扩展性

可以通过以下方式扩展功能：
- 实现自定义的卡片类型
- 添加额外的消费验证规则
- 自定义过期处理逻辑
- 集成第三方支付系统

## 参考文档

- [预付卡设计原理](https://blog.csdn.net/zhichaosong/article/details/120316738)
- [Symfony Bundle 开发指南](https://symfony.com/doc/current/bundles.html)

## 许可证

此项目根据 MIT 许可证发布 - 有关详细信息，请参阅 LICENSE 文件。
