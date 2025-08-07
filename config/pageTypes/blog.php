<?php

return [
    'id' => 2,
    'type' => 2,
    'name' => 'Blog Page',
    'slug' => 'blog',
    'has_posts' => true,
    'post_attributes' => [
        'translatable' => [
            'title' => [
                'type' => 'text',
                'required' => true,
                'label' => 'Post Title',
                'placeholder' => 'Enter blog post title'
            ],
            'slug' => [
                'type' => 'text',
                'required' => true,
                'label' => 'URL Slug',
                'placeholder' => 'post-url-slug'
            ],
            'excerpt' => [
                'type' => 'textarea',
                'required' => false,
                'label' => 'Short Description',
                'placeholder' => 'Brief summary of the post'
            ],
            'content' => [
                'type' => 'editor',
                'required' => true,
                'label' => 'Post Content',
                'placeholder' => 'Write your blog post content here'
            ],
            'meta_title' => [
                'type' => 'text',
                'required' => false,
                'label' => 'SEO Title',
                'placeholder' => 'SEO optimized title'
            ],
            'meta_description' => [
                'type' => 'textarea',
                'required' => false,
                'label' => 'SEO Description',
                'placeholder' => 'SEO meta description'
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
            'author' => [
                'type' => 'text',
                'required' => false,
                'label' => 'Author Name',
                'placeholder' => 'Post author'
            ],
            'tags' => [
                'type' => 'text',
                'required' => false,
                'label' => 'Tags',
                'placeholder' => 'tag1, tag2, tag3'
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