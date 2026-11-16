<?php

namespace Wexample\SymfonyPerson\Repository;

use Wexample\SymfonyHelpers\Repository\AbstractRepository;
use Wexample\SymfonyPerson\Entity\Person;
use Wexample\SymfonyPerson\Entity\PersonIdentifier;
use Wexample\SymfonyPerson\Entity\Traits\Manipulator\PersonEntityManipulatorTrait;

/**
 * @method Person|null find($id, $lockMode = null, $lockVersion = null)
 * @method Person      saveNewPerson(string $displayName, string $slug)
 */
class PersonRepository extends AbstractRepository
{
    use PersonEntityManipulatorTrait;

    /**
     * The slug is the caller's to make unique: PersonService does.
     */
    public function createNewPerson(
        string $displayName,
        string $slug
    ): Person {
        return new Person($displayName, $slug);
    }

    public function findOneBySlug(string $slug): ?Person
    {
        return $this->findOneBy(['slug' => $slug]);
    }

    /**
     * The person a source refers to by that value, if met before.
     */
    public function findOneByIdentifier(
        string $scheme,
        string $value
    ): ?Person {
        return $this->createQueryBuilder('person')
            ->innerJoin(PersonIdentifier::class, 'identifier', 'WITH', 'identifier.person = person')
            ->where('identifier.scheme = :scheme')
            ->andWhere('identifier.value = :value')
            ->setParameter('scheme', $scheme)
            ->setParameter('value', $value)
            ->getQuery()
            ->getOneOrNullResult();
    }

    /**
     * @return list<string> the slugs already taken among those starting with $base
     */
    public function findSlugsStartingWith(string $base): array
    {
        return array_column($this->createQueryBuilder('person')
            ->select('person.slug')
            ->where('person.slug = :base OR person.slug LIKE :prefix')
            ->setParameter('base', $base)
            ->setParameter('prefix', addcslashes($base, '%_').'-%')
            ->getQuery()
            ->getScalarResult(), 'slug');
    }
}
