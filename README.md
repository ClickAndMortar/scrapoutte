# Scrapoutte

[Goutte](https://github.com/FriendsOfPHP/Goutte) enabled web page scraper.

Scrapoutte will follow all links (excluding external ones) matching a given CSS selector.

## Installation

Use PHAR (see Releases) or install with composer:

```
composer install
```

## Usage

```
php bin/scrapoutte scrape <url> [--selector|-s <css-selector>] [--user-agent|-u <user-agent>]
```

Defaults option values:

* **Selector**: `a` (all links)
* **User Agent**: Chrome 56 macOS Sierra
