# JsonStore

A **Laravel-friendly, dot-accessible JSON store** designed for simple data persistence, user settings, feature flags, and configuration snapshots. JsonStore offers automatic saving, lazy loading, file locking for safe concurrency, TTL-based caching, and handy array utilities—all using intuitive dot notation.

[![License: MIT](https://img.shields.io/badge/License-MIT-yellow.svg)](LICENSE)
[![Latest Version](https://img.shields.io/packagist/v/flexible-labs/json-store.svg)](https://packagist.org/packages/flexible-labs/json-store)

---

## Table of Contents

- [Overview](#overview)
- [Why Use JsonStore?](#why-use-jsonstore)
- [Installation](#installation)
- [Usage](#usage)
  - [Basic Example](#basic-example)
  - [Advanced Usage](#advanced-usage)
- [API Reference](#api-reference)
  - [Instance Creation](#instance-creation)
  - [File & Storage Methods](#file--storage-methods)
  - [Data Manipulation Methods](#data-manipulation-methods)
  - [Caching and TTL](#caching-and-ttl)
  - [Array Utilities](#array-utilities)
  - [Locking & Concurrency](#locking--concurrency)
- [Configuration](#configuration)
- [Contributing](#contributing)
- [License](#license)
- [Contact](#contact)

---

## Overview

JsonStore is a lightweight solution for managing JSON data in Laravel projects. It integrates directly with Laravel's filesystem and configuration systems to offer a hassle-free experience for:

- **Automatic Data Persistence:** Automatically saves changes to disk, avoiding data loss.
- **Lazy Loading:** Loads file data only when needed.
- **Safe Concurrency:** Uses file locking to ensure data integrity during simultaneous accesses.
- **TTL Caching:** Caches values temporarily with automatic expiration.
- **Array Manipulation:** Provides simple helper methods for array operations like insertion and deletion.

---

## Why Use JsonStore?

- **Simplicity & Readability:** Utilize dot notation to access nested JSON keys, making your code cleaner and easier to read.
- **Performance:** Lazy loading combined with auto-saving reduces unnecessary I/O operations.
- **Data Integrity:** File-level locking prevents race conditions during concurrent writes.
- **Flexibility:** Easily customize storage disks and base paths, making JsonStore adaptable to various project architectures.
- **Versatility:** Perfect for user settings, feature flags, configuration snapshots, and caching responses from APIs.

---

## Installation

Install JsonStore using Composer:

```bash
composer require flexible-labs/json-store
