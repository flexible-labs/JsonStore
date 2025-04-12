<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Default Filesystem Disk
    |--------------------------------------------------------------------------
    |
    | This value determines which filesystem disk JsonStore will use.
    | It can be "local", "public", or any custom disk defined in `config/filesystems.php`.
    |
    */

    'disk' => env('JSONSTORE_DISK', 'local'),

    /*
    |--------------------------------------------------------------------------
    | Default Base Directory
    |--------------------------------------------------------------------------
    |
    | All JsonStore files will be stored under this base directory.
    | You can override this when creating a new instance.
    |
    */

    'base_path' => env('JSONSTORE_BASE', ''),
];
