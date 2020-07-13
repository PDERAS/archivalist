# Archivalist

[![Latest Version on Packagist](https://img.shields.io/packagist/v/pderas/archivalist.svg?style=flat-square)](https://packagist.org/packages/pderas/archivalist)
[![Total Downloads](https://img.shields.io/packagist/dt/pderas/archivalist.svg?style=flat-square)](https://packagist.org/packages/pderas/archivalist)

Archivalist is a minimal package designed to make archiving changes to Laravel models easy.

## Installation

You can install the package via composer:

```bash
composer require pderas/archivalist
```

## Usage

Simply add the `PDERAS\Archivalist\Traits\HasArchives` to any model you wish to archive.
```php
use PDERAS\Archivalist\Traits\HasArchives;
class Post extends Model {
    use HasArchives;
}
```

If you wish certain columns to _always_ be archived, this can be accomplished by adding wither a `archived` property or method to the model

```php
use PDERAS\Archivalist\Traits\HasArchives;
class Post extends Model {
    use HasArchives;

    protected $archived = [
        'updated_at'
    ];

    // Alternatively...
    protected function archived() {
        return [
            'updated_at' => $this->getOriginal('updated_at'),
            'is_archived' => true
        ];
    }
}
```

Archives can be 'rehydrated' into the state of the original model
```php
$user->company = 'Pderas';
$user->save();

$archive = $user->archives()->first(); // => \PDERAS\Archivalist\Models\Archive
$original = $archive->rehydrate(); // => \App\User
```

A Collection with the full history of the model can be acquired using `->getHistory()`

```php
$user->getHistory(); // A user model for every state the user was in
```

Mass assignment is not supported, in which case you must use the following workaround:

```php
//  Do not do
Post::where('status','open')
    ->update(['status','closed']); // This will fail

//  Do this instead
Archivalist::proxy(Post::query())
    ->where('status','open')
    ->update(['status','closed']);
```

## Testing

```bash
composer test
```

## Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information what has changed recently.

## Security

If you discover any security related issues, please email reed.jones@pderas.com instead of using the issue tracker.

## Credits

- [Reed Jones](https://github.com/pderas)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
