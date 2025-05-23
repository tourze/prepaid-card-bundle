<?php

namespace PrepaidCardBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use PrepaidCardBundle\Repository\ContractRepository;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Serializer\Attribute\Ignore;
use Tourze\Arrayable\AdminArrayInterface;
use Tourze\Arrayable\ApiArrayInterface;
use Tourze\DoctrineIndexedBundle\Attribute\IndexColumn;
use Tourze\DoctrineIpBundle\Attribute\CreateIpColumn;
use Tourze\DoctrineRandomBundle\Attribute\RandomStringColumn;
use Tourze\DoctrineTimestampBundle\Attribute\CreateTimeColumn;
use Tourze\DoctrineUserBundle\Attribute\CreatedByColumn;
use Tourze\EasyAdmin\Attribute\Action\Listable;
use Tourze\EasyAdmin\Attribute\Column\ExportColumn;
use Tourze\EasyAdmin\Attribute\Column\ListColumn;
use Tourze\EasyAdmin\Attribute\Field\FormField;
use Tourze\EasyAdmin\Attribute\Filter\Keyword;
use Tourze\EasyAdmin\Attribute\Permission\AsPermission;

#[AsPermission(title: '预付卡订单')]
#[Listable]
#[ORM\Table(name: 'ims_prepaid_contract', options: ['comment' => '预付订单'])]
#[ORM\Entity(repositoryClass: ContractRepository::class)]
class Contract implements ApiArrayInterface, AdminArrayInterface
{
    #[ListColumn(order: -1)]
    #[ExportColumn]
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: Types::INTEGER, options: ['comment' => 'ID'])]
    private ?int $id = 0;

    #[RandomStringColumn(length: 10)]
    #[Groups(['admin_curd'])]
    #[FormField(title: '编码', order: -1)]
    #[Keyword]
    #[ListColumn(order: -1)]
    #[ORM\Column(type: Types::STRING, length: 100, unique: true, nullable: true, options: ['comment' => '编码'])]
    private ?string $code = null;

    #[ListColumn]
    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true, options: ['comment' => '退款时间'])]
    private ?\DateTimeInterface $refundTime = null;

    #[ListColumn]
    #[ORM\Column(type: Types::DECIMAL, precision: 10, scale: 2, options: ['comment' => '总费用'])]
    private string $costAmount;

    /**
     * @var Collection<int, Consumption>
     */
    #[Ignore]
    #[ListColumn(title: '流水')]
    #[ORM\OneToMany(mappedBy: 'contract', targetEntity: Consumption::class, cascade: ['persist'], orphanRemoval: true)]
    private Collection $consumptions;

    #[ListColumn(order: 99)]
    #[CreateIpColumn]
    #[ORM\Column(length: 45, nullable: true, options: ['comment' => '创建时IP'])]
    private ?string $createdFromIp = null;

    #[CreatedByColumn]
    #[ORM\Column(nullable: true, options: ['comment' => '创建人'])]
    private ?string $createdBy = null;

    #[IndexColumn]
    #[ListColumn(order: 98, sorter: true)]
    #[ExportColumn]
    #[CreateTimeColumn]
    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true, options: ['comment' => '创建时间'])]
    private ?\DateTimeInterface $createTime = null;

    public function __construct()
    {
        $this->consumptions = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCode(): ?string
    {
        return $this->code;
    }

    public function setCode(string $code): self
    {
        $this->code = $code;

        return $this;
    }

    /**
     * @return Collection<int, Consumption>
     */
    public function getConsumptions(): Collection
    {
        return $this->consumptions;
    }

    public function addConsumption(Consumption $consumption): static
    {
        if (!$this->consumptions->contains($consumption)) {
            $this->consumptions->add($consumption);
            $consumption->setContract($this);
        }

        return $this;
    }

    public function removeConsumption(Consumption $consumption): static
    {
        if ($this->consumptions->removeElement($consumption)) {
            // set the owning side to null (unless already changed)
            if ($consumption->getContract() === $this) {
                $consumption->setContract(null);
            }
        }

        return $this;
    }

    public function getRefundTime(): ?\DateTimeInterface
    {
        return $this->refundTime;
    }

    public function setRefundTime(?\DateTimeInterface $refundTime): static
    {
        $this->refundTime = $refundTime;

        return $this;
    }

    public function getCostAmount(): string
    {
        return $this->costAmount;
    }

    public function setCostAmount(string $costAmount): static
    {
        $this->costAmount = $costAmount;

        return $this;
    }

    /**
     * 获取可退款金额
     */
    public function getRefundableAmount(): float
    {
        $list = [];
        foreach ($this->consumptions as $consumption) {
            $list[] = $consumption->getRefundableAmount();
        }

        return array_sum($list);
    }

    public function getCreatedFromIp(): ?string
    {
        return $this->createdFromIp;
    }

    public function setCreatedFromIp(?string $createdFromIp): void
    {
        $this->createdFromIp = $createdFromIp;
    }

    public function setCreatedBy(?string $createdBy): void
    {
        $this->createdBy = $createdBy;
    }

    public function getCreatedBy(): ?string
    {
        return $this->createdBy;
    }

    public function setCreateTime(?\DateTimeInterface $createdAt): self
    {
        $this->createTime = $createdAt;

        return $this;
    }

    public function getCreateTime(): ?\DateTimeInterface
    {
        return $this->createTime;
    }

    public function retrieveApiArray(): array
    {
        return [
            'id' => $this->getId(),
            'code' => $this->getCode(),
            'costAmount' => $this->getCostAmount(),
        ];
    }

    public function retrieveAdminArray(): array
    {
        return [
            'id' => $this->getId(),
            'code' => $this->getCode(),
            'costAmount' => $this->getCostAmount(),
            'refundTime' => $this->getRefundTime()?->format('Y-m-d H:i:s'),
            'createTime' => $this->getCreateTime()?->format('Y-m-d H:i:s'),
            'createdFromIp' => $this->getCreatedFromIp(),
        ];
    }
}
