<?php

namespace PrepaidCardBundle\Entity;

use Carbon\Carbon;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use PrepaidCardBundle\Enum\PrepaidCardStatus;
use PrepaidCardBundle\Repository\CardRepository;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Serializer\Attribute\Ignore;
use Tourze\Arrayable\AdminArrayInterface;
use Tourze\Arrayable\ApiArrayInterface;
use Tourze\DoctrineIndexedBundle\Attribute\IndexColumn;
use Tourze\DoctrineSnowflakeBundle\Service\SnowflakeIdGenerator;
use Tourze\DoctrineTimestampBundle\Traits\TimestampableAware;
use Tourze\DoctrineTrackBundle\Attribute\TrackColumn;
use Tourze\DoctrineUserBundle\Attribute\CreatedByColumn;
use Tourze\EasyAdmin\Attribute\Action\CurdAction;
use Tourze\EasyAdmin\Attribute\Action\Editable;
use Tourze\EasyAdmin\Attribute\Action\Exportable;
use Tourze\EasyAdmin\Attribute\Action\Listable;
use Tourze\EasyAdmin\Attribute\Column\BoolColumn;
use Tourze\EasyAdmin\Attribute\Column\ExportColumn;
use Tourze\EasyAdmin\Attribute\Column\ListColumn;
use Tourze\EasyAdmin\Attribute\Field\FormField;
use Tourze\EasyAdmin\Attribute\Filter\Filterable;
use Tourze\EasyAdmin\Attribute\Filter\Keyword;
use Tourze\EasyAdmin\Attribute\Permission\AsPermission;

/**
 * @see https://blog.csdn.net/zhichaosong/article/details/120316738
 */
#[AsPermission(title: '礼品卡')]
#[Listable]
#[Exportable]
#[Editable]
#[ORM\Table(name: 'ims_prepaid_card', options: ['comment' => '礼品卡'])]
#[ORM\Entity(repositoryClass: CardRepository::class)]
class Card implements ApiArrayInterface, AdminArrayInterface
{
    use TimestampableAware;
    #[ExportColumn]
    #[ListColumn(order: -1, sorter: true)]
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'CUSTOM')]
    #[ORM\CustomIdGenerator(SnowflakeIdGenerator::class)]
    #[ORM\Column(type: Types::BIGINT, nullable: false, options: ['comment' => 'ID'])]
    private ?string $id = null;

    #[Filterable(label: '礼品卡公司')]
    #[ListColumn(title: '礼品卡公司')]
    #[ORM\ManyToOne(inversedBy: 'cards')]
    private ?Company $company = null;

    #[ORM\Column(length: 40, unique: true, options: ['comment' => '卡号'])]
    private string $cardNumber;

    #[ExportColumn]
    #[Keyword]
    #[ListColumn]
    #[ORM\Column(length: 64, nullable: true, options: ['comment' => '卡密'])]
    private ?string $cardPassword = null;

    #[ExportColumn]
    #[ListColumn(sorter: true)]
    #[FormField]
    #[TrackColumn]
    #[ORM\Column(type: Types::DECIMAL, precision: 10, scale: 2, options: ['comment' => '面值'])]
    private ?string $parValue = null;

    #[ExportColumn]
    #[ListColumn(sorter: true)]
    #[FormField]
    #[IndexColumn]
    #[TrackColumn]
    #[ORM\Column(type: Types::DECIMAL, precision: 10, scale: 2, nullable: true, options: ['comment' => '余额'])]
    private ?string $balance = null;

    #[ExportColumn]
    #[ListColumn]
    #[IndexColumn]
    #[TrackColumn]
    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true, options: ['comment' => '绑定时间'])]
    private ?\DateTimeInterface $bindTime = null;

    #[ExportColumn]
    #[ListColumn]
    #[FormField]
    #[IndexColumn]
    #[TrackColumn]
    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true, options: ['comment' => '过期时间'])]
    private ?\DateTimeInterface $expireTime = null;

    #[ExportColumn]
    #[Filterable(label: '用户')]
    #[ListColumn(title: '用户')]
    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: true, onDelete: 'SET NULL')]
    private ?UserInterface $owner = null;

    #[ExportColumn]
    #[ListColumn]
    #[FormField]
    #[TrackColumn]
    #[ORM\Column(length: 30, nullable: true, enumType: PrepaidCardStatus::class, options: ['comment' => '状态'])]
    private ?PrepaidCardStatus $status = null;

    /**
     * @var Collection<int, Consumption>
     */
    #[CurdAction(label: '消费记录')]
    #[Ignore]
    #[ORM\OneToMany(mappedBy: 'card', targetEntity: Consumption::class, orphanRemoval: true)]
    private Collection $consumptions;

    #[ORM\ManyToOne(inversedBy: 'cards')]
    private ?Campaign $campaign = null;

    #[ORM\ManyToOne(inversedBy: 'cards')]
    private ?Package $package = null;

    #[BoolColumn]
    #[IndexColumn]
    #[TrackColumn]
    #[ORM\Column(type: Types::BOOLEAN, nullable: true, options: ['comment' => '有效', 'default' => 0])]
    #[ListColumn(order: 97)]
    #[FormField(order: 97)]
    private ?bool $valid = false;

    #[CreatedByColumn]
    #[ORM\Column(nullable: true, options: ['comment' => '创建人'])]
    private ?string $createdBy = null;

    public function __construct()
    {
        $this->consumptions = new ArrayCollection();
    }

    public function getId(): ?string
    {
        return $this->id;
    }

    public function isValid(): ?bool
    {
        return $this->valid;
    }

    public function setValid(?bool $valid): self
    {
        $this->valid = $valid;

        return $this;
    }

    public function getCompany(): ?Company
    {
        return $this->company;
    }

    public function setCompany(?Company $company): static
    {
        $this->company = $company;

        return $this;
    }

    public function getCardNumber(): string
    {
        return $this->cardNumber;
    }

    public function setCardNumber(string $cardNumber): static
    {
        $this->cardNumber = $cardNumber;

        return $this;
    }

    public function getCardPassword(): ?string
    {
        return $this->cardPassword;
    }

    public function setCardPassword(?string $cardPassword): static
    {
        $this->cardPassword = $cardPassword;

        return $this;
    }

    public function getParValue(): ?string
    {
        return $this->parValue;
    }

    public function setParValue(string $parValue): static
    {
        $this->parValue = $parValue;

        return $this;
    }

    public function getBindTime(): ?\DateTimeInterface
    {
        return $this->bindTime;
    }

    public function setBindTime(?\DateTimeInterface $bindTime): static
    {
        $this->bindTime = $bindTime;

        return $this;
    }

    public function getExpireTime(): ?\DateTimeInterface
    {
        return $this->expireTime;
    }

    public function setExpireTime(?\DateTimeInterface $expireTime): static
    {
        $this->expireTime = $expireTime;

        return $this;
    }

    public function getOwner(): ?UserInterface
    {
        return $this->owner;
    }

    public function setOwner(?UserInterface $owner): static
    {
        $this->owner = $owner;

        return $this;
    }

    public function getBalance(): ?string
    {
        return $this->balance;
    }

    public function setBalance(?string $balance): static
    {
        $this->balance = $balance;

        return $this;
    }

    public function getStatus(): ?PrepaidCardStatus
    {
        return $this->status;
    }

    public function setStatus(?PrepaidCardStatus $status): static
    {
        $this->status = $status;

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
            $consumption->setCard($this);
        }

        return $this;
    }

    public function removeConsumption(Consumption $consumption): static
    {
        if ($this->consumptions->removeElement($consumption)) {
            // set the owning side to null (unless already changed)
            if ($consumption->getCard() === $this) {
                $consumption->setCard(null);
            }
        }

        return $this;
    }

    public function retrieveApiArray(): array
    {
        return [
            'id' => $this->getId(),
            'createTime' => $this->getCreateTime()?->format('Y-m-d H:i:s'),
            'updateTime' => $this->getUpdateTime()?->format('Y-m-d H:i:s'),
            'cardNumber' => $this->getCardNumber(),
            // 'cardPassword' => $this->getCardPassword(),
            'parValue' => $this->getParValue(),
            'balance' => $this->getBalance(),
            'bindTime' => $this->getBindTime()?->format('Y-m-d H:i:s'),
            'expireTime' => $this->getExpireTime()?->format('Y-m-d H:i:s'),
            'status' => $this->getStatus()?->toArray(),
            'campaign' => $this->getCampaign()?->retrieveApiArray(),
            'package' => $this->getPackage()?->retrieveApiArray(),
        ];
    }

    public function getCampaign(): ?Campaign
    {
        return $this->campaign;
    }

    public function setCampaign(?Campaign $campaign): static
    {
        $this->campaign = $campaign;

        return $this;
    }

    public function checkStatus(): void
    {
        $now = Carbon::now();
        if ($now->greaterThan($this->getExpireTime())) {
            $this->setStatus(PrepaidCardStatus::EXPIRED);
        } else {
            if ($this->getBalance() > 0) {
                $this->setStatus(PrepaidCardStatus::VALID);
            } else {
                $this->setStatus(PrepaidCardStatus::EMPTY);
            }
        }
    }

    public function getPackage(): ?Package
    {
        return $this->package;
    }

    public function setPackage(?Package $package): static
    {
        $this->package = $package;

        return $this;
    }

    public function setCreatedBy(?string $createdBy): void
    {
        $this->createdBy = $createdBy;
    }

    public function getCreatedBy(): ?string
    {
        return $this->createdBy;
    }public function retrieveAdminArray(): array
    {
        return [
            'id' => $this->getId(),
            'createTime' => $this->getCreateTime()?->format('Y-m-d H:i:s'),
            'updateTime' => $this->getUpdateTime()?->format('Y-m-d H:i:s'),
            'cardNumber' => $this->getCardNumber(),
            'cardPassword' => $this->getCardPassword(),
            'parValue' => $this->getParValue(),
            'balance' => $this->getBalance(),
            'bindTime' => $this->getBindTime()?->format('Y-m-d H:i:s'),
            'expireTime' => $this->getExpireTime()?->format('Y-m-d H:i:s'),
            'status' => $this->getStatus()?->value,
        ];
    }
}
