<?php

namespace Wexample\SymfonyPerson\Service;

use Symfony\Component\String\Slugger\SluggerInterface;
use Wexample\SymfonyPerson\Entity\Person;
use Wexample\SymfonyPerson\Repository\PersonIdentifierRepository;
use Wexample\SymfonyPerson\Repository\PersonRepository;

/**
 * Finds a person by what a source calls them, or creates them the first time,
 * their identifier with them. Nothing is flushed: the caller decides when.
 */
class PersonService
{
    public function __construct(
        private readonly PersonRepository $personRepository,
        private readonly PersonIdentifierRepository $identifierRepository,
        private readonly SluggerInterface $slugger,
    ) {
    }

    /**
     * @param string $displayName used only when the person is created: a name
     *                            met later does not rename them
     */
    public function findOrCreateByIdentifier(
        string $scheme,
        string $value,
        string $displayName
    ): Person {
        $person = $this->personRepository->findOneByIdentifier($scheme, $value);

        if (null !== $person) {
            return $person;
        }

        $person = $this->personRepository->createNewPerson($displayName, $this->createUniqueSlug($displayName));
        $this->identifierRepository->createNewPersonIdentifier($person, $scheme, $value);
        $this->personRepository->save($person, flush: false);

        return $person;
    }

    /**
     * A new name, and the address that goes with it.
     */
    public function rename(
        Person $person,
        string $displayName
    ): void {
        $person
            ->setDisplayName($displayName)
            ->setSlug($this->createUniqueSlug($displayName));
    }

    /**
     * `jean-jaures`, then `jean-jaures-2` for a second one of that name.
     *
     * Read from the database: two people of the same name created in one batch
     * must be flushed in between, or they would get the same slug.
     */
    public function createUniqueSlug(string $displayName): string
    {
        $base = $this->createSlugBase($displayName);
        $taken = array_flip($this->personRepository->findSlugsStartingWith($base));

        if (! isset($taken[$base])) {
            return $base;
        }

        for ($suffix = 2; isset($taken[$base.'-'.$suffix]); ++$suffix) {
        }

        return $base.'-'.$suffix;
    }

    /**
     * The people whose name reads as $displayName, accents, case and punctuation
     * aside — « Eléonore Caroit » for « Éléonore Caroit », « Jean Pierre » for
     * « Jean-Pierre » —: compared through their slugs. For a source that names
     * people without identifying them: one namesake is the person it means,
     * several leave it unsaid. Give the person found the source's own
     * identifier, so that the next import meets them by it.
     *
     * @return list<Person>
     */
    public function findNamesakes(string $displayName): array
    {
        return $this->personRepository->findBySlugBase($this->createSlugBase($displayName));
    }

    private function createSlugBase(string $displayName): string
    {
        return $this->slugger->slug($displayName)->lower()->toString() ?: 'person';
    }
}
