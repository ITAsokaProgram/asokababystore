<?php
require_once '../../../config.php';
require_once '../../utils/init.php';

try {
    // Create MenuSync instance
    $menuSync = createMenuSync($conn);
    
    // Sync menu from JSON file
    $jsonFile = '../../../menus.json';
    $result = $menuSync->syncFromJson($jsonFile);
    
    // Optional: Get menu statistics
    $totalMenus = $menuSync->getMenuStats();
    echo "\n📊 Total menu di database: $totalMenus\n";
    
} catch (Exception $e) {
    echo "\n❌ FATAL ERROR: " . $e->getMessage() . "\n";
    exit(1);
} catch (Error $e) {
    echo "\n❌ PHP ERROR: " . $e->getMessage() . "\n";
    exit(1);
}
?>