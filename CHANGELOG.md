# Changelog

All notable changes to `archivalist` will be documented in this file

## 0.2.0 - 2020-07-10

### Added
- added `\PDERAS\Archivalist\Archivalist` facade
- added `\PDERAS\Archivalist\Archivalist::proxy($query)` to proxy & archive mass updates
- added `PDERAS\Archivalist\Models\Archive@getTableColumns`
- added `PDERAS\Archivalist\Models\Archive@getRelatedModel`
- added `PDERAS\Archivalist\Models\Archive@getRelatedClass`
- added `PDERAS\Archivalist\Models\Archive@getRelatedId`
- added `PDERAS\Archivalist\Models\Archive@getRelatedId`
- added `PDERAS\Archivalist\Models\Archive@getCurrentData`
- added `PDERAS\Archivalist\Models\Archive@getArchivedData`

### Changed
- `PDERAS\Archivalist\Models\Archive@rehydrate` now requires a parent model to be supplied, which the rehydration will be based off

### Fixed
- hydration is now based on the previous archive, and not the current model value

### Security
- `$hidden` attributes will no longer be archived

## 0.1.0 - 2020-07-08

- initial release
