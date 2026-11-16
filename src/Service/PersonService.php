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
     * `jean-jaures`, then `jean-jaures-2` for a second one of that name.
     *
     * Read from the database: two people of the same name created in one batch
     * must be flushed in between, or they would get the same slug.
     */
    public function createUniqueSlug(string $displayName): string
    {
        $base = $this->slugger->slug($displayName)->lower()->toString() ?: 'person';
        $taken = array_flip($this->personRepository->findSlugsStartingWith($base));

        if (! isset($taken[$base])) {
            return $base;
        }

        for ($suffix = 2; isset($taken[$base.'-'.$suffix]); ++$suffix) {
        }

        return $base.'-'.$suffix;
    }
}
