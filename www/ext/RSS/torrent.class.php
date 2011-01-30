<?php
/**
 * Torrent
 *
 * PHP version 5 only
 *
 * LICENSE: This source file is subject to version 3 of the GNU GPL
 * that is available through the world-wide-web at the following URI:
 * http://www.gnu.org/licenses/gpl.html. If you did not receive a copy of
 * the GNU GPL License and are unable to obtain it through the web, please
 * send a note to adrien.gibrat@gmail.com so I can mail you a copy.
 *
 * @author   Adrien Gibrat <adrien.gibrat@gmail.com>
 * @copyleft 2010 - Just use it!
 * @license  http://www.gnu.org/licenses/gpl.html GNU General Public License version 3
 * @version  Release: 1.0
 */
class Torrent {
	
	/**
	* @const float Default http timeout
	*/
	const timeout = 30;

	/**
	* @var array List of error occured
	*/
	static protected $errors = array();

	/** Read and decode torrent file/data OR build a torrent from source folder/file(s)
	 * Supported signatures:
	 * - Torrent(); // get an instance (usefull to scrape and check errors)
	 * - Torrent( string $torrent ); // analyse a torrent file
	 * - Torrent( string $torrent, string $announce );
	 * - Torrent( string $torrent, array $meta );
	 * - Torrent( string $file_or_folder ); // create a torrent file
	 * - Torrent( string $file_or_folder, string $announce_url, [int $piece_length] );
	 * - Torrent( string $file_or_folder, array $meta, [int $piece_length] );
	 * - Torrent( array $files_list );
	 * - Torrent( array $files_list, string $announce_url, [int $piece_length] );
	 * - Torrent( array $files_list, array $meta, [int $piece_length] );
	 * @param string|array torrent to read or source folder/file(s) (optional, to get an instance)
	 * @param string|array announce url or meta informations (optional)
	 * @param int piece length (optional)
	 */
	public function __construct ( $data = null, $meta = array(), $piece_length = 256 ) { // used
		$meta = array_merge( $meta, $this->decode( $data ) );
		foreach( $meta as $key => $value )
			$this->{$key} = $value;
	}

	/** Convert the current Torrent instance in torrent format
	 * @return string encoded torrent data
	 */
	public function __toString() {
		return $this->encode( $this );
	}

	/** Return last error message
	 * @return string|boolean last error message or false if none
	 */
	public function error() {
		return empty( self::$errors ) ?
			false :
			self::$errors[0]->getMessage();
	}

	/** Return Errors
	 * @return array|boolean error list or false if none
	 */
	public function errors() {
		return empty( self::$errors ) ?
			false :
			self::$errors;
	}

	/**** Analyze BitTorrent ****/

	/** Compute hash info
	 * @return string hash info or null if info not set
	 */
	public function hash_info () {
		return isset( $this->info ) ?
			sha1( self::encode( $this->info ) ) :
			null;
	}

	/**** Encode BitTorrent ****/

	/** Encode torrent data
	 * @param mixed data to encode
	 * @return string torrent encoded data
	 */
	static public function encode ( $mixed ) {
		switch ( gettype( $mixed ) ) {
			case 'integer':
			case 'double':
				return self::encode_integer( $mixed );
            case 'object':
            	$mixed = (array) $mixed; //Thanks to W-Shadow: http://w-shadow.com/blog/2008/11/11/parse-edit-and-create-torrent-files-with-php/
            case 'array':
				return self::encode_array( $mixed );
			default:
				return self::encode_string( (string) $mixed );
		}
	}

	/** Encode torrent string
	 * @param string string to encode
	 * @return string encoded string
	 */
	static private function encode_string ( $string ) {
		return strlen( $string ) . ':' . $string;
	}

	/** Encode torrent integer
	 * @param integer integer to encode
	 * @return string encoded integer
	 */
	static private function encode_integer ( $integer ) {
		return 'i' . $integer . 'e';
	}

	/** Encode torrent dictionary or list
	 * @param array array to encode
	 * @return string encoded dictionary or list
	 */
	static private function encode_array ( $array ) {
		if ( self::is_list( $array ) ) {
			$return = 'l';
			foreach ( $array as $value )
				$return .= self::encode( $value );
		} else {
			ksort( $array, SORT_STRING );
			$return = 'd';
			foreach ( $array as $key => $value )
				$return .= self::encode( strval( $key ) ) . self::encode( $value );
		}
		return $return . 'e';
	}

	/**** Decode BitTorrent ****/

	/** Decode torrent data or file
	 * @param string data or file path to decode
	 * @return array decoded torrent data
	 */
	static protected function decode ( $string ) {
		$data = is_file( $string ) || self::url_exists( $string ) ?
			self::file_get_contents( $string ) :
			$string;
		return (array) self::decode_data( $data );
	}

	/** Decode torrent data
	 * @param string data to decode
	 * @return array decoded torrent data
	 */
	static private function decode_data ( & $data ) {
		switch( self::char( $data ) ) {
		case 'i':
			$data = substr( $data, 1 );
			return self::decode_integer( $data );
		case 'l':
			$data = substr( $data, 1 );
			return self::decode_list( $data );
		case 'd':
			$data = substr( $data, 1 );
			return self::decode_dictionary( $data );
		default:
			return self::decode_string( $data );
		}
	}

	/** Decode torrent dictionary
	 * @param string data to decode
	 * @return array decoded dictionary
	 */
	static private function decode_dictionary ( & $data ) {
		$dictionary = array();
		$previous = null;
		while ( ( $char = self::char( $data ) ) != 'e' ) {
			if ( $char === false )
				return self::set_error( new Exception( 'Unterminated dictionary' ) );
			if ( ! ctype_digit( $char ) )
				return self::set_error( new Exception( 'Invalid dictionary key' ) );
			$key = self::decode_string( $data );
			if ( isset( $dictionary[$key] ) )
				return self::set_error( new Exception( 'Duplicate dictionary key' ) );
			if ( $key < $previous )
				return self::set_error( new Exception( 'Missorted dictionary key' ) );
			$dictionary[$key] = self::decode_data( $data );
			$previous = $key;
		}
		$data = substr( $data, 1 );
		return $dictionary;
	}

	/** Decode torrent list
	 * @param string data to decode
	 * @return array decoded list
	 */
	static private function decode_list ( & $data ) {
		$list = array();
		while ( ( $char = self::char( $data ) ) != 'e' ) {
			if ( $char === false )
				return self::set_error( new Exception( 'Unterminated list' ) );
			$list[] = self::decode_data( $data );
		}
		$data = substr( $data, 1 );
		return $list;
	}

	/** Decode torrent string
	 * @param string data to decode
	 * @return string decoded string
	 */
	static private function decode_string ( & $data ) {
		if ( self::char( $data ) === '0' && substr( $data, 1, 1 ) != ':' )
			self::set_error( new Exception( 'Invalid string length, leading zero' ) );
		if ( ! $colon = @strpos( $data, ':' ) )
			return self::set_error( new Exception( 'Invalid string length, colon not found' ) );
		$length = intval( substr( $data, 0, $colon ) );
		if ( $length + $colon + 1 > strlen( $data ) )
			return self::set_error( new Exception( 'Invalid string, input too short for string length' ) );
		$string = substr( $data, $colon + 1, $length );
		$data = substr( $data, $colon + $length + 1 );
		return $string;
	}

	/** Decode torrent integer
	 * @param string data to decode
	 * @return integer decoded integer
	 */
	static private function decode_integer ( & $data ) {
		$start = 0;
		$end	= strpos( $data, 'e');
		if ( $end === 0 )
			self::set_error( new Exception( 'Empty integer' ) );
		if ( self::char( $data ) == '-' )
			$start++;
		if ( substr( $data, $start, 1 ) == '0' && ( $start != 0 || $end > $start + 1 ) )
			self::set_error( new Exception( 'Leading zero in integer' ) );
		if ( ! ctype_digit( substr( $data, $start, $end ) ) )
			self::set_error( new Exception( 'Non-digit characters in integer' ) );
		$integer = substr( $data, 0, $end );
		$data = substr( $data, $end + 1 );
		return $integer + 0;
	}

	/**** Internal Helpers ****/

	/** Add an error to errors stack
	 * @param Exception error to add
	 * @param boolean return error message or not (optional, default to false)
	 * @return boolean|string return false or error message if requested
	 */
	static protected function set_error ( $exception, $message = false ) {
		return ( array_unshift( self::$errors,  $exception ) && $message ) ? $exception->getMessage() : false;
	}

	/** Helper to test if an array is a list
	 * @param array array to test
	 * @return boolean is the array a list or not
	 */
	static protected function is_list ( $array ) {
		foreach ( array_keys( $array ) as $key )
			if ( ! is_int( $key ) )
				return false;
		return true;
	}

	/** Helper to return the first char of encoded data
	 * @param string encoded data
	 * @return string|boolean first char of encoded data or false if empty data
	 */
	static private function char ( $data ) {
		return empty( $data ) ?
			false :
			substr( $data, 0, 1 );
	}

	/**** Public Helpers ****/

	/** Helper to check if url exists
	 * @param string url to check
	 * @return boolean does the url exist or not
	 */
	static public function url_exists ( $url ) {
		return preg_match( '#^http(s)?://[a-z0-9-]+(.[a-z0-9-]+)*(:[0-9]+)?(/.*)?$#i', $url ) ?
			(bool) preg_grep( '#^HTTP/.*\s(200|304)\s#', (array) @get_headers( $url ) ) :
			false;
	}

	/** Helper to get (distant) file content
	 * @param string file location
	 * @param float http timeout (optional, default to self::timeout 30s)
	 * @return string|boolean file content or false if error
	 */
	static public function file_get_contents ( $file, $timeout = self::timeout ) {
		if ( is_file( $file ) || ini_get( 'allow_url_fopen' ) )
			return @file_get_contents( $file, false, is_file( $file ) && $timeout ? stream_context_create( array( 'http' => array( 'timeout' => $timeout ) ) ) : null );
		elseif ( ! function_exists( 'curl_init' ) )
			return ! self::$errors[] = new Exception( 'Install CURL or enable "allow_url_fopen"' );
		$handle = curl_init( $file );
		if ( $timeout )
			curl_setopt( $handle, CURLOPT_TIMEOUT, $timeout );
		curl_setopt( $handle, CURLOPT_RETURNTRANSFER, 1 );
		$content = curl_exec( $handle );
		curl_close( $handle );
		return $content;
	}

}

?>