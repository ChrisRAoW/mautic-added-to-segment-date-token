<?php 

return [
    'name'        => 'Added to Segment Date Token',
    'description' => 'Token to display date when contact was added to segment',
    'version'     => '1.0',
    'author'      => 'Right Amount of Weird',
    'services' => [
        'events' => [
            'mautic.plugin.addtosegementdate.subscriber' => [
                'class'     => 'MauticPlugin\AddedToSegmentDateTokenBundle\EventListener\AddedToSegmentDateToken',
                'arguments' => [
                    'mautic.helper.template.date',
                ],
            ],
        ],
    ],
];