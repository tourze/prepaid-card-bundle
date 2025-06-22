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
use Tourze\DoctrineIpBundle\Attribute\CreateIpColumn;
use Tourze\DoctrineRandomBundle\Attribute\RandomStringColumn;
use Tourze\DoctrineTimestampBundle\Traits\CreateTimeAware;
use Tourze\DoctrineUserBundle\Traits\CreatedByAware;

#[ORM\Table(name: 'ims_prepaid_contract', options: ['comment' => '预付订单'])]
#[ORM\Entity(repositoryClass: ContractRepository::class)]
class Contract implements ApiArrayInterface, AdminArrayInterface
, \Stringable
{
    use CreateTimeAware;
    use CreatedByAware;
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: Types::INTEGER, options: ['comment' => 'ID'])]
    private ?int $id = 0;

    #[RandomStringColumn(length: 10)]
    #[Groups(['admin_curd'])]
    #[ORM\Column(type: Types::STRING, length: 100, unique: true, nullable: true, options: ['comment' => '编码'])]
    private ?string $code = null;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE, nullable: true, options: ['comment' => '退款时间'])]
    private ?\DateTimeImmutable $refundTime = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 10, scale: 2, options: ['comment' => '总费用'])]
    private string $costAmount;

    /**
     * @var Collection<int, Consumption>
     */
    #[Ignore]
    #[ORM\OneToMany(mappedBy: 'contract', targetEntity: Consumption::class, cascade: ['persist'], orphanRemoval: true)]
    private Collection $consumptions;

    #[CreateIpColumn]
    #[ORM\Column(length: 45, nullable: true, options: ['comment' => '创建时IP'])]
    private ?string $createdFromIp = null;


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

    public function getRefundTime(): ?\DateTimeImmutable
    {
        return $this->refundTime;
    }

    public function setRefundTime(?\DateTimeImmutable $refundTime): static
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

    public function __toString(): string
    {
        return (string) $this->getId();
    }
}
