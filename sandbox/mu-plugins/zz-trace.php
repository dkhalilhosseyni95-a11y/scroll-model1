<?php
set_error_handler( static function ( $no, $str, $file, $line ) {
	if ( str_contains( $str, "Array to string conversion" ) ) {
		$trace = debug_backtrace( DEBUG_BACKTRACE_IGNORE_ARGS, 12 );
		$out = "\n=== ARRAY CONVERSION ===\n" . $str . " @ " . $file . ":" . $line . "\n";
		foreach ( $trace as $i => $frame ) {
			$out .= $i . " " . ( $frame["file"] ?? "?" ) . ":" . ( $frame["line"] ?? "?" ) . " " . ( $frame["function"] ?? "" ) . "\n";
		}
		file_put_contents( "/var/www/html/wp-content/uploads/trace.log", $out, FILE_APPEND );
	}
	return false;
} );
