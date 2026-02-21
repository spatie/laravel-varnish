# Changelog

All notable changes to `laravel-varnish` will be documented in this file

## 2.10.3 - 2026-02-21

Add Laravel 13 support

## 2.10.2 - 2025-02-21

### What's Changed

* Laravel 12.x Compatibility by @laravel-shift in https://github.com/spatie/laravel-varnish/pull/72

**Full Changelog**: https://github.com/spatie/laravel-varnish/compare/2.10.1...2.10.2

## 2.10.1 - 2025-02-14

### What's Changed

* use setSharedMaxAge function by @indykoning in https://github.com/spatie/laravel-varnish/pull/71

### New Contributors

* @indykoning made their first contribution in https://github.com/spatie/laravel-varnish/pull/71

**Full Changelog**: https://github.com/spatie/laravel-varnish/compare/2.10.0...2.10.1

## 2.10.0 - 2024-02-29

### What's Changed

* Laravel 11.x Compatibility by @laravel-shift in https://github.com/spatie/laravel-varnish/pull/69

**Full Changelog**: https://github.com/spatie/laravel-varnish/compare/2.9.2...2.10.0

## 2.9.2 - 2023-01-31

### What's Changed

- update: broken link for varnish setup blog post by @abhij89 in https://github.com/spatie/laravel-varnish/pull/61
- Refactor tests to Pest by @alexmanase in https://github.com/spatie/laravel-varnish/pull/62
- Laravel 10.x Compatibility by @laravel-shift in https://github.com/spatie/laravel-varnish/pull/64

### New Contributors

- @abhij89 made their first contribution in https://github.com/spatie/laravel-varnish/pull/61
- @alexmanase made their first contribution in https://github.com/spatie/laravel-varnish/pull/62
- @laravel-shift made their first contribution in https://github.com/spatie/laravel-varnish/pull/64

**Full Changelog**: https://github.com/spatie/laravel-varnish/compare/2.9.1...2.9.2

## 2.9.1 - 2022-09-06

### What's Changed

- Changed initialization of Symfony\Component\Process\Process class  by @NightfallSD in https://github.com/spatie/laravel-varnish/pull/60

### New Contributors

- @NightfallSD made their first contribution in https://github.com/spatie/laravel-varnish/pull/60

**Full Changelog**: https://github.com/spatie/laravel-varnish/compare/2.9.0...2.9.1

## 2.9.0 - 2022-02-10

- allow Laravel 9

## 2.8.3 - 2022-01-28

## What's Changed

- Add PHP 8.1 Support by @patinthehat in https://github.com/spatie/laravel-varnish/pull/54
- s-maxage is the correct header by @dvershinin in https://github.com/spatie/laravel-varnish/pull/56

## New Contributors

- @patinthehat made their first contribution in https://github.com/spatie/laravel-varnish/pull/54
- @dvershinin made their first contribution in https://github.com/spatie/laravel-varnish/pull/56

**Full Changelog**: https://github.com/spatie/laravel-varnish/compare/2.8.2...2.8.3

## 2.8.2 - 2021-03-04

- add compatibility for PHP 8 (#52)

## 2.8.1 - 2020-09-09

- Support Laravel 8

## 2.8.0 - 2020-03-11

- add logic to pass in a URL to the varnish:flush command to purge a host + url with a regex (#42)

## 2.7.0 - 2020-03-03

- add support for Laravel 7

## 2.3.0 - 2019-09-04

- add support for Laravel 6

## 2.2.0 - 2019-02-27

- drop support for Laravel 5.7 and below
- drop support for PHP 7.1 and below

## 2.1.2 - 2019-02-27

- add support for Laravel 5.8

## 2.1.1 - 2018-08-29

- add support for Laravel 5.7

## 2.1.0 - 2018-08-13

- add support for Lumen

## 2.0.2 - 2017-02-08

- add support for L5.6

## 2.0.1 - 2017-09-09

- make it more clear in the config file that `hosts` can accept multiple values

## 2.0.0 - 2017-08-31

- add support for Laravel 5.5, dropped support for older versions of the framework
- renamed config file from `laravel-varnish` to `varnish`

## 1.0.1 - 2017-01-05

- fix bug: call to undefined function `varnish()`

## 1.0.0 - 2017-01-04

- initial release
