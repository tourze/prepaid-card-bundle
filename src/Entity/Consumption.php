<?php

namespace PrepaidCardBundle\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use PrepaidCardBundle\Repository\ConsumptionRepository;
use Symfony\Component\Serializer\Attribute\Ignore;
use Tourze\Arrayable\AdminArrayInterface;
use Tourze\Arrayable\ApiArrayInterface;
use Tourze\DoctrineIpBundle\Attribute\CreateIpColumn;
use Tourze\DoctrineTimestampBundle\Traits\CreateTimeAware;
use Tourze\DoctrineUserBundle\Traits\CreatedByAware;

#[ORM\Table(name: 'ims_prepaid_consumption', options: ['comment' => '消费记录'])]
#[ORM\Entity(repositoryClass: ConsumptionRepository::class)]
class Consumption implements ApiArrayInterface, AdminArrayInterface, \Stringable
{
    use CreateTimeAware;
    use CreatedByAware;
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: Types::INTEGER, options: ['comment' => 'ID'])]
    private ?int $id = 0;

    #[Ignore]
    #[ORM\ManyToOne(inversedBy: 'consumptions')]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private Card $card;

    #[ORM\Column(length: 100, options: ['comment' => '标题'])]
    private string $title;

    #[ORM\Column(length: 40, nullable: true, options: ['comment' => '关联订单ID'])]
    private ?string $orderId = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 10, scale: 2, options: ['comment' => '费用'])]
    private string $amount;

    #[ORM\ManyToOne(inversedBy: 'consumptions')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Contract $contract = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 10, scale: 2, nullable: true, options: ['comment' => '可退款金额'])]
    private ?string $refundableAmount = null;

    #[CreateIpColumn]
    #[ORM\Column(length: 45, nullable: true, options: ['comment' => '创建时IP'])]
    private ?string $createdFromIp = null;


    public function __toString(): string
    {
        if ($this->getId() === null || $this->getId() === 0) {
            return '';
        }

        return "{$this->getTitle()} {$this->getAmount()}";
    }

    public function getId(): ?int
    {
        return $this->id;
    }


    public function getCard(): Card
    {
        return $this->card;
    }

    public function setCard(Card $card): static
    {
        $this->card = $card;

        return $this;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function setTitle(string $title): static
    {
        $this->title = $title;

        return $this;
    }

    public function getOrderId(): ?string
    {
        return $this->orderId;
    }

    public function setOrderId(?string $orderId): static
    {
        $this->orderId = $orderId;

        return $this;
    }

    public function getAmount(): string
    {
        return $this->amount;
    }

    public function setAmount(string $amount): static
    {
        $this->amount = $amount;

        return $this;
    }

    public function retrieveApiArray(): array
    {
        return [
            'id' => $this->getId(),
            'createTime' => $this->getCreateTime()?->format('Y-m-d H:i:s'),
            'title' => $this->getTitle(),
            'orderId' => $this->getOrderId(),
            'cost' => $this->getAmount(),
            'contract' => $this->getContract()?->getCostAmount(),
        ];
    }

    public function getContract(): ?Contract
    {
        return $this->contract;
    }

    public function setContract(?Contract $contract): static
    {
        $this->contract = $contract;

        return $this;
    }

    public function getRefundableAmount(): ?string
    {
        return $this->refundableAmount;
    }

    public function setRefundableAmount(?string $refundableAmount): static
    {
        $this->refundableAmount = $refundableAmount;

        return $this;
    }

    public function getCreatedFromIp(): ?string
    {
        return $this->createdFromIp;
    }

    public function setCreatedFromIp(?string $createdFromIp): void
    {
        $this->createdFromIp = $createdFromIp;
    }


    public function retrieveAdminArray(): array
    {
        return [
            'id' => $this->getId(),
            'createTime' => $this->getCreateTime()?->format('Y-m-d H:i:s'),
            'title' => $this->getTitle(),
            'orderId' => $this->getOrderId(),
            'amount' => $this->getAmount(),
            'refundableAmount' => $this->getRefundableAmount(),
            'createdFromIp' => $this->getCreatedFromIp(),
        ];
    }
}
