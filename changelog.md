# Changes in Filesystem v1.0.3
All notables changes of the Filesystem releases are documented in this file.

## [Unreleased]
### Added
- Nothing to see here.

### Changed
- Moved from using live S3 connection to an emulated one.

## [1.0.4] - 2016-11-28
### Added
- Nothing to see here.

### Changed
- S3Driver, changed PutObject command to Upload command which will automagically
 use multipart upload where applicable.

## [1.0.3] - 2016-11-24
### Added
- Create ChangeLog 

### Changed
- Added optional 'AES256' encryption parameter to S3Driver