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
use Symfony\Component\Validator\Constraints as Assert;
use Tourze\Arrayable\AdminArrayInterface;
use Tourze\Arrayable\ApiArrayInterface;
use Tourze\DoctrineIndexedBundle\Attribute\IndexColumn;
use Tourze\DoctrineSnowflakeBundle\Traits\SnowflakeKeyAware;
use Tourze\DoctrineTimestampBundle\Traits\TimestampableAware;
use Tourze\DoctrineTrackBundle\Attribute\TrackColumn;
use Tourze\DoctrineUserBundle\Traits\BlameableAware;

/**
 * @implements AdminArrayInterface<string, mixed>
 * @implements ApiArrayInterface<string, mixed>
 */
#[ORM\Table(name: 'ims_prepaid_package', options: ['comment' => '礼品卡码包'])]
#[ORM\Entity(repositoryClass: PackageRepository::class)]
class Package implements ApiArrayInterface, AdminArrayInterface, \Stringable
{
    use TimestampableAware;
    use BlameableAware;
    use SnowflakeKeyAware;

    #[Ignore]
    #[ORM\ManyToOne(inversedBy: 'packages')]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private ?Campaign $campaign = null;

    #[IndexColumn]
    #[Assert\NotBlank]
    #[Assert\Length(max: 40)]
    #[ORM\Column(length: 40, unique: true, options: ['comment' => '码包ID'])]
    private string $packageId;

    #[IndexColumn]
    #[Assert\PositiveOrZero]
    #[Assert\Length(max: 13)]
    #[ORM\Column(type: Types::DECIMAL, precision: 10, scale: 2, nullable: true, options: ['comment' => '面值'])]
    private ?string $parValue = null;

    #[Assert\Positive]
    #[ORM\Column(options: ['comment' => '数量'])]
    private int $quantity;

    #[Assert\DateTime]
    #[ORM\Column(type: Types::DATETIME_IMMUTABLE, nullable: true, options: ['comment' => '卡有效起始时间'])]
    private ?\DateTimeImmutable $startTime = null;

    #[Assert\DateTime]
    #[ORM\Column(type: Types::DATETIME_IMMUTABLE, nullable: true, options: ['comment' => '卡有效截止时间'])]
    private ?\DateTimeImmutable $expireTime = null;

    #[Assert\PositiveOrZero]
    #[ORM\Column(nullable: true, options: ['comment' => '余额有效期（天）'])]
    private ?int $expireDays = null;

    #[Assert\DateTime]
    #[ORM\Column(type: Types::DATETIME_IMMUTABLE, nullable: true, options: ['comment' => '最大有效时间'])]
    private ?\DateTimeImmutable $maxValidTime = null;

    #[Assert\Choice(callback: [PrepaidCardType::class, 'cases'])]
    #[ORM\Column(length: 20, enumType: PrepaidCardType::class, options: ['comment' => '类型'])]
    private PrepaidCardType $type;

    #[Assert\Choice(callback: [PrepaidCardExpireType::class, 'cases'])]
    #[ORM\Column(type: Types::SMALLINT, enumType: PrepaidCardExpireType::class, options: ['comment' => '类型'])]
    private PrepaidCardExpireType $expireType;

    #[Assert\PositiveOrZero]
    #[ORM\Column(type: Types::INTEGER, nullable: true, options: ['default' => 0, 'comment' => '天数'])]
    private int $expireNum;

    #[Assert\Length(max: 500)]
    #[Assert\Url]
    #[ORM\Column(length: 500, nullable: true, options: ['comment' => '缩略图'])]
    private ?string $thumbUrl = null;

    /**
     * @var Collection<int, Card>
     */
    #[ORM\OneToMany(mappedBy: 'package', targetEntity: Card::class)]
    private Collection $cards;

    #[IndexColumn]
    #[TrackColumn]
    #[Assert\Type(type: 'bool')]
    #[ORM\Column(type: Types::BOOLEAN, nullable: true, options: ['comment' => '有效', 'default' => 0])]
    private ?bool $valid = false;

    public function __construct()
    {
        $this->cards = new ArrayCollection();
    }

    public function isValid(): ?bool
    {
        return $this->valid;
    }

    public function setValid(?bool $valid): void
    {
        $this->valid = $valid;
    }

    public function getCampaign(): ?Campaign
    {
        return $this->campaign;
    }

    public function setCampaign(?Campaign $campaign): void
    {
        $this->campaign = $campaign;
    }

    public function getPackageId(): string
    {
        return $this->packageId;
    }

    public function setPackageId(string $packageId): void
    {
        $this->packageId = $packageId;
    }

    public function getParValue(): ?string
    {
        return $this->parValue;
    }

    public function setParValue(?string $parValue): void
    {
        $this->parValue = $parValue;
    }

    public function getQuantity(): ?int
    {
        return $this->quantity;
    }

    public function setQuantity(int $quantity): void
    {
        $this->quantity = $quantity;
    }

    public function getType(): PrepaidCardType
    {
        return $this->type;
    }

    public function setType(PrepaidCardType $type): void
    {
        $this->type = $type;
    }

    public function getStartTime(): ?\DateTimeImmutable
    {
        return $this->startTime;
    }

    public function setStartTime(?\DateTimeImmutable $startTime): void
    {
        $this->startTime = $startTime;
    }

    public function getExpireTime(): ?\DateTimeImmutable
    {
        return $this->expireTime;
    }

    public function setExpireTime(?\DateTimeImmutable $expireTime): void
    {
        $this->expireTime = $expireTime;
    }

    public function getExpireDays(): ?int
    {
        return $this->expireDays;
    }

    public function setExpireDays(?int $expireDays): void
    {
        $this->expireDays = $expireDays;
    }

    public function getMaxValidTime(): ?\DateTimeImmutable
    {
        return $this->maxValidTime;
    }

    public function setMaxValidTime(?\DateTimeImmutable $maxValidTime): void
    {
        $this->maxValidTime = $maxValidTime;
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

    public function addCard(Card $card): void
    {
        if (!$this->cards->contains($card)) {
            $this->cards->add($card);
            $card->setPackage($this);
        }
    }

    public function removeCard(Card $card): void
    {
        if ($this->cards->removeElement($card)) {
            // set the owning side to null (unless already changed)
            if ($card->getPackage() === $this) {
                $card->setPackage(null);
            }
        }
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
    }

    /**
     * @return array<string, mixed>
     */
    public function retrieveApiArray(): array
    {
        return [
            'id' => $this->getId(),
            'parValue' => $this->getParValue(),
            'thumbUrl' => $this->getThumbUrl(),
        ];
    }

    /**
     * @return array<string, mixed>
     */
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
            'type' => $this->getType()->value,
            'thumbUrl' => $this->getThumbUrl(),
            'expireNum' => $this->getExpireNum(),
            'expireType' => $this->getExpireType()->value,
        ];
    }

    public function __toString(): string
    {
        return (string) $this->getId();
    }
}
