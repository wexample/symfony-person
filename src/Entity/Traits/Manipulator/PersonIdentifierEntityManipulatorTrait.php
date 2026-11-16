<?php

namespace Wexample\SymfonyPerson\Entity\Traits\Manipulator;

use Wexample\SymfonyHelpers\Entity\Traits\Manipulator\EntityManipulatorTrait;
use Wexample\SymfonyPerson\Entity\PersonIdentifier;

trait PersonIdentifierEntityManipulatorTrait
{
    use EntityManipulatorTrait;

    public static function getEntityClassName(): string
    {
        return PersonIdentifier::class;
    }
}
