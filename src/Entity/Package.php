<?php

namespace PrepaidCardBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use PrepaidCardBundle\Enum\PrepaidCardExpireType;
use PrepaidCardBundle\Enum\PrepaidCardType;
use PrepaidCardBundle\Repository\PackageRepository;
use Symfony\Component\Serializer\Annotation\Ignore;
use Tourze\Arrayable\AdminArrayInterface;
use Tourze\Arrayable\ApiArrayInterface;
use Tourze\DoctrineIndexedBundle\Attribute\IndexColumn;
use Tourze\DoctrineSnowflakeBundle\Service\SnowflakeIdGenerator;
use Tourze\DoctrineTimestampBundle\Traits\TimestampableAware;
use Tourze\DoctrineTrackBundle\Attribute\TrackColumn;
use Tourze\DoctrineUserBundle\Attribute\CreatedByColumn;
use Tourze\DoctrineUserBundle\Attribute\UpdatedByColumn;

#[ORM\Table(name: 'ims_prepaid_package', options: ['comment' => '礼品卡码包'])]
#[ORM\Entity(repositoryClass: PackageRepository::class)]
class Package implements ApiArrayInterface, AdminArrayInterface
, \Stringable
{
    use TimestampableAware;
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'CUSTOM')]
    #[ORM\CustomIdGenerator(SnowflakeIdGenerator::class)]
    #[ORM\Column(type: Types::BIGINT, nullable: false, options: ['comment' => 'ID'])]
    private ?string $id = null;

    #[Ignore]
    #[ORM\ManyToOne(inversedBy: 'packages')]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private ?Campaign $campaign = null;

    #[IndexColumn]
    #[ORM\Column(length: 40, unique: true, options: ['comment' => '码包ID'])]
    private string $packageId;

    #[IndexColumn]
    #[ORM\Column(type: Types::DECIMAL, precision: 10, scale: 2, nullable: true, options: ['comment' => '面值'])]
    private ?string $parValue = null;

    #[ORM\Column(options: ['comment' => '数量'])]
    private int $quantity;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE, nullable: true, options: ['comment' => '卡有效起始时间'])]
    private ?\DateTimeImmutable $startTime = null;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE, nullable: true, options: ['comment' => '卡有效截止时间'])]
    private ?\DateTimeImmutable $expireTime = null;

    #[ORM\Column(nullable: true, options: ['comment' => '余额有效期（天）'])]
    private ?int $expireDays = null;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE, nullable: true, options: ['comment' => '最大有效时间'])]
    private ?\DateTimeImmutable $maxValidTime = null;

    #[ORM\Column(length: 20, enumType: PrepaidCardType::class, options: ['comment' => '类型'])]
    private PrepaidCardType $type;

    #[ORM\Column(type: Types::SMALLINT, enumType: PrepaidCardExpireType::class, options: ['comment' => '类型'])]
    private PrepaidCardExpireType $expireType;

    #[ORM\Column(type: Types::INTEGER, nullable: true, options: ['default' => 0, 'comment' => '天数'])]
    private int $expireNum;

    #[ORM\Column(length: 500, nullable: true, options: ['comment' => '缩略图'])]
    private ?string $thumbUrl = null;

    /**
     * @var Collection<int, Card>
     */
    #[ORM\OneToMany(mappedBy: 'package', targetEntity: Card::class)]
    private Collection $cards;

    #[IndexColumn]
    #[TrackColumn]
    #[ORM\Column(type: Types::BOOLEAN, nullable: true, options: ['comment' => '有效', 'default' => 0])]
    private ?bool $valid = false;

    #[CreatedByColumn]
    #[ORM\Column(nullable: true, options: ['comment' => '创建人'])]
    private ?string $createdBy = null;

    #[UpdatedByColumn]
    #[ORM\Column(nullable: true, options: ['comment' => '更新人'])]
    private ?string $updatedBy = null;

    public function __construct()
    {
        $this->cards = new ArrayCollection();
    }

    public function getId(): ?string
    {
        return $this->id;
    }

    public function setCreatedBy(?string $createdBy): self
    {
        $this->createdBy = $createdBy;

        return $this;
    }

    public function getCreatedBy(): ?string
    {
        return $this->createdBy;
    }

    public function setUpdatedBy(?string $updatedBy): self
    {
        $this->updatedBy = $updatedBy;

        return $this;
    }

    public function getUpdatedBy(): ?string
    {
        return $this->updatedBy;
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

    public function getCampaign(): ?Campaign
    {
        return $this->campaign;
    }

    public function setCampaign(?Campaign $campaign): static
    {
        $this->campaign = $campaign;

        return $this;
    }

    public function getPackageId(): string
    {
        return $this->packageId;
    }

    public function setPackageId(string $packageId): static
    {
        $this->packageId = $packageId;

        return $this;
    }

    public function getParValue(): ?string
    {
        return $this->parValue;
    }

    public function setParValue(?string $parValue): static
    {
        $this->parValue = $parValue;

        return $this;
    }

    public function getQuantity(): ?int
    {
        return $this->quantity;
    }

    public function setQuantity(int $quantity): static
    {
        $this->quantity = $quantity;

        return $this;
    }

    public function getType(): PrepaidCardType
    {
        return $this->type;
    }

    public function setType(PrepaidCardType $type): static
    {
        $this->type = $type;

        return $this;
    }

    public function getStartTime(): ?\DateTimeImmutable
    {
        return $this->startTime;
    }

    public function setStartTime(?\DateTimeImmutable $startTime): static
    {
        $this->startTime = $startTime;

        return $this;
    }

    public function getExpireTime(): ?\DateTimeImmutable
    {
        return $this->expireTime;
    }

    public function setExpireTime(?\DateTimeImmutable $expireTime): static
    {
        $this->expireTime = $expireTime;

        return $this;
    }

    public function getExpireDays(): ?int
    {
        return $this->expireDays;
    }

    public function setExpireDays(?int $expireDays): static
    {
        $this->expireDays = $expireDays;

        return $this;
    }

    public function getMaxValidTime(): ?\DateTimeImmutable
    {
        return $this->maxValidTime;
    }

    public function setMaxValidTime(?\DateTimeImmutable $maxValidTime): static
    {
        $this->maxValidTime = $maxValidTime;

        return $this;
    }

    public function getThumbUrl(): ?string
    {
        return $this->thumbUrl;
    }

    public function setThumbUrl(?string $thumbUrl): void
    {
        $this->thumbUrl = $thumbUrl;
    }

    /**
     * @return Collection<int, Card>
     */
    public function getCards(): Collection
    {
        return $this->cards;
    }

    public function addCard(Card $card): static
    {
        if (!$this->cards->contains($card)) {
            $this->cards->add($card);
            $card->setPackage($this);
        }

        return $this;
    }

    public function removeCard(Card $card): static
    {
        if ($this->cards->removeElement($card)) {
            // set the owning side to null (unless already changed)
            if ($card->getPackage() === $this) {
                $card->setPackage(null);
            }
        }

        return $this;
    }

    public function getExpireType(): PrepaidCardExpireType
    {
        return $this->expireType;
    }

    public function setExpireType(PrepaidCardExpireType $expireType): void
    {
        $this->expireType = $expireType;
    }

    public function getExpireNum(): int
    {
        return $this->expireNum;
    }

    public function setExpireNum(int $expireNum): void
    {
        $this->expireNum = $expireNum;
    }public function retrieveApiArray(): array
    {
        return [
            'id' => $this->getId(),
            'parValue' => $this->getParValue(),
            'thumbUrl' => $this->getThumbUrl(),
        ];
    }

    public function retrieveAdminArray(): array
    {
        return [
            'id' => $this->getId(),
            'createTime' => $this->getCreateTime()?->format('Y-m-d H:i:s'),
            'updateTime' => $this->getUpdateTime()?->format('Y-m-d H:i:s'),
            'startTime' => $this->getStartTime()?->format('Y-m-d H:i:s'),
            'expireTime' => $this->getExpireTime()?->format('Y-m-d H:i:s'),
            'valid' => $this->isValid(),
            'packageId' => $this->getPackageId(),
            'parValue' => $this->getParValue(),
            'quantity' => $this->getQuantity(),
            'expireDays' => $this->getExpireDays(),
            'maxValidTime' => $this->getMaxValidTime()?->format('Y-m-d H:i:s'),
            'type' => $this->getType()?->value,
            'thumbUrl' => $this->getThumbUrl(),
            'expireNum' => $this->getExpireNum(),
            'expireType' => $this->getExpireType()?->value,
        ];
    }

    public function __toString(): string
    {
        return (string) $this->getId();
    }
}
