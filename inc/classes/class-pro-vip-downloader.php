<?php
/**
 * Downloader Class
 *
 * @class          Pro_VIP_Downloader
 * @version        1.0
 * @package        Pro-VIP
 * @category       Class
 * @author         Pro-WP Team
 */

defined( 'ABSPATH' ) or die; // Prevents direct access

/*
* Downloader Class
* By Timothy 'Tim' Oliver
*
* Streamlines and simplifies
* downloading of files to the user's
* computer. Download managers can
* also perform resume downloads with it.
*
* Based on code and logic from:
* http://w-shadow.com/blog/2007/08/12/how-to-force-file-download-with-php/
*
* ============================================================================
*
* Copyright (C) 2011 by Tim Oliver
*
* Permission is hereby granted, free of charge, to any person obtaining a copy
* of this software and associated documentation files (the "Software"), to deal
* in the Software without restriction, including without limitation the rights
* to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
* copies of the Software, and to permit persons to whom the Software is
* furnished to do so, subject to the following conditions:
*
* The above copyright notice and this permission notice shall be included in
* all copies or substantial portions of the Software.
*
* THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
* IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
* FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
* AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
* LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
* OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
* THE SOFTWARE.
*
*/

class Pro_VIP_Downloader {

	public
		$forceSingle = false,
		$fileSize = 0,
		$mimetype = 'application/octet-stream',
		$dlFileName = '';


	//length of the file


	//in multi-threaded downloading, the offset to start at
	private
		$filePath = '',
		$mt_range = 0;

	/*
	* Class Constructor
	*/
	function __construct() {
		if ( ini_get( 'safe_mode' ) ) {
			throw new Exception( '<b>Downloader:</b> Will not be able to handle large files while safe mode is enabled.' );
		}
	}


	/*
	* Prepare Headers
	*
	* Prepare the main output header strings for the download
	*/
	private function prepare_headers( $size = 0 ) {
		// required for IE, otherwise Content-Disposition may be ignored
		if ( ini_get( 'zlib.output_compression' ) ) {
			ini_set( 'zlib.output_compression', 'Off' );
		}

		header( 'Content-Type: ' . $this->mimetype );
		header( 'Content-Disposition: attachment; filename="' . $this->dlFileName . '"' );
		header( "Content-Transfer-Encoding: binary" );
		header( 'Accept-Ranges: bytes' );

		/* The three lines below basically make the
		download non-cacheable */
		header( "Cache-control: private" );
		header( 'Pragma: private' );
		header( "Expires: Mon, 26 Jul 1997 05:00:00 GMT" );

		// multipart-download and download resuming support
		if ( isset( $_SERVER[ 'HTTP_RANGE' ] ) && ! $this->forceSingle ) {
			list( $a, $range ) = explode( "=", $_SERVER[ 'HTTP_RANGE' ], 2 );
			list( $range ) = explode( ",", $range, 2 );
			list( $range, $range_end ) = explode( "-", $range );

			$range = intval( $range );

			if ( ! $range_end ) {
				$range_end = $size - 1;
			} else {
				$range_end = intval( $range_end );
			}

			$new_length = $range_end - $range + 1;
			header( 'HTTP/1.1 206 Partial Content' );
			header( 'Content-Length: ' . $new_length );
			header( 'Content-Range: bytes ' . $range . '-' . $range_end . '/' . $size );

			//set the offset range
			$this->mt_range = $range;
		} else {
			$new_length = $size;
			header( "Content-Length: " . $size );
		}

		return $new_length;
	}

	public function filePath( $file ){

		//assert the file is valid
		if ( ! is_file( $file ) ) {
			throw new Exception( 'Downloader: Could not find file \'' . $file . '\'' );
		}

		//make sure it's read-able
		if ( ! is_readable( $file ) ) {
			throw new Exception( 'Downloader: File was unreadable \'' . $file . '\'' );
		}

		$this->filePath = $file;
	}

	/*
	* Download File
	*
	* Set up the headers and download the file to the
	*/
	function download() {

		$filename = $this->filePath;
		if( empty( $filename ) ){
			throw new Exception( 'Downloader: File path is empty.' );
		}


		//set script execution time to 0 so the script
		//won't time out.
		set_time_limit( 0 );

		//get the size of the file
		$this->fileSize = filesize( $filename );

		//set up the main headers
		//find out the number of bytes to write in this iteration
		$block_size = $this->prepare_headers( $this->fileSize );

		/* output the file itself */
		$chunksize  = 1 * ( 1024 * 1024 );
		$bytes_send = 0;

		if ( $file = fopen( $filename, 'r' ) ) {
			if ( isset( $_SERVER[ 'HTTP_RANGE' ] ) && ! $this->forceSingle ) {
				fseek( $file, $this->mt_range );
			}

			//write the data out to the browser
			while ( ! feof( $file ) && ! connection_aborted() && $bytes_send < $block_size ) {
				$buffer = fread( $file, $chunksize );
				echo $buffer;
				flush();
				$bytes_send += strlen( $buffer );
			}

			fclose( $file );
		} else {
			throw new Exception( 'Downloader: Could not open file \'' . $filename . '\'' );
		}

		//terminate script upon completion
		die();
	}
}