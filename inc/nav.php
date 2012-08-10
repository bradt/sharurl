<?php
$nav = array(
	'home' => array(
		'link' => '',
		'title' => 'Upload'
	),
	'about' => array(
		'link' => 'about',
		'title' => 'About',
		'children' => array(
			'about' => array(
				'link' => '',
				'title' => 'About'
			),
			'faq' => array(
				'link' => 'faq',
				'title' => 'Frequently Asked Questions'
			),
            'contact' => array(
                'link' => 'contact',
                'title' => 'Contact'
            )
		)
	),
    'pricing' => array(
        'link' => 'pricing',
        'title' => 'Pricing'
    ),
    'account' => array(
        'link' => 'account',
        'title' => 'My Account',
        'children' => array(
            'account' => array(
                'link' => '',
                'title' => 'Files'
            ),
            'upgrade' => array(
                'link' => 'upgrade',
                'title' => 'Upgrade'
            ),
            'details' => array(
                'link' => 'details',
                'title' => 'My Details'
            )
        )
    ),
	'api' => array(
		'link' => 'api',
		'title' => 'API'
	),
	'blog' => array(
		'link' => 'blog',
		'title' => 'Blog'
	)
);
?>