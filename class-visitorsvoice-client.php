<?php
	/**
	* Client for the Visitors Voice API
	*
	* This class encapsulates all remote communication via HTTP with the Visitors Voice API.
	* If the client encounters any errors, it throws an instance of the VisitorsVoiceError exception class.
	*
	* @author  Pontus Rosin <pontus@visitorsvoice.com>
	* @since 1.0
	* @license: GPLv2 or later
	* @license URI: http://www.gnu.org/licenses/gpl-2.0.html
	*/

	class VisitorsVoiceClient {

		/**
		* Set mode: prod means make real API calls, local means make API call on local machine
		*/
		private $mode = "prod";
		
		/**
		* The URL endpoint that is the basis for all calls to the Visitors Voice API
		*/
		private $endpoint = 'http://api.visitorsvoice.com/api/v1/';
		
		
		/**
		* The URL endpoint for local mode
		*/
		private $endpoint_local = 'http://localhost:9999/api/';
		
		/**
		* The Visitors Voice API Key that this client should use for authenticating its calls
		*/
		private $api_key = NULL;
		
		/**
		* Blank constructor for the class
		*/
		public function __construct() {}

		/**
		* Retrieve the client's API Key
		*
		* @return string The client's api key
		*/
		public function get_api_key() {
			return $this->api_key;
		}

		/**
		* Set the client's API Key
		*
		* @param string $api_key The api key that the client should use
		*/
		public function set_api_key($api_key) {
			$this->api_key = $api_key;
		}

		/**
		* Based on the API Key supplied to the client, confirm whether or not the client is authorized to talk to the Visitors Voice API
		*
		* @return bool True or false signaling whether or not the client is authorized
		*/
		public function api_authorized() {
			$url = $this->endpoint . 'APIKey/Get';
			if($this->mode=='local'){
				$url = $this->endpoint_local . 'authorize.json';
			}
			try {
				$response = $this->call_api( 'GET', $url );
				
				if( 200 == $response['code']){
					$resp = json_decode($response['body']);
					if( $resp->Message == 'ok'){
						return true;
					} else return false;
				} else return false;
			} catch( VisitorsVoiceError $e ) {
				return false;
			}
		}
		
		/**
		* Get list of pages with suggestions from Visitors Voice API
		*
		* @return array 
		*/
		public function get_new_page_suggestions($permalink) {
			$url = $this->endpoint . 'RefinementsToUrl/Get';
			$params['url'] = $permalink;
			if($this->mode=='local'){
				$url = $this->endpoint_local . 'suggestion.json';
			}
			try {
				$response = $this->call_api( 'GET', $url, $params );
				return $response['body'];
			} catch( VisitorsVoiceError $e ) {
				return false;
			}
		}
		
		/**
		* Get list of pages with suggestions from Visitors Voice API
		*
		* @return array 
		*/
		public function get_new_page_suggestions_list($max_nr_prioritized_page) {
			$url = $this->endpoint . 'RefinementsToUrlList/Get';
			$params['max_results'] = $max_nr_prioritized_page;
			if($this->mode=='local'){
				$url = $this->endpoint_local . 'suggestionlist.json';
			}
			try {
				$response = $this->call_api( 'GET', $url, $params );
				
				return $response['body'];
			} catch( VisitorsVoiceError $e ) {
				return false;
			}
		}
		
		/**
		* Get URL Context for a URL (SearchTermsFromUrl, SearchTermsToUrl & KeywordsToUrl) from Visitors Voice API
		*
		* @param string $url The url to get related search terms and keywords for
		* @return arrays
		*/
		public function get_url_context($permalink, $daysback) {
			$url = $this->endpoint . 'UrlContext/Get';
			$enddate = date('Ymd');
			$startdate = $old = date("Ymd", strtotime($enddate." -".$daysback." days"));
			$params['start_date'] = $startdate;
			$params['end_date'] = $enddate;
			$params['url'] = $permalink;
			if($this->mode=='local'){
				$url = $this->endpoint_local . 'urlcontext.json';
			}
			try {
				$response = $this->call_api( 'GET', $url, $params );
				
				return $response['body'];
			} catch( VisitorsVoiceError $e ) {
				return false;
			}
		}
		
		/**
		* Make calls directly to the Visitors Voice API using wp_remote_request
		*
		* @param string $method The HTTP method to be used for the call: { GET, POST, PUT, DELETE }
		* @param string $url The URL for the call
		* @param array $params An array of parameters to be passed along as part of the call
		* @return array An array containing the HTTP status code as well as the response body
		*/
		private function call_api( $method, $url, $params = array() ) {
			if( $this->api_key )
				$params['api_key'] = $this->api_key;
			else
				throw new VisitorsVoiceError( 'Unauthorized', 403 );

			$headers = array(
				'User-Agent' => 'Visitors Voice Wordpress Plugin/' . VISITORSVOICE_VERSION,
				'Content-Type' => 'application/json'
			);

			$args = array(
				'method' => '',
				'timeout' => 10,
				'redirection' => 5,
				'httpversion' => '1.0',
				'blocking' => true,
				'headers' => $headers,
				'cookies' => array()
			);

			if( 'GET' == $method || 'DELETE' == $method ) {
				$url .= '?' . $this->serialize_params( $params );
				$args['method'] = $method;
				$args['body'] = array();
			} else if( $method == 'POST' ) {
				$args['method'] = $method;
				$args['body'] = json_encode( $params );
			}

			$response = wp_remote_request( $url, $args );
			
			if( ! is_wp_error( $response ) ) {
				$response_code = wp_remote_retrieve_response_code( $response );
				$response_message = wp_remote_retrieve_response_message( $response );
				if( 200 == $response_code ) {
					$response_body = wp_remote_retrieve_body( $response );
					return array( 'code' => $response_code, 'body' => $response_body );
				} elseif( 200 != $response_code && ! empty( $response_message ) ) {
					$response_body = wp_remote_retrieve_body( $response );
					throw new VisitorsVoiceError( $response_body, $response_code );
				} else {
					throw new VisitorsVoiceError( 'Unknown Error', $response_code );
				}
			} else {
				throw new VisitorsVoiceError( $response->get_error_message(), 500 );
			}
		}

		/**
		* Serialize parameters into query string.  Modifies http_build_query to remove array indexes.
		*
		* @param array $params An array of parameters to be passed along as part of the call
		* @return string The serialized query string
		*/
		private function serialize_params( $params ) {
			$query_string = http_build_query( $params );
			return preg_replace( '/%5B(?:[0-9]+)%5D=/', '%5B%5D=', $query_string );
		}
	}