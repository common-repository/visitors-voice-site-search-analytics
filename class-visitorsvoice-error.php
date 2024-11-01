<?php

/**
	* The VisitorsVoiceError Exception class
	*
	* An instance of this class is thrown any time the Visitors Voice Client encounters an error
	* making calls to the remote service.
	*
	* @author  Pontus Rosin <pontus@Visitors Voice.com>
	*
	* @since 1.0
	* @license: GPLv2 or later
	* @license URI: http://www.gnu.org/licenses/gpl-2.0.html
	*
	*/

class VisitorsVoiceError extends Exception {
	public function isInvalidAuthentication() {
		return $this->code == 401;
	}
}