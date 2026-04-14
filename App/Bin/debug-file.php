<?php

declare(strict_types=1);

define('ROOT_DIR', realpath(dirname(__DIR__, 2)));
require_once ROOT_DIR . '/vendor/autoload.php';

try {
    $app = new App();
    $app->boot();

    $filepath = ROOT_DIR . '/storage/uploads/images/card7_690a5c50880ca.png';

    echo '🔍 Debugging file: ' . basename($filepath) . "\n";
    echo "==================================\n\n";

    if (!file_exists($filepath)) {
        echo "❌ File doesn't exist!\n";
        exit(1);
    }

    echo "File exists: YES\n";
    echo 'Size: ' . filesize($filepath) . " bytes\n";
    echo 'Modified: ' . date('Y-m-d H:i:s', filemtime($filepath)) . "\n";
    echo 'Age: ' . round((time() - filemtime($filepath)) / 86400, 1) . " days\n";

    // Check if it's in database
    require_once ROOT_DIR . '/Framework/Components/Files/src/FileUpload/CleanUp/DatabaseFilePathService.class.php';

    $productModel = $app->get(ProductModel::class);
    $galleryModel = $app->get(ProductImageGalleryModel::class);
    $databaseFilePaths = new DatabaseFilePathService($productModel, $galleryModel, null);

    $validPaths = $databaseFilePaths->getValidFilePaths();

    echo "\n📊 Database check:\n";
    echo 'Valid paths in database: ' . count($validPaths) . "\n";

    // Check if this file is in database
    $relativePath = 'images/card7_690a5c50880ca.png';
    $isInDatabase = in_array($relativePath, $validPaths, true);

    echo 'File in database: ' . ($isInDatabase ? 'YES ✅' : 'NO ❌') . "\n";

    if ($isInDatabase) {
        echo "This file is VALID and should NOT be deleted!\n";
    } else {
        echo "This file is an ORPHAN and should be deleted!\n";

        // Check why it wasn't deleted
        echo "\n❓ Why wasn't it deleted?\n";
        echo "------------------------\n";

        // Run orphan finder check
        require_once ROOT_DIR . '/Framework/Components/Files/src/FileUpload/CleanUp/OrphanFileFinderService.class.php';
        $finder = new OrphanFileFinderService($databaseFilePaths, null);
        $orphans = $finder->findOrphanFiles(ROOT_DIR . '/storage/uploads');

        $foundAsOrphan = false;
        foreach ($orphans as $orphan) {
            if ($orphan['path'] === $filepath) {
                $foundAsOrphan = true;
                echo "- Found in orphan list: YES\n";
                echo '- Orphan age: ' . round((time() - $orphan['modified_at']) / 86400, 1) . " days\n";
                break;
            }
        }

        if (!$foundAsOrphan) {
            echo "- Not found in orphan list!\n";
            echo "- This means the finder didn't identify it as an orphan\n";
        }
    }
} catch (Throwable $e) {
    echo '❌ Error: ' . $e->getMessage() . "\n";
}
