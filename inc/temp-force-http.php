<?php
/**
 * TẠM THỜI — ép địa chỉ site về http.
 *
 * Máy chủ chưa được cấp chứng chỉ SSL cho bluebells.vn. Vì địa chỉ site trong
 * cơ sở dữ liệu đang là https, trình duyệt chặn mọi ảnh, CSS và lệnh gọi
 * admin-ajax — nên trang chủ mất ảnh và wp-admin báo "Connection lost".
 *
 * Bộ lọc dưới đây hạ địa chỉ site xuống http ngay lúc chạy, không đụng vào
 * cơ sở dữ liệu. Ticket gửi Mắt Bão ngày 19/9/2026, mã 788140.
 *
 * XOÁ FILE NÀY VÀ DÒNG require_once TRONG functions.php NGAY KHI CÓ CHỨNG CHỈ.
 *
 * @package Bluebells_Studios
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Hạ https:// xuống http:// trong giá trị option siteurl/home.
 *
 * @param mixed $value Giá trị option.
 * @return mixed
 */
function bbs_temp_http_downgrade( $value ) {
	return is_string( $value ) ? preg_replace( '#^https://#i', 'http://', $value ) : $value;
}
add_filter( 'option_siteurl', 'bbs_temp_http_downgrade' );
add_filter( 'option_home', 'bbs_temp_http_downgrade' );

// Không ép wp-admin sang https khi chưa có chứng chỉ.
if ( function_exists( 'force_ssl_admin' ) ) {
	force_ssl_admin( false );
}
