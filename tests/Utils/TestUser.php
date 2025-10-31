<?php

namespace PrepaidCardBundle\Tests\Utils;

use Symfony\Component\Security\Core\User\UserInterface;

/**
 * 实现UserInterface的测试用户类，用于测试预付卡服务
 *
 * @internal
 *
 * @codeCoverageIgnore
 */
class TestUser implements UserInterface
{
    /**
     * @var array<string>
     */
    private array $roles = [];

    public function __construct(
        private readonly string $id = '1',
        private readonly string $username = 'test_user',
    ) {
    }

    /**
     * 获取用户ID
     */
    public function getId(): string
    {
        return $this->id;
    }

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

    public function getUserIdentifier(): string
    {
        return '' !== $this->username ? $this->username : 'default_user';
    }

    public function eraseCredentials(): void
    {
        // 空实现，不需要擦除凭证
    }

    public function __toString(): string
    {
        return $this->getId();
    }
}
