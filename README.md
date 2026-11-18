# symfony-person

Version: 3.0.0

People as the public knows them: one entry per person, however many sources name them, found again by the identifiers those sources give them

## Table of Contents

- [Introduction](#introduction)

`symfony-person` keeps people as the public knows them — a member of parliament, a minister, an author —, once, however many sources name them: a `Person` has a name, an address of its own (`slug`), a description, a portrait, and one `PersonIdentifier` per source that names them, by which an import finds the same person again instead of creating a second one.
