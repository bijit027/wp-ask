<?php
/**
 * Fix namespace inconsistencies from PollQuest to PollQuest
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$rootDir = __DIR__ . '/..';
$includesDir = $rootDir . '/includes';

echo "🔧 Fixing namespace inconsistencies...\n";

$iterator = new RecursiveIteratorIterator(
    new RecursiveDirectoryIterator($includesDir)
);

$count = 0;
foreach ($iterator as $file) {
    if ($file->isFile() && $file->getExtension() === 'php') {
        $content = file_get_contents($file->getPathname());
        $original = $content;
        
        // Replace namespace PollQuest with namespace PollQuest
        $content = preg_replace('/^namespace PollQuest\b/m', 'namespace PollQuest', $content);
        
        // Replace use PollQuest with use PollQuest
        $content = preg_replace('/^use PollQuest\\\\/m', 'use PollQuest\\', $content);
        
        if ($content !== $original) {
            file_put_contents($file->getPathname(), $content);
            $count++;
            echo "✓ Fixed: " . esc_html( $file->getFilename() ) . "\n";
        }
    }
}

echo "\n✅ Fixed " . esc_html( (string) $count ) . " files\n";
