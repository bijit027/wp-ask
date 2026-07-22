<?php
/**
 * Fix namespace inconsistencies from WPAsk to InsightPulse
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
        
        // Replace namespace WPAsk with namespace InsightPulse
        $content = preg_replace('/^namespace WPask\b/m', 'namespace InsightPulse', $content);
        
        // Replace use WPAsk with use InsightPulse
        $content = preg_replace('/^use WPask\\\\/m', 'use InsightPulse\\', $content);
        
        if ($content !== $original) {
            file_put_contents($file->getPathname(), $content);
            $count++;
            echo "✓ Fixed: " . esc_html( $file->getFilename() ) . "\n";
        }
    }
}

echo "\n✅ Fixed " . esc_html( (string) $count ) . " files\n";
