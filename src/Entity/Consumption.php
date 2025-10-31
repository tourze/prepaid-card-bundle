<?php

namespace PrepaidCardBundle\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use PrepaidCardBundle\Repository\ConsumptionRepository;
use Symfony\Component\Serializer\Attribute\Ignore;
use Symfony\Component\Validator\Constraints as Assert;
use Tourze\Arrayable\AdminArrayInterface;
use Tourze\Arrayable\ApiArrayInterface;
use Tourze\DoctrineIpBundle\Traits\CreatedFromIpAware;
use Tourze\DoctrineTimestampBundle\Traits\CreateTimeAware;
use Tourze\DoctrineUserBundle\Traits\CreatedByAware;

/**
 * @implements AdminArrayInterface<string, mixed>
 * @implements ApiArrayInterface<string, mixed>
 */
#[ORM\Table(name: 'ims_prepaid_consumption', options: ['comment' => '消费记录'])]
#[ORM\Entity(repositoryClass: ConsumptionRepository::class)]
class Consumption implements ApiArrayInterface, AdminArrayInterface, \Stringable
{
    use CreateTimeAware;
    use CreatedByAware;
    use CreatedFromIpAware;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: Types::INTEGER, options: ['comment' => 'ID'])]
    private int $id = 0;

    #[Ignore]
    #[ORM\ManyToOne(inversedBy: 'consumptions')]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private Card $card;

    #[Assert\NotBlank]
    #[Assert\Length(max: 100)]
    #[ORM\Column(length: 100, options: ['comment' => '标题'])]
    private string $title;

    #[Assert\Length(max: 40)]
    #[ORM\Column(length: 40, nullable: true, options: ['comment' => '关联订单ID'])]
    private ?string $orderId = null;

    #[Assert\NotBlank]
    #[Assert\PositiveOrZero]
    #[Assert\Length(max: 13)]
    #[ORM\Column(type: Types::DECIMAL, precision: 10, scale: 2, options: ['comment' => '费用'])]
    private string $amount;

    #[ORM\ManyToOne(inversedBy: 'consumptions')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Contract $contract = null;

    #[Assert\PositiveOrZero]
    #[Assert\Length(max: 13)]
    #[ORM\Column(type: Types::DECIMAL, precision: 10, scale: 2, nullable: true, options: ['comment' => '可退款金额'])]
    private ?string $refundableAmount = null;

    public function __toString(): string
    {
        if (null === $this->getId() || 0 === $this->getId()) {
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

    public function setCard(Card $card): void
    {
        $this->card = $card;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function setTitle(string $title): void
    {
        $this->title = $title;
    }

    public function getOrderId(): ?string
    {
        return $this->orderId;
    }

    public function setOrderId(?string $orderId): void
    {
        $this->orderId = $orderId;
    }

    public function getAmount(): string
    {
        return $this->amount;
    }

    public function setAmount(string $amount): void
    {
        $this->amount = $amount;
    }

    /**
     * @return array<string, mixed>
     */
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

    public function setContract(?Contract $contract): void
    {
        $this->contract = $contract;
    }

    public function getRefundableAmount(): ?string
    {
        return $this->refundableAmount;
    }

    public function setRefundableAmount(?string $refundableAmount): void
    {
        $this->refundableAmount = $refundableAmount;
    }

    /**
     * @return array<string, mixed>
     */
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
