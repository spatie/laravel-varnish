# Changelog

All notable changes to `laravel-varnish` will be documented in this file

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
