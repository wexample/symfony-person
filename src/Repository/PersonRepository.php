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

    /**
     * Everyone, by name, one page of them at a time.
     *
     * @return list<Person>
     */
    public function findByName(
        int $limit = 50,
        int $offset = 0
    ): array {
        return $this->createQueryBuilder('person')
            ->orderBy('person.displayName', 'ASC')
            ->setFirstResult($offset)
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }

    /**
     * The people whose address holds $fragment — a slug itself, lowercase and
     * without accents, so « melenchon » finds « Jean-Luc Mélenchon » —, those it
     * opens first, then by name.
     *
     * @return list<Person>
     */
    public function findBySlugContaining(
        string $fragment,
        int $limit
    ): array {
        $escaped = addcslashes($fragment, '%_');

        return $this->createQueryBuilder('person')
            ->addSelect('CASE WHEN person.slug LIKE :prefix THEN 0 ELSE 1 END AS HIDDEN inside')
            ->where('person.slug LIKE :fragment')
            ->setParameter('fragment', '%'.$escaped.'%')
            ->setParameter('prefix', $escaped.'%')
            ->orderBy('inside', 'ASC')
            ->addOrderBy('person.displayName', 'ASC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
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
     * The people whose slug was made from $base — `jean-jaures`, `jean-jaures-2`… —:
     * those whose names read the same.
     *
     * @return list<Person>
     */
    public function findBySlugBase(string $base): array
    {
        $pattern = '/^'.preg_quote($base, '/').'(-\d+)?$/';

        return array_values(array_filter(
            $this->createQueryBuilder('person')
                ->where('person.slug = :base OR person.slug LIKE :prefix')
                ->setParameter('base', $base)
                ->setParameter('prefix', addcslashes($base, '%_').'-%')
                ->getQuery()
                ->getResult(),
            static fn (Person $person): bool => 1 === preg_match($pattern, $person->getSlug())
        ));
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
