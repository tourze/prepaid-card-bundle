<?php

namespace PrepaidCardBundle\Tests\Mock;

/**
 * 模拟BizUser类供测试使用
 * 
 * @internal
 * @codeCoverageIgnore
 */
class MockBizUser
{
    private string $id;

    public function __construct(string $id = '1')
    {
        $this->id = $id;
    }

    public function getId(): string
    {
        return $this->id;
    }
}