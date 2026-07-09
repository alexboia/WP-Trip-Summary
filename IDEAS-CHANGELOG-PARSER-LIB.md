# Changelog Parser Library Ideas

## Positioning

This library should stay small, but it is not too slim to publish as a standalone Composer package if it has a clear contract:

- extract changelog information from known text formats;
- expose structured changelog data through stable value objects;
- keep WordPress/plugin-specific integration outside the core parser;
- make caching optional and explicit, preferably through a small cache interface or decorator.

The current `Abp01_ReadmeChangelogExtractor` is a good extraction candidate, but the public package should use a generic namespace and class naming scheme rather than project-specific prefixes.

Possible package names:

- `alexboia/wp-readme-changelog`
- `alexboia/markdown-changelog`
- `alexboia/changelog-parser`

Possible namespace:

```php
AlexBoia\ChangelogParser
```

## Core Boundary

The reusable package should know how to parse text and return structured changelog data.

It should not know about:

- WordPress admin screens;
- plugin UI data sources;
- project-specific storage;
- application-specific cache locations;
- `changeLogDataSource` implementation details.

The host project can keep `changeLogDataSource` as an adapter/client that consumes the library.

Suggested public model:

```php
$changelog = $parser->parse($contents);

$latest = $changelog->latestRelease();
$version = $latest->version();
$date = $latest->date();
$changes = $latest->changes();
```

## Supported Formats

### 1. WordPress `readme.txt`

This is the current specific use case and should remain first-class.

Typical changelog section:

```text
== Changelog ==

= 1.2.0 =
* Added support for Markdown changelogs.
* Fixed stale cache invalidation.

= 1.1.0 =
* Improved parser behavior.
```

The parser should support:

- locating the `== Changelog ==` section;
- extracting version blocks;
- preserving bullet text;
- returning empty but valid results when the changelog section is missing or empty;
- tolerating common WordPress readme formatting variations.

### 2. Keep a Changelog-style Markdown

This is the best next target for generic GitHub repository support.

Typical format:

```md
# Changelog

## [1.2.0] - 2026-07-08

### Added
- Add Markdown changelog support.

### Changed
- Improve README parser.

### Fixed
- Fix cache invalidation.
```

The parser should initially recognize:

- `CHANGELOG.md` content;
- changelog sections embedded in `README.md`;
- version headings such as `## [1.2.3] - 2026-07-08`, `## 1.2.3`, `## v1.2.3`, and `### 1.2.3`;
- common groups such as `Added`, `Changed`, `Deprecated`, `Removed`, `Fixed`, and `Security`;
- an optional `Unreleased` section.

### 3. GitHub Release Notes-style Markdown

This can be a later extractor because GitHub release notes are often generated outside the repository and are less consistent as source files.

Common shape:

```md
## What's Changed
* Fix cache invalidation by @user in #123
* Add Markdown parsing by @user in #124

**Full Changelog**: v1.1.0...v1.2.0
```

Possible support:

- parse `What's Changed` sections;
- preserve pull request and contributor references as plain text initially;
- optionally expose PR numbers and comparison links later.

### 4. Conventional Changelog Output

This is useful mostly for projects that generate changelogs from Conventional Commits.

Common groups:

- `Features`
- `Bug Fixes`
- `Performance Improvements`
- `BREAKING CHANGES`

This should be treated as a later compatibility layer, not the first generic Markdown implementation.

## Recommended Roadmap

1. Extract the current WordPress `readme.txt` parser into a clean Composer package.
2. Rename project-specific classes and move them under a PSR-4 namespace.
3. Keep `changeLogDataSource` in the host project as an adapter around the package.
4. Add a generic Markdown changelog parser focused on Keep a Changelog-style files.
5. Add tolerant parsing for common GitHub-ish release notes.
6. Consider Conventional Changelog output only after the core data model is stable.

## Publishing Notes

The package is worth publishing if it includes:

- a focused `composer.json`;
- PSR-4 autoloading;
- a small README with input/output examples;
- tests for missing changelogs, empty changelogs, multiple versions, malformed version headings, and grouped Markdown entries;
- an explicit license;
- semantic versioning, starting with `v0.1.0` if the API still needs room to settle.

The value of the package is not its size. The value is that it turns messy changelog text into predictable structured data.
