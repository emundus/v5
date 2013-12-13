<?php

/**
 * oauth-php: Example OAuth client for accessing Xing profiles
 *
 * @author Rene Schmidt, based on Google Client example by BBG
 *
 *
 * The MIT License
 *
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
 */

require_once COM_FABRIK_BASE . '/components/com_fabrik/libs/oauth-php/OAuthStore.php';
require_once COM_FABRIK_BASE . '/components/com_fabrik/libs/oauth-php/OAuthRequester.php';

/**
 * Overloads some methods from OAuthRequester. They work now with Xing API.
 */
class XingOAuthRequester extends OAuthRequester
{

	/**
	 * Request a request token from the site belonging to consumer_key
	 *
	 * @param   string  $consumer_key
	 * @param   int     $usr_id
	 * @param   array   $params (optional) extra arguments for when requesting the request token
	 * @param   string  $method (optional) change the method of the request, defaults to POST (as it should be)
	 * @param   array   $options (optional) options like name and token_ttl
	 * @param   array   $curl_options  optional extra options for curl request
	 *
	 * @exception OAuthException2 when no key could be fetched
	 * @exception OAuthException2 when no server with consumer_key registered
	 * @return array (authorize_uri, token)
	 */
	static function requestRequestToken($consumer_key, $usr_id, $params = null, $method = 'POST', $options = array(), $curl_options = array())
	{
		OAuthRequestLogger::start();

		if (isset($options['token_ttl']) && is_numeric($options['token_ttl']))
		{
			$params['xoauth_token_ttl'] = intval($options['token_ttl']);
		}

		$store = OAuthStore::instance();
		$r = $store->getServer($consumer_key, $usr_id);
		$uri = $r['request_token_uri'];

		$oauth = new OAuthRequester($uri, $method, $params);
		$oauth->sign($usr_id, $r, '', 'requestToken');

		$text = $oauth->curl_raw($curl_options);

		if (empty($text))
		{
			throw new OAuthException2('No answer from the server "' . $uri . '" while requesting a request token');
		}
		$data = $oauth->curl_parse($text);

		// Patch for xing api
		if (!in_array((int) $data['code'], array(200, 201)))
		{
			throw new OAuthException2('Unexpected result from the server "' . $uri . '" (' . $data['code'] . ') while requesting a request token');
		}
		$token = array();
		$params = explode('&', $data['body']);
		foreach ($params as $p)
		{
			@list($name, $value) = explode('=', $p, 2);
			$token[$name] = $oauth->urldecode($value);
		}

		if (!empty($token['oauth_token']) && !empty($token['oauth_token_secret']))
		{
			$opts = array();
			if (isset($options['name']))
			{
				$opts['name'] = $options['name'];
			}
			if (isset($token['xoauth_token_ttl']))
			{
				$opts['token_ttl'] = $token['xoauth_token_ttl'];
			}
			$store->addServerToken($consumer_key, 'request', $token['oauth_token'], $token['oauth_token_secret'], $usr_id, $opts);
		}
		else
		{
			throw new OAuthException2('The server "' . $uri . '" did not return the oauth_token or the oauth_token_secret');
		}

		OAuthRequestLogger::flush();

		// Now we can direct a browser to the authorize_uri
		return array(
				'authorize_uri' => $r['authorize_uri'],
				'token' => $token['oauth_token']
		);
	}

	/**
	 * Request an access token from the site belonging to consumer_key.
	 * Before this we got an request token, now we want to exchange it for
	 * an access token.
	 *
	 * @static
	 * @param string consumer_key
	 * @param string token
	 * @param int usr_id    user requesting the access token
	 * @param string method (optional) change the method of the request, defaults to POST (as it should be)
	 * @param array options (optional) extra options for request, eg token_ttl
	 * @param array curl_options  optional extra options for curl request
	 * @return array (code=>http-code, headers=>http-headers, body=>body)
	 * @throws OAuthException2 when no key could be fetched
	 * @throws OAuthException2 when no server with consumer_key registered
	 * @throws OAuthException2
	 */
	static function requestAccessToken($consumer_key, $token, $usr_id, $method = 'POST', $options = array(), $curl_options = array())
	{
		OAuthRequestLogger::start();

		$store = OAuthStore::instance();
		$r = $store->getServerTokenSecrets($consumer_key, $token, 'request', $usr_id);
		echo "<pre>";print_r($r);
		$uri = $r['access_token_uri'];
		$token_name = $r['token_name'];

		// Delete the server request token, this one was for one use only
		$store->deleteServerToken($consumer_key, $r['token'], 0, true);

		// Try to exchange our request token for an access token
		$oauth = new OAuthRequester($uri, $method);

		if (isset($options['oauth_verifier']))
		{
			$oauth->setParam('oauth_verifier', $options['oauth_verifier']);
		}
		if (isset($options['token_ttl']) && is_numeric($options['token_ttl']))
		{
			$oauth->setParam('xoauth_token_ttl', intval($options['token_ttl']));
		}

		OAuthRequestLogger::setRequestObject($oauth);


		$oauth->sign($usr_id, $r, '', 'accessToken');


		$text = $oauth->curl_raw($curl_options);
		if (empty($text))
		{
			throw new OAuthException2('No answer from the server "' . $uri . '" while requesting an access token');
		}
		$data = $oauth->curl_parse($text);

		// Patch for xing api
		if (!in_array((int) $data['code'], array(200, 201, 301, 302)))
		{
			throw new OAuthException2('Unexpected result from the server "' . $uri . '" (' . $data['code'] . ') while requesting an access token');
		}

		$token = array();
		$params = explode('&', $data['body']);
		foreach ($params as $p)
		{
			@list($name, $value) = explode('=', $p, 2);
			$token[$oauth->urldecode($name)] = $oauth->urldecode($value);
		}

		if (!empty($token['oauth_token']) && !empty($token['oauth_token_secret']))
		{
			$opts = array();
			$opts['name'] = $token_name;
			if (isset($token['xoauth_token_ttl']))
			{
				$opts['token_ttl'] = $token['xoauth_token_ttl'];
			}
			$store->addServerToken($consumer_key, 'access', $token['oauth_token'], $token['oauth_token_secret'], $usr_id, $opts);
		}
		else
		{
			throw new OAuthException2('The server "' . $uri . '" did not return the oauth_token or the oauth_token_secret');
		}

		OAuthRequestLogger::flush();

		// Patch for xing api
		return $data;
	}
}

