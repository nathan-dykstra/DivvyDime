<?php

return [
    [
        'text' => 'Home',
        'icon' => 'fa-solid fa-house fa-sm',
        'route' => 'dashboard', // TODO: fix route (this should just be divvydime.ca/)
    ],
    [
        'text' => 'Search',
        'icon' => 'fa-solid fa-magnifying-glass fa-sm',
        'route' => 'dashboard' // TODO: implement desktop search (modal)
    ],
    [
        'text' => 'Activity',
        'icon' => 'fa-solid fa-bell fa-sm',
        'route' => 'activity',
    ],
    [
        'text' => 'Expenses',
        'icon' => 'fa-solid fa-receipt fa-sm',
        'route' => 'expenses',
    ],
    [
        'text' => 'Groups',
        'icon' => 'fa-solid fa-user-group fa-sm',
        'route' => 'groups',
    ],
    [
        'text' => 'Friends',
        'icon' => 'fa-solid fa-user fa-sm',
        'route' => 'friends',
    ],
];
