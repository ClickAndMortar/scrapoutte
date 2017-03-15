# Scrapoutte

[Goutte](https://github.com/FriendsOfPHP/Goutte) enabled web page scraper.

Scrapoutte will follow all links (internal or external) matching a given CSS selector.

## Installation

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
