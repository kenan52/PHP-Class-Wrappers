<?php
/**
 * Build a configuration array to pass to `Hybridauth\Hybridauth`
 *
 */

$config = [
	'callback' => 'https://iWeb.com/shared/socialLogin.php?provider=Google',
  'providers' => [
    'Google' => [
      'enabled' => true,
      'keys' => [
        'id' => '441202812881-kk953ssciu1nihll41m2vkgocd6ig04r.apps.googleusercontent.com',
        'secret' => '_cyx2B7YX-1FiOT8aDIctPzF',
      ],
    ],
  ],
];