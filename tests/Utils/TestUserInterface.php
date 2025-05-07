<?php

namespace PrepaidCardBundle\Tests\Utils;

/**
 * 测试用户接口
 * 用于模拟在预付卡系统中的用户对象
 */
interface TestUserInterface
{
    /**
     * 获取用户ID
     */
    public function getId(): string|int;

    /**
     * 获取用户名称
     */
    public function getUsername(): string;
}
