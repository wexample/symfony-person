<?php

namespace Wexample\SymfonyPerson\Repository;

use Wexample\SymfonyHelpers\Repository\AbstractRepository;
use Wexample\SymfonyPerson\Entity\Person;
use Wexample\SymfonyPerson\Entity\PersonIdentifier;
use Wexample\SymfonyPerson\Entity\Traits\Manipulator\PersonIdentifierEntityManipulatorTrait;

/**
 * @method PersonIdentifier|null find($id, $lockMode = null, $lockVersion = null)
 * @method PersonIdentifier      saveNewPersonIdentifier(Person $person, string $scheme, string $value)
 */
class PersonIdentifierRepository extends AbstractRepository
{
    use PersonIdentifierEntityManipulatorTrait;

    public function createNewPersonIdentifier(
        Person $person,
        string $scheme,
        string $value
    ): PersonIdentifier {
        return new PersonIdentifier($person, $scheme, $value);
    }
}
