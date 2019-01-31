<?php
/**
 * Build a configuration array to pass to `Hybridauth\Hybridauth`
 *
 */

$config = [
	'callback' => 'https://imerhaba.com/shared/socialLogin.php?provider=Facebook',
  'providers' => [
    'Twitter' => [
      'enabled' => true,
      'keys' => [
        'key' => '9tLRwuaS7Ll2Um7nVrKET5ccz',
        'secret' => 'i6sVqE8rCN4hGHGAobrMCzjh5WHyFbonC85rKmtsRYCSpS01Mm',
      ],
			'authorize_url_parameters' => ['provider' => 'Twitter',],
    ],
    'Google' => [
      'enabled' => true,
      'keys' => [
        'id' => '441202812881-kk953ssciu1nihll41m2vkgocd6ig04r.apps.googleusercontent.com',
        'secret' => '_cyx2B7YX-1FiOT8aDIctPzF',
      ],
    ],
    'Facebook' => [
      'enabled' => true,
      'keys' => [
        'id' => '396449634435523',
        'secret' => '9bcda813a172ad15cd90df2992c268a6',
      ],
    ],
  ],
];