<?php
/**
 * IP Helper
 *
 * @package InsightPulse
 */

namespace InsightPulse\Utils;

/**
 * Class IpHelper
 */
class IpHelper {

	/**
	 * Safely get the user's IP address, accounting for Cloudflare and proxies.
	 * Mirrors OpinionCraft Utility::getIp().
	 *
	 * @return string
	 */
	public static function get_ip(): string {
		// Check if the website is behind Cloudflare
		if ( ! empty( $_SERVER['HTTP_CF_CONNECTING_IP'] ) ) {
			return sanitize_text_field( wp_unslash( $_SERVER['HTTP_CF_CONNECTING_IP'] ) );
		}

		// Check for shared internet/ISP proxy
		if ( ! empty( $_SERVER['HTTP_CLIENT_IP'] ) ) {
			return sanitize_text_field( wp_unslash( $_SERVER['HTTP_CLIENT_IP'] ) );
		}

		// Check for IPs passing through proxies
		if ( ! empty( $_SERVER['HTTP_X_FORWARDED_FOR'] ) ) {
			$ip_list = explode( ',', wp_unslash( $_SERVER['HTTP_X_FORWARDED_FOR'] ) );
			return sanitize_text_field( trim( $ip_list[0] ) );
		}

		return sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ?? '' ) );
	}
}
