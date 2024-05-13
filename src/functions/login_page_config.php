<?php

add_filter( 'login_errors', fn () => 'Nieprawidłowe dane logowania' );
add_filter( 'login_headerurl', fn () => 'https://rekurencja.com' );

add_action(
	'login_head',
	function () {
		$login_page_bg   = '/wp-content/themes/rekurencja-theme/assets/img/background.svg';
		$login_page_logo = '/wp-content/themes/rekurencja-theme/assets/img/logo.svg';

		echo "<style>
   body {
       background: url($login_page_bg) no-repeat center;
       background-size: cover;
       --focusColor: #a26be6;
       --purpleColor: #8b48ae;
   }

   .login h1 a {
       background: url($login_page_logo);
       width: 100%;
       height: 107px;
       background-size: contain;
       background-repeat: no-repeat;
   }

   body.login div#login form#loginform p.submit input#wp-submit {
       background-color: var(--purpleColor);
       transition: 0.5s ease-in-out;
   }

   body.login div#login form#loginform p.submit input#wp-submit:hover,
   {
       background-color: var(--focusColor);
   }

   body.login div#login form#loginform input:focus {
       border-color: var(--focusColor);
   }

   body.login div#login form#loginform p.submit input#wp-submit:focus {
       box-shadow: 0 0 0 1px #fff, 0 0 0 3px var(--purpleColor);
   }
</style>";
	}
);
