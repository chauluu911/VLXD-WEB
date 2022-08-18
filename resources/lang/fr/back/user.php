<?php

return [
    'seller' => [
    	'post' => ':approve approved, :notApprove not approve',
    ],
    'object_name' => 'account',
    'info' => [
    	'name' => 'full name',
    	'phone' => 'phone',
    	'email' => 'email',
    	'dob' => 'date of birth',
    	'country' => 'country',
    	'address' => 'address',
    ],
    'contact' => [
        'connect' => [
            'status' => [
                'waiting' => 'Waiting',
                'connected' => 'Connected'
            ]
        ]
    ],
    'advertise-list' => [
        'advertise' => 'advertise',
        'duration' => ':from to :to',
        'status' => [
            'approved' => 'Approved',
            'waiting' => 'Waiting',
            'expired' => 'Expired',
        ],
        'attr' => [
            'class1' => 'category level 1', 
            'class2' => 'category level 2',
            'class3' => 'category level 3',
            'titleEn' => 'title (En)',
            'titleFr' => 'title (Fr)',
            'titleVi' => 'title (Vi)',
        ]
    ]
];
