<?php
/**
 * Generates the bundled demo imagery with GD (no external assets, no network).
 *
 * Usage (inside a PHP container with GD):
 *   php tools/make-images.php
 *
 * Output: plugins/moon-core/assets/images/*.jpg
 *
 * @package MoonCore\Tools
 */

$target = __DIR__ . '/../plugins/moon-core/assets/images';

if ( ! is_dir( $target ) ) {
	mkdir( $target, 0775, true );
}

/**
 * Palette pair per product, matching the Persian colour names in the catalogue.
 */
$products = array(
	'01' => array( array( 24, 23, 21 ), array( 58, 53, 47 ), 'coat' ),
	'02' => array( array( 226, 219, 205 ), array( 176, 166, 150 ), 'coat' ),
	'03' => array( array( 234, 226, 210 ), array( 190, 160, 110 ), 'dress' ),
	'04' => array( array( 240, 236, 228 ), array( 198, 192, 182 ), 'blouse' ),
	'05' => array( array( 48, 46, 44 ), array( 92, 88, 82 ), 'skirt' ),
	'06' => array( array( 112, 108, 102 ), array( 68, 64, 60 ), 'trousers' ),
	'07' => array( array( 42, 39, 35 ), array( 88, 80, 70 ), 'coat' ),
	'08' => array( array( 214, 205, 188 ), array( 160, 148, 128 ), 'vest' ),
	'09' => array( array( 244, 241, 235 ), array( 200, 196, 188 ), 'shirt' ),
	'10' => array( array( 30, 29, 27 ), array( 74, 68, 58 ), 'shirt' ),
	'11' => array( array( 58, 55, 52 ), array( 104, 98, 90 ), 'trousers' ),
	'12' => array( array( 228, 216, 194 ), array( 184, 154, 104 ), 'scarf' ),
);

/**
 * Draw a vertical gradient background.
 *
 * @param resource|GdImage $image Canvas.
 * @param int              $w     Width.
 * @param int              $h     Height.
 * @param array            $from  RGB top colour.
 * @param array            $to    RGB bottom colour.
 */
function moon_gradient( $image, $w, $h, $from, $to ) {
	for ( $y = 0; $y < $h; $y++ ) {
		$ratio = $y / max( 1, $h - 1 );
		$color = imagecolorallocate(
			$image,
			(int) round( $from[0] + ( $to[0] - $from[0] ) * $ratio ),
			(int) round( $from[1] + ( $to[1] - $from[1] ) * $ratio ),
			(int) round( $from[2] + ( $to[2] - $from[2] ) * $ratio )
		);
		imageline( $image, 0, $y, $w, $y, $color );
	}
}

/**
 * Soft radial light on the canvas.
 *
 * @param resource|GdImage $image Canvas.
 * @param int              $cx    Centre x.
 * @param int              $cy    Centre y.
 * @param int              $r     Radius.
 * @param int              $alpha 0-127 (127 = transparent).
 */
function moon_glow( $image, $cx, $cy, $r, $alpha ) {
	for ( $i = $r; $i > 0; $i -= 4 ) {
		$t     = 1 - ( $i / $r );
		$a     = (int) ( 127 - ( 127 - $alpha ) * $t * $t );
		$color = imagecolorallocatealpha( $image, 255, 252, 244, max( 0, min( 127, $a ) ) );
		imagefilledellipse( $image, $cx, $cy, $i * 2, (int) ( $i * 1.6 ), $color );
	}
}

/**
 * Draw an abstract garment silhouette.
 *
 * @param resource|GdImage $image Canvas.
 * @param int              $w     Width.
 * @param int              $h     Height.
 * @param string           $shape Shape name.
 * @param array            $rgb   Fill colour.
 * @param int              $alpha Alpha.
 */
function moon_garment( $image, $w, $h, $shape, $rgb, $alpha ) {
	$fill  = imagecolorallocatealpha( $image, $rgb[0], $rgb[1], $rgb[2], $alpha );
	$edge  = imagecolorallocatealpha( $image, 255, 255, 255, 12 );
	$cx    = (int) ( $w / 2 );
	$top   = (int) ( $h * 0.3 );
	$shW   = (int) ( $w * 0.16 );

	switch ( $shape ) {
		case 'coat':
			$points = array(
				$cx - $shW, $top,
				$cx - (int) ( $shW * 2.6 ), $top + (int) ( $h * 0.06 ),
				$cx - (int) ( $shW * 2.2 ), $top + (int) ( $h * 0.34 ),
				$cx - (int) ( $shW * 1.5 ), (int) ( $h * 0.9 ),
				$cx + (int) ( $shW * 1.5 ), (int) ( $h * 0.9 ),
				$cx + (int) ( $shW * 2.2 ), $top + (int) ( $h * 0.34 ),
				$cx + (int) ( $shW * 2.6 ), $top + (int) ( $h * 0.06 ),
				$cx + $shW, $top,
			);
			imagefilledpolygon( $image, $points, $fill );
			imageline( $image, $cx, $top + 6, $cx, (int) ( $h * 0.88 ), $edge );
			break;

		case 'dress':
			$points = array(
				$cx - (int) ( $shW * 0.8 ), $top,
				$cx - (int) ( $shW * 1.9 ), $top + (int) ( $h * 0.1 ),
				$cx - (int) ( $shW * 2.6 ), (int) ( $h * 0.88 ),
				$cx + (int) ( $shW * 2.6 ), (int) ( $h * 0.88 ),
				$cx + (int) ( $shW * 1.9 ), $top + (int) ( $h * 0.1 ),
				$cx + (int) ( $shW * 0.8 ), $top,
			);
			imagefilledpolygon( $image, $points, $fill );
			imagefilledellipse( $image, $cx, (int) ( $h * 0.62 ), (int) ( $w * 0.05 ), (int) ( $h * 0.22 ), $edge );
			break;

		case 'blouse':
			imagefilledrectangle( $image, $cx - (int) ( $shW * 1.4 ), $top + (int) ( $h * 0.12 ), $cx + (int) ( $shW * 1.4 ), (int) ( $h * 0.78 ), $fill );
			imagefilledellipse( $image, $cx - (int) ( $shW * 1.4 ), $top + (int) ( $h * 0.2 ), (int) ( $shW * 2 ), (int) ( $shW * 2 ), $fill );
			imagefilledellipse( $image, $cx + (int) ( $shW * 1.4 ), $top + (int) ( $h * 0.2 ), (int) ( $shW * 2 ), (int) ( $shW * 2 ), $fill );
			break;

		case 'skirt':
			$points = array(
				$cx - (int) ( $shW * 1.3 ), $top + (int) ( $h * 0.16 ),
				$cx + (int) ( $shW * 1.3 ), $top + (int) ( $h * 0.16 ),
				$cx + (int) ( $shW * 2.5 ), (int) ( $h * 0.86 ),
				$cx - (int) ( $shW * 2.5 ), (int) ( $h * 0.86 ),
			);
			imagefilledpolygon( $image, $points, $fill );
			for ( $i = -2; $i <= 2; $i++ ) {
				imageline( $image, $cx + $i * (int) ( $shW * 0.5 ), $top + (int) ( $h * 0.18 ), $cx + $i * (int) ( $shW * 0.9 ), (int) ( $h * 0.85 ), $edge );
			}
			break;

		case 'trousers':
			imagefilledrectangle( $image, $cx - (int) ( $shW * 1.5 ), (int) ( $h * 0.32 ), $cx + (int) ( $shW * 1.5 ), (int) ( $h * 0.52 ), $fill );
			imagefilledpolygon( $image, array( $cx - (int) ( $shW * 1.5 ), (int) ( $h * 0.5 ), $cx - (int) ( $shW * 0.1 ), (int) ( $h * 0.5 ), $cx - (int) ( $shW * 0.5 ), (int) ( $h * 0.86 ), $cx - (int) ( $shW * 1.4 ), (int) ( $h * 0.86 ) ), $fill );
			imagefilledpolygon( $image, array( $cx + (int) ( $shW * 0.1 ), (int) ( $h * 0.5 ), $cx + (int) ( $shW * 1.5 ), (int) ( $h * 0.5 ), $cx + (int) ( $shW * 1.4 ), (int) ( $h * 0.86 ), $cx + (int) ( $shW * 0.5 ), (int) ( $h * 0.86 ) ), $fill );
			break;

		case 'shirt':
			$points = array(
				$cx - (int) ( $shW * 0.9 ), $top + (int) ( $h * 0.04 ),
				$cx - (int) ( $shW * 2.3 ), $top + (int) ( $h * 0.14 ),
				$cx - (int) ( $shW * 1.9 ), (int) ( $h * 0.82 ),
				$cx + (int) ( $shW * 1.9 ), (int) ( $h * 0.82 ),
				$cx + (int) ( $shW * 2.3 ), $top + (int) ( $h * 0.14 ),
				$cx + (int) ( $shW * 0.9 ), $top + (int) ( $h * 0.04 ),
			);
			imagefilledpolygon( $image, $points, $fill );
			imageline( $image, $cx, $top + (int) ( $h * 0.08 ), $cx, (int) ( $h * 0.8 ), $edge );
			break;

		case 'scarf':
			for ( $i = 0; $i < 5; $i++ ) {
				$offset = (int) ( $i * $w * 0.05 ) - (int) ( $w * 0.1 );
				imagefilledrectangle( $image, $cx - (int) ( $w * 0.22 ) + $offset, $top - (int) ( $h * 0.05 ), $cx + (int) ( $w * 0.22 ) + $offset, (int) ( $h * 0.8 ), $fill );
				$fill = imagecolorallocatealpha( $image, $rgb[0], $rgb[1], $rgb[2], max( 20, $alpha - 6 * $i ) );
			}
			break;

		case 'vest':
		default:
			imagefilledrectangle( $image, $cx - (int) ( $shW * 1.2 ), $top + (int) ( $h * 0.08 ), $cx + (int) ( $shW * 1.2 ), (int) ( $h * 0.8 ), $fill );
			imageline( $image, $cx, $top + (int) ( $h * 0.1 ), $cx, (int) ( $h * 0.78 ), $edge );
			break;
	}

	// Three buttons / folds for texture.
	for ( $i = 0; $i < 3; $i++ ) {
		imagefilledellipse( $image, $cx, $top + (int) ( $h * ( 0.34 + $i * 0.11 ) ), 10, 10, $edge );
	}
}

/**
 * Add a fine film grain.
 *
 * @param resource|GdImage $image Canvas.
 * @param int              $w     Width.
 * @param int              $h     Height.
 */
function moon_grain( $image, $w, $h ) {
	$count = (int) ( $w * $h / 90 );
	for ( $i = 0; $i < $count; $i++ ) {
		$x     = wp_rand_local( 0, $w - 1 );
		$y     = wp_rand_local( 0, $h - 1 );
		$alpha = wp_rand_local( 96, 126 );
		$color = imagecolorallocatealpha( $image, 255, 255, 255, $alpha );
		imagesetpixel( $image, $x, $y, $color );
	}
}

/**
 * Local rand helper (GD generator runs outside WordPress).
 *
 * @param int $min Min.
 * @param int $max Max.
 * @return int
 */
function wp_rand_local( $min, $max ) {
	return random_int( $min, $max );
}

/**
 * Build one editorial image.
 *
 * @param string $file   Target file name.
 * @param int    $w      Width.
 * @param int    $h      Height.
 * @param array  $from   Top RGB.
 * @param array  $to     Bottom RGB.
 * @param string $shape  Garment shape.
 * @param array  $accent Accent RGB.
 */
function moon_image( $file, $w, $h, $from, $to, $shape = '', $accent = array( 185, 154, 98 ) ) {
	global $target;

	$image = imagecreatetruecolor( $w, $h );
	imageantialias( $image, true );

	moon_gradient( $image, $w, $h, $from, $to );
	moon_glow( $image, (int) ( $w * 0.62 ), (int) ( $h * 0.28 ), (int) ( $w * 0.55 ), 96 );

	// Fabric folds: soft diagonal bands.
	for ( $i = 0; $i < 7; $i++ ) {
		$band  = imagecolorallocatealpha( $image, 255, 255, 255, random_int( 108, 124 ) );
		$start = (int) ( ( $i - 2 ) * $w * 0.22 );
		imagefilledpolygon(
			$image,
			array(
				$start, $h,
				$start + (int) ( $w * 0.12 ), $h,
				$start + (int) ( $w * 0.42 ), 0,
				$start + (int) ( $w * 0.3 ), 0,
			),
			$band
		);
	}

	if ( $shape ) {
		// Soft cast shadow so the silhouette separates from the backdrop.
		$shadow = imagecolorallocatealpha( $image, 0, 0, 0, 92 );
		imagefilledellipse( $image, (int) ( $w / 2 ), (int) ( $h * 0.88 ), (int) ( $w * 0.46 ), (int) ( $h * 0.1 ), $shadow );
		moon_garment( $image, $w, $h, $shape, $to, 56 );
	}

	// Gold hairline frame.
	$gold = imagecolorallocatealpha( $image, $accent[0], $accent[1], $accent[2], 70 );
	$inset = (int) ( $w * 0.045 );
	imagerectangle( $image, $inset, $inset, $w - $inset, $h - $inset, $gold );

	// Vignette.
	$edge = imagecolorallocatealpha( $image, $from[0], $from[1], $from[2], 80 );
	for ( $i = 0; $i < 26; $i++ ) {
		imagerectangle( $image, $i, $i, $w - $i, $h - $i, $edge );
	}

	moon_grain( $image, $w, $h );

	imagejpeg( $image, $target . '/' . $file, 82 );
	imagedestroy( $image );

	echo "wrote {$file}" . PHP_EOL;
}

foreach ( $products as $index => $spec ) {
	moon_image( 'product-' . $index . '.jpg', 900, 1200, $spec[0], $spec[1], $spec[2] );
}

moon_image( 'hero-poster.jpg', 2000, 1125, array( 12, 12, 11 ), array( 34, 31, 27 ), '', array( 201, 172, 119 ) );
moon_image( 'category-women.jpg', 1000, 1333, array( 30, 28, 26 ), array( 58, 50, 42 ), 'dress' );
moon_image( 'category-men.jpg', 1000, 1333, array( 22, 22, 21 ), array( 44, 42, 38 ), 'coat' );
moon_image( 'campaign.jpg', 1600, 1000, array( 18, 17, 16 ), array( 46, 40, 33 ), 'coat', array( 201, 172, 119 ) );
moon_image( 'story.jpg', 1200, 1500, array( 232, 226, 214 ), array( 198, 188, 172 ), '', array( 140, 116, 74 ) );

echo "done" . PHP_EOL;
