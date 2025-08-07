<?php

return [
    'id' => 5,
    'type' => 5,
    'folder' => 'projcet',
    'name' => 'Routes/News Page',
    'slug' => 'routes',
    'has_posts' => true,
    'post_attributes' => [
        'translatable' => [
            'title' => [
                'type' => 'text',
                'required' => true,
                'label' => 'News Title',
                'placeholder' => 'Enter news title'
            ],
            'slug' => [
                'type' => 'text',
                'required' => true,
                'label' => 'URL Slug',
                'placeholder' => 'news-url-slug'
            ],
            'summary' => [
                'type' => 'textarea',
                'required' => true,
                'label' => 'News Summary',
                'placeholder' => 'Brief summary of the news'
            ],
            'content' => [
                'type' => 'editor',
                'required' => true,
                'label' => 'Full Content',
                'placeholder' => 'Write the full news content here'
            ]
        ],
        'non_translatable' => [
            'featured_image' => [
                'type' => 'image',
                'required' => false,
                'label' => 'Featured Image',
                'accept' => 'image/*'
            ],
            'published_at' => [
                'type' => 'datetime-local',
                'required' => true,
                'label' => 'Publish Date',
                'default' => 'now'
            ],
            'priority' => [
                'type' => 'select',
                'required' => true,
                'label' => 'Priority',
                'default' => 'medium',
                'options' => [
                    'low' => 'Low',
                    'medium' => 'Medium',
                    'high' => 'High',
                    'urgent' => 'Urgent'
                ]
            ],
            'status' => [
                'type' => 'select',
                'required' => true,
                'label' => 'Status',
                'default' => 'draft',
                'options' => [
                    'draft' => 'Draft',
                    'published' => 'Published',
                    'archived' => 'Archived'
                ]
            ]
        ]
    ]
];