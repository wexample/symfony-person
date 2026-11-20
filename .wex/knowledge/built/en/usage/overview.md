## Finding or creating

```php
$person = $this->people->findOrCreateByIdentifier('wikidata', 'Q3052772', 'Emmanuel Macron');
$person = $this->personRepository->findOneByIdentifier('an', '720430');
$person = $this->personRepository->findOneBySlug('emmanuel-macron');
$people = $this->people->findNamesakes('Eléonore Caroit');   // [Éléonore Caroit]
```

`PersonService::findNamesakes()` is for a source that names people without identifying them: the people whose name reads the same, accents, case and punctuation aside — compared through their slugs. One namesake is the person the source means; several leave it unsaid. Give the person found the source's own identifier, so that the next import meets them by it.

`PersonRepository::findBySlugContaining('melenchon', 8)` finds by a fragment of the address — lowercase, without accents, so a fragment slugged the same way matches whatever the name's accents —, those it opens first: what a search box asks.

`PersonService::findOrCreateByIdentifier()` creates the person with the identifier the first time, with a unique slug (`jean-jaures`, then `jean-jaures-2`); `rename()` gives a new name and the address that goes with it. Nothing is flushed: the caller decides when — two people of the same name created in one batch must be flushed in between, the slugs being read from the database.

## Portrait

```php
$person->setPortrait($media);   // a symfony-media Media
```

A media of the library, its credit — author, license, source — with it when someone else took the picture; the relation empties itself when the media is deleted.
