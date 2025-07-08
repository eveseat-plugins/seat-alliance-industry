<?php
return [
    'allianceindustry' => [
        'name'          => 'Alliance Industry Planner',
        'icon'          => 'fas fa-industry',
        'route_segment' => 'allianceindustry',
        'permission' => 'allianceindustry.view_orders',
        'entries'       => [
            [
                'name'  => 'Orders',
                'icon'  => 'fas fa-list',
                'route' => 'allianceindustry.orders',
                'permission' => 'allianceindustry.view_orders',
            ],
            [
                'name'  => 'Deliveries',
                'icon'  => 'fas fa-user',
                'route' => 'allianceindustry.deliveries',
                'permission' => 'allianceindustry.create_deliveries',
            ],
            [
                'name'  => 'Settings',
                'icon'  => 'fas fa-cogs',
                'route' => 'allianceindustry.settings',
                'permission' => 'allianceindustry.settings',
            ],
            [
                'name'  => 'About',
                'icon'  => 'fas fa-info',
                'route' => 'allianceindustry.about',
                'permission' => 'allianceindustry.view_orders',
            ],
        ]
    ]
];