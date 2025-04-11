# JsonStore

A Laravel-friendly, dot-accessible JSON store with automatic saving, file locking, TTL caching, and array utilities. Perfect for user settings, feature flags, simple data persistence, and config snapshots — all stored in clean JSON.

---

## ✨ Features

- Dot notation access (`\$store->get('profile.name')`)
- Auto-saving on destruct or manual control
- Safe concurrency with file-level locking (`withLock()`)
- Lightweight TTL caching with `remember()`
- Insert/remove from arrays with helpers
- Support for object or array responses
- Fully customizable file paths (e.g., per user or tenant)

---

## 📦 Installation

```bash
composer require flexible-labs/json-store
```

For local development using symlinked packages:

```json
"repositories": [
    {
        "type": "path",
        "url": "../json-store",
        "options": {
            "symlink": true
        }
    }
]
```

---

## 🚀 Usage Examples

```php
use FlexibleLabs\JsonStore\JsonStore;

// Create a per-user store with default structure
\$store = new JsonStore("users/{$user->id}.json", [
    'settings' => [
        'theme' => 'light',
        'notifications' => [
            'email' => true,
            'sms' => false,
        ],
    ]
]);

// Set a single key
\$store->set('profile.name', 'Sulieman Shahbari');

// Set multiple keys at once
\$store->set([
    'settings.theme' => 'dark',
    'settings.language' => 'en',
]);

// Deeply nested key
\$store->set('account.preferences.editor.mode', 'vim');

// Conditionally set if not already set
\$store->set('profile.created_at', now());

// Insert into array
\$store->insert('tags', 'laravel');
\$store->insert('tags', 'open-source');

// Remove from array
\$store->deleteFrom('tags', 'open-source');

// Lock file to prevent race conditions
\$store->withLock(function () use (\$store) {
    \$count = \$store->get('analytics.visits', 0);
    \$store->set('analytics.visits', \$count + 1);
});

// TTL-based cache
\$data = \$store->remember('external.api.cache', 3600, fn () => Http::get('https://api.example.com')->json());

// Save manually or rely on auto-save
\$store->save();
```

---

## 🧪 API Reference

| Method             | Description                                      | Example |
|--------------------|--------------------------------------------------|---------|
| `get(key)`        | Get a value or all data                         | `$store->get('profile.name')` |
| `set(key, value)`| Set one or multiple values                     | `$store->set('profile.age', 30)` |
| `delete(key)`     | Remove a key                                    | `$store->delete('profile.age')` |
| `has(key)`        | Check if a key exists                          | `$store->has('profile.name')` |
| `getOrSet()`        | Retrieve or set a fallback                     | `$store->getOrSet('settings.theme', 'dark')` |
| `remember()`        | TTL-based cache for a key                      | `$store->remember('cache.key', 60, fn () => 'value')` |
| `insert()`          | Append a value to an array                    | `$store->insert('tags', 'laravel')` |
| `deleteFrom()`      | Remove a value from an array                  | `$store->deleteFrom('tags', 'laravel')` |
| `withLock()`        | Wrap logic in a file lock                     | `$store->withLock(fn () => ...)` |
| `save()`            | Save manually to disk                         | `$store->save()` |

## 🔐 Locking Example

```php
\$store->withLock(function () use (\$store) {
    \$votes = \$store->get('votes', 0);
    \$store->set('votes', \$votes + 1);
});
```

---

## 🔁 Remember with TTL

```php
\$userData = \$store->remember('github.user.123', 300, function () {
    return Http::get('https://api.github.com/users/123')->json();
});
```

---

## 📘 Method Examples

### 🔹 get()
```php
$value = $store->get('user.email');
$all = $store->get();
$object = $store->get(null, null, true); // as object
```

### 🔹 set()
```php
$store->set('profile.name', 'Sulieman');
$store->set([
    'settings.theme' => 'dark',
    'settings.language' => 'en'
]);
```

### 🔹 delete()
```php
$store->delete('settings.theme');
```

### 🔹 has()
```php
if ($store->has('settings.language')) {
    // Do something
}
```

### 🔹 getOrSet()
```php
$theme = $store->getOrSet('settings.theme', 'light');
```

### 🔹 remember()
```php
$data = $store->remember('external.api.cache', 600, function () {
    return Http::get('https://example.com')->json();
});
```

### 🔹 insert()
```php
$store->insert('tags', 'laravel');
```

### 🔹 deleteFrom()
```php
$store->deleteFrom('tags', 'laravel');
```

### 🔹 withLock()
```php
$store->withLock(function () use ($store) {
    $store->set('key', 'safe value');
});
```

### 🔹 save()
```php
$store->save();
```

## 📄 License

MIT © Suleiman Shahbari / Flexible Labs