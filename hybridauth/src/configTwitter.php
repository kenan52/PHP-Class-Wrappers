<?php
/**
 * Build a configuration array to pass to `Hybridauth\Hybridauth`
 *
 */

$config = [
	'callback' => 'https://iWeb.com/shared/socialLogin.php?provider=Twitter',
  'providers' => [
    'Twitter' => [
      'enabled' => true,
      'keys' => [
        'key' => '9tLRwuaS7Ll2Um7nVrKET5ccz',
        'secret' => 'i6sVqE8rCN4hGHGAobrMCzjh5WHyFbonC85rKmtsRYCSpS01Mm',
      ],
			'authorize_url_parameters' => ['provider' => 'Twitter',],
    ],
  ],
];