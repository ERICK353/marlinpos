<?php

$dir = __DIR__ . '/app/Filament/Admin';
$newDir = __DIR__ . '/app/Filament/Shop';

if (!file_exists($dir)) {
    echo "Directory not found: $dir\n";
} else {
    // 1. Update all namespaces in app/Filament/Admin
    $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($dir));
    foreach ($iterator as $file) {
        if ($file->isFile() && $file->getExtension() === 'php') {
            $content = file_get_contents($file->getPathname());
            $newContent = str_replace('App\Filament\Admin', 'App\Filament\Shop', $content);
            file_put_contents($file->getPathname(), $newContent);
            echo "Updated namespace in " . $file->getFilename() . "\n";
        }
    }

    // 2. Rename the directory
    rename($dir, $newDir);
    echo "Renamed $dir to $newDir\n";
}

// 3. Update AdminPanelProvider.php
$providerPath = __DIR__ . '/app/Providers/Filament/AdminPanelProvider.php';
$newProviderPath = __DIR__ . '/app/Providers/Filament/ShopPanelProvider.php';

if (file_exists($providerPath)) {
    $content = file_get_contents($providerPath);
    $content = str_replace('App\Filament\Admin', 'App\Filament\Shop', $content);
    $content = str_replace('AdminPanelProvider', 'ShopPanelProvider', $content);
    $content = str_replace("id('admin')", "id('shop')", $content);
    $content = str_replace("path('admin')", "path('shop')", $content);
    file_put_contents($providerPath, $content);
    
    // Rename provider file
    rename($providerPath, $newProviderPath);
    echo "Renamed AdminPanelProvider.php to ShopPanelProvider.php\n";
}

// 4. Update bootstrap/providers.php
$bootstrapPath = __DIR__ . '/bootstrap/providers.php';
if (file_exists($bootstrapPath)) {
    $content = file_get_contents($bootstrapPath);
    $content = str_replace('AdminPanelProvider::class', 'ShopPanelProvider::class', $content);
    file_put_contents($bootstrapPath, $content);
    echo "Updated bootstrap/providers.php\n";
}

echo "Done!\n";
