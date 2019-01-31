<?php
/**
 * Build a configuration array to pass to `Hybridauth\Hybridauth`
 *
 */

$config = [
	'callback' => 'https://iWeb.com/shared/socialLogin.php?provider=Facebook',
  'providers' => [
    'Facebook' => [
      'enabled' => true,
      'keys' => [
        'id' => '396449634435523',
        'secret' => '9bcda813a172ad15cd90df2992c268a6',
      ],
    ],
  ],
];