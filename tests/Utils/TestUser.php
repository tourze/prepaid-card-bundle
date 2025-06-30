<?php

namespace PrepaidCardBundle\Tests\Utils;

use Symfony\Component\Security\Core\User\UserInterface;

/**
 * 实现UserInterface的测试用户类，用于测试预付卡服务
 * 
 * @internal
 * @codeCoverageIgnore
 */
class TestUser implements UserInterface
{
    private string $id;
    private string $username;
    private array $roles = [];

    public function __construct(string $id = '1', string $username = 'test_user')
    {
        $this->id = $id;
        $this->username = $username;
    }

    /**
     * 获取用户ID
     */
    public function getId(): string
    {
        return $this->id;
    }

    /**
     * {@inheritdoc}
     */
    public function getRoles(): array
    {
        return $this->roles;
    }

    /**
     * @deprecated since Symfony 5.3, use getUserIdentifier() instead
     */
    public function getUsername(): string
    {
        return $this->username;
    }

    /**
     * {@inheritdoc}
     */
    public function getUserIdentifier(): string
    {
        return $this->username;
    }

    /**
     * {@inheritdoc}
     */
    public function eraseCredentials(): void
    {
        // 空实现，不需要擦除凭证
    }
}
