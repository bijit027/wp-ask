<?php
require_once('../../../wp-load.php');
global $wpdb;
$table = $wpdb->prefix . 'ipulse_surveys';
$results = $wpdb->get_results("SELECT id, title, status FROM {$table}");
header('Content-Type: application/json');
echo json_encode($results);
