<?php
function longer_permalinks_sanitize_title_with_dashes( $title, $raw_title = '', $context = 'display' ) {
	$title = strip_tags( $title );
	// Preserve escaped octets.
	$title = preg_replace( '|%([a-fA-F0-9][a-fA-F0-9])|', '---$1---', $title );
	// Remove percent signs that are not part of an octet.
	$title = str_replace( '%', '', $title );
	// Restore octets.
	$title = preg_replace( '|---([a-fA-F0-9][a-fA-F0-9])---|', '%$1', $title );

	if ( seems_utf8( $title ) ) {
		if ( function_exists( 'mb_strtolower' ) ) {
			$title = mb_strtolower( $title, 'UTF-8' );
		}
		$title = utf8_uri_encode($title, 3000);
	}

	$title = strtolower( $title );

	if ( 'save' === $context ) {
		// Convert &nbsp, &ndash, and &mdash to hyphens.
		$title = str_replace( array( '%c2%a0', '%e2%80%93', '%e2%80%94' ), '-', $title );
		// Convert &nbsp, &ndash, and &mdash HTML entities to hyphens.
		$title = str_replace( array( '&nbsp;', '&#160;', '&ndash;', '&#8211;', '&mdash;', '&#8212;' ), '-', $title );
		// Convert forward slash to hyphen.
		$title = str_replace( '/', '-', $title );

		// Strip these characters entirely.
		$title = str_replace(
			array(
				// Soft hyphens.
				'%c2%ad',
				// &iexcl and &iquest.
				'%c2%a1',
				'%c2%bf',
				// Angle quotes.
				'%c2%ab',
				'%c2%bb',
				'%e2%80%b9',
				'%e2%80%ba',
				// Curly quotes.
				'%e2%80%98',
				'%e2%80%99',
				'%e2%80%9c',
				'%e2%80%9d',
				'%e2%80%9a',
				'%e2%80%9b',
				'%e2%80%9e',
				'%e2%80%9f',
				// Bullet.
				'%e2%80%a2',
				// &copy, &reg, &deg, &hellip, and &trade.
				'%c2%a9',
				'%c2%ae',
				'%c2%b0',
				'%e2%80%a6',
				'%e2%84%a2',
				// Acute accents.
				'%c2%b4',
				'%cb%8a',
				'%cc%81',
				'%cd%81',
				// Grave accent, macron, caron.
				'%cc%80',
				'%cc%84',
				'%cc%8c',
				// Non-visible characters that display without a width.
				'%e2%80%8b', // Zero width space.
				'%e2%80%8c', // Zero width non-joiner.
				'%e2%80%8d', // Zero width joiner.
				'%e2%80%8e', // Left-to-right mark.
				'%e2%80%8f', // Right-to-left mark.
				'%e2%80%aa', // Left-to-right embedding.
				'%e2%80%ab', // Right-to-left embedding.
				'%e2%80%ac', // Pop directional formatting.
				'%e2%80%ad', // Left-to-right override.
				'%e2%80%ae', // Right-to-left override.
				'%ef%bb%bf', // Byte order mark.
				'%ef%bf%bc', // Object replacement character.
			),
			'',
			$title
		);

		// Convert non-visible characters that display with a width to hyphen.
		$title = str_replace(
			array(
				'%e2%80%80', // En quad.
				'%e2%80%81', // Em quad.
				'%e2%80%82', // En space.
				'%e2%80%83', // Em space.
				'%e2%80%84', // Three-per-em space.
				'%e2%80%85', // Four-per-em space.
				'%e2%80%86', // Six-per-em space.
				'%e2%80%87', // Figure space.
				'%e2%80%88', // Punctuation space.
				'%e2%80%89', // Thin space.
				'%e2%80%8a', // Hair space.
				'%e2%80%a8', // Line separator.
				'%e2%80%a9', // Paragraph separator.
				'%e2%80%af', // Narrow no-break space.
			),
			'-',
			$title
		);

		// Convert &times to 'x'.
		$title = str_replace( '%c3%97', 'x', $title );
	}

	// Remove HTML entities.
	$title = preg_replace( '/&.+?;/', '', $title );
	$title = str_replace( '.', '-', $title );

	$title = preg_replace( '/[^%a-z0-9 _-]/', '', $title );
	$title = preg_replace( '/\s+/', '-', $title );
	$title = preg_replace( '|-+|', '-', $title );
	$title = trim( $title, '-' );

	return $title;
}
