<?php

namespace Wexample\SymfonyPerson\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Stringable;
use Wexample\SymfonyHelpers\Entity\AbstractEntity;
use Wexample\SymfonyPerson\Repository\PersonIdentifierRepository;

/**
 * What a source calls a person: its scheme — `an` for the Assemblée nationale's
 * register, `senat`, `wikidata` — and the value it gives them there (`720430`,
 * `Q3052772`). A value belongs to one person only, so meeting it again finds them.
 */
#[ORM\Entity(repositoryClass: PersonIdentifierRepository::class)]
#[ORM\Table(name: 'person_identifier')]
#[ORM\UniqueConstraint(columns: ['scheme', 'value'])]
class PersonIdentifier extends AbstractEntity implements Stringable
{
    #[ORM\ManyToOne(targetEntity: Person::class, inversedBy: 'identifiers')]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    protected Person $person;

    #[ORM\Column(type: Types::STRING, length: 32)]
    protected string $scheme;

    #[ORM\Column(type: Types::STRING, length: 255)]
    protected string $value;

    public function __construct(
        Person $person,
        string $scheme,
        string $value
    ) {
        parent::__construct();

        $this->person = $person;
        $this->scheme = $scheme;
        $this->value = $value;

        $person->addIdentifier($this);
    }

    public function __toString(): string
    {
        return $this->scheme.':'.$this->value;
    }

    public function getPerson(): Person
    {
        return $this->person;
    }

    public function getScheme(): string
    {
        return $this->scheme;
    }

    public function getValue(): string
    {
        return $this->value;
    }
}
