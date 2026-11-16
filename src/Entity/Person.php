<?php

namespace Wexample\SymfonyPerson\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Stringable;
use Wexample\SymfonyHelpers\Entity\AbstractEntity;
use Wexample\SymfonyHelpers\Entity\Traits\HasDateCreatedTrait;
use Wexample\SymfonyPerson\Repository\PersonRepository;

/**
 * Someone as the public knows them — a member of parliament, a minister, an
 * author —, once, however many sources name them. Each source's own reference to
 * them is a PersonIdentifier: that is how an import finds the same person again
 * instead of creating a second one.
 */
#[ORM\Entity(repositoryClass: PersonRepository::class)]
#[ORM\Table(name: 'person')]
#[ORM\Index(columns: ['family_name'])]
class Person extends AbstractEntity implements Stringable
{
    use HasDateCreatedTrait;

    /**
     * How they are named on a page: « Jean Jaurès ».
     */
    #[ORM\Column(type: Types::STRING, length: 255)]
    protected string $displayName;

    #[ORM\Column(type: Types::STRING, length: 255, nullable: true)]
    protected ?string $givenName = null;

    /**
     * What they are sorted by.
     */
    #[ORM\Column(type: Types::STRING, length: 255, nullable: true)]
    protected ?string $familyName = null;

    /**
     * Their address on a site, unique: two people of the same name are told apart
     * by a suffix (PersonService).
     */
    #[ORM\Column(type: Types::STRING, length: 255, unique: true)]
    protected string $slug;

    /**
     * A line saying who they are, as a reader would need it.
     */
    #[ORM\Column(type: Types::TEXT, nullable: true)]
    protected ?string $description = null;

    /**
     * @var Collection<int, PersonIdentifier>
     */
    #[ORM\OneToMany(mappedBy: 'person', targetEntity: PersonIdentifier::class, cascade: ['persist'], orphanRemoval: true)]
    protected Collection $identifiers;

    public function __construct(
        string $displayName,
        string $slug
    ) {
        parent::__construct();

        $this->displayName = $displayName;
        $this->slug = $slug;
        $this->identifiers = new ArrayCollection();
        $this->setDateCreatedNow();
    }

    public function __toString(): string
    {
        return $this->displayName;
    }

    public function getDisplayName(): string
    {
        return $this->displayName;
    }

    public function setDisplayName(string $displayName): static
    {
        $this->displayName = $displayName;

        return $this;
    }

    public function getGivenName(): ?string
    {
        return $this->givenName;
    }

    public function getFamilyName(): ?string
    {
        return $this->familyName;
    }

    public function setNames(
        ?string $givenName,
        ?string $familyName
    ): static {
        $this->givenName = $givenName;
        $this->familyName = $familyName;

        return $this;
    }

    public function getSlug(): string
    {
        return $this->slug;
    }

    /**
     * Their address changes with it: what linked to the former one no longer finds
     * them. Made unique by PersonService.
     */
    public function setSlug(string $slug): static
    {
        $this->slug = $slug;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): static
    {
        $this->description = $description;

        return $this;
    }

    /**
     * @return Collection<int, PersonIdentifier>
     */
    public function getIdentifiers(): Collection
    {
        return $this->identifiers;
    }

    public function getIdentifier(string $scheme): ?string
    {
        foreach ($this->identifiers as $identifier) {
            if ($scheme === $identifier->getScheme()) {
                return $identifier->getValue();
            }
        }

        return null;
    }

    /**
     * Kept in step by PersonIdentifier's constructor, the owning side.
     *
     * @internal
     */
    public function addIdentifier(PersonIdentifier $identifier): void
    {
        $this->identifiers->add($identifier);
    }
}
