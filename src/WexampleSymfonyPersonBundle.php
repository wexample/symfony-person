<?php

namespace Wexample\SymfonyPerson;

use Wexample\SymfonyHelpers\Class\AbstractBundle;

/**
 * People kept once each, whatever names them: an application points at a person
 * with a plain relation, and an import finds again the one it met before through
 * the identifier its source gives them.
 */
class WexampleSymfonyPersonBundle extends AbstractBundle
{
}
