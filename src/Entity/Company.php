<?php

namespace PrepaidCardBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use PrepaidCardBundle\Repository\CompanyRepository;
use Symfony\Component\Serializer\Attribute\Ignore;
use Tourze\Arrayable\AdminArrayInterface;
use Tourze\DoctrineSnowflakeBundle\Service\SnowflakeIdGenerator;
use Tourze\DoctrineTimestampBundle\Traits\TimestampableAware;
use Tourze\DoctrineTrackBundle\Attribute\TrackColumn;
use Tourze\DoctrineUserBundle\Traits\BlameableAware;
use Tourze\EnumExtra\Itemable;

#[ORM\Table(name: 'ims_prepaid_company', options: ['comment' => '预付卡公司'])]
#[ORM\Entity(repositoryClass: CompanyRepository::class)]
class Company implements AdminArrayInterface, \Stringable, Itemable
{
    use TimestampableAware;
    use BlameableAware;
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'CUSTOM')]
    #[ORM\CustomIdGenerator(SnowflakeIdGenerator::class)]
    #[ORM\Column(type: Types::BIGINT, nullable: false, options: ['comment' => 'ID'])]
    private ?string $id = null;

    #[TrackColumn]
    #[ORM\Column(length: 255, unique: true, options: ['comment' => '公司名称'])]
    private string $title;

    /**
     * @var Collection<int, Campaign>
     */
    #[Ignore]
    #[ORM\OneToMany(mappedBy: 'company', targetEntity: Campaign::class)]
    private Collection $campaigns;

    /**
     * @var Collection<int, Card>
     */
    #[Ignore]
    #[ORM\OneToMany(mappedBy: 'company', targetEntity: Card::class)]
    private Collection $cards;


    public function __construct()
    {
        $this->campaigns = new ArrayCollection();
        $this->cards = new ArrayCollection();
    }

    public function __toString(): string
    {
        if ($this->getId() === null) {
            return '';
        }

        return $this->getTitle();
    }

    public function getId(): ?string
    {
        return $this->id;
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

    /**
     * @return Collection<int, Campaign>
     */
    public function getCampaigns(): Collection
    {
        return $this->campaigns;
    }

    public function addCampaign(Campaign $campaign): static
    {
        if (!$this->campaigns->contains($campaign)) {
            $this->campaigns->add($campaign);
            $campaign->setCompany($this);
        }

        return $this;
    }

    public function removeCampaign(Campaign $campaign): static
    {
        if ($this->campaigns->removeElement($campaign)) {
            // set the owning side to null (unless already changed)
            if ($campaign->getCompany() === $this) {
                $campaign->setCompany(null);
            }
        }

        return $this;
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
            $card->setCompany($this);
        }

        return $this;
    }

    public function removeCard(Card $card): static
    {
        if ($this->cards->removeElement($card)) {
            // set the owning side to null (unless already changed)
            if ($card->getCompany() === $this) {
                $card->setCompany(null);
            }
        }

        return $this;
    }public function retrieveAdminArray(): array
    {
        return [
            'id' => $this->getId(),
            'createTime' => $this->getCreateTime()?->format('Y-m-d H:i:s'),
            'updateTime' => $this->getUpdateTime()?->format('Y-m-d H:i:s'),
            'title' => $this->getTitle(),
        ];
    }

    public function toSelectItem(): array
    {
        return [
            'label' => $this->getTitle(),
            'text' => $this->getTitle(),
            'value' => $this->getId(),
            'name' => $this->getTitle(),
        ];
    }
}
