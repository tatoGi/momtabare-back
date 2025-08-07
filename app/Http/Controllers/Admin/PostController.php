<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Page;
use App\Models\Post;
use App\Models\PostAttribute;
use App\Services\PageTypeService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class PostController extends Controller
{
    /**
     * Display posts for a specific page
     */
    public function index(Page $page)
    {
        if (!$page->supportsPost()) {
            return redirect()->back()->with('error', 'This page type does not support posts.');
        }

        $posts = $page->posts()->with('attributes')->paginate(10);
        $pageTypeConfig = $page->getPageTypeConfig();
        
        return view('admin.posts.index', compact('page', 'posts', 'pageTypeConfig'));
    }

    /**
     * Show the form for creating a new post
     */
    public function create(Page $page)
    {
        if (!$page->supportsPost()) {
            return redirect()->back()->with('error', 'This page type does not support posts.');
        }

        $pageTypeConfig = $page->getPageTypeConfig();
        $translatableAttributes = PageTypeService::getTranslatableAttributes($page->type_id);
        $nonTranslatableAttributes = PageTypeService::getNonTranslatableAttributes($page->type_id);
        
        return view('admin.posts.create', compact('page', 'pageTypeConfig', 'translatableAttributes', 'nonTranslatableAttributes'));
    }

    /**
     * Store a newly created post
     */
    public function store(Request $request, Page $page)
    {
        if (!$page->supportsPost()) {
            return redirect()->back()->with('error', 'This page type does not support posts.');
        }

        // Get validation rules from PageTypeService
        $rules = PageTypeService::getValidationRules($page->type_id);
        $rules['published_at'] = 'nullable|date';
        $rules['active'] = 'boolean';
        $rules['sort_order'] = 'nullable|integer';
        
        $validator = Validator::make($request->all(), $rules);
        
        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        // Create the post
        $post = new Post();
        $post->page_id = $page->id;
        $post->active = $request->boolean('active', true);
        $post->sort_order = $request->input('sort_order', 0);
        $post->published_at = $request->input('published_at') ? now() : null;
        
        // Save the post and check if it was successful
        if (!$post->save()) {
            return redirect()->back()->with('error', 'Failed to create post')->withInput();
        }
        
        // Verify the post has an ID
        if (!$post->id) {
            return redirect()->back()->with('error', 'Post was not saved properly')->withInput();
        }

        // Save attributes
        $this->savePostAttributes($post, $request, $page->type_id);

        return redirect()->route('admin.pages.posts.index', ['locale' => app()->getLocale(), 'page' => $page->id])
            ->with('success', 'Post created successfully!');
    }

    /**
     * Show the form for editing a post
     */
    public function edit(Page $page, Post $post)
    {
        if (!$page->supportsPost() || $post->page_id !== $page->id) {
            return redirect()->back()->with('error', 'Invalid post or page.');
        }

        $pageTypeConfig = $page->getPageTypeConfig();
        $translatableAttributes = PageTypeService::getTranslatableAttributes($page->type_id);
        $nonTranslatableAttributes = PageTypeService::getNonTranslatableAttributes($page->type_id);
        
        // Load existing attributes
        $existingAttributes = [];
        foreach ($post->attributes as $attr) {
            if ($attr->locale) {
                $existingAttributes[$attr->locale][$attr->attribute_key] = $attr->attribute_value;
            } else {
                $existingAttributes[$attr->attribute_key] = $attr->attribute_value;
            }
        }
        
        return view('admin.posts.edit', compact('page', 'post', 'pageTypeConfig', 'translatableAttributes', 'nonTranslatableAttributes', 'existingAttributes'));
    }

    /**
     * Update a post
     */
    public function update(Request $request, Page $page, Post $post)
    {
        if (!$page->supportsPost() || $post->page_id !== $page->id) {
            return redirect()->back()->with('error', 'Invalid post or page.');
        }

        // Get validation rules from PageTypeService
        $rules = PageTypeService::getValidationRules($page->type_id);
        $rules['published_at'] = 'nullable|date';
        $rules['active'] = 'boolean';
        $rules['sort_order'] = 'nullable|integer';
        
        $validator = Validator::make($request->all(), $rules);
        
        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        // Update the post
        $post->active = $request->boolean('active', true);
        $post->sort_order = $request->input('sort_order', 0);
        $post->published_at = $request->input('published_at') ? now() : null;
        $post->save();

        // Update attributes
        $this->savePostAttributes($post, $request, $page->type_id);

        return redirect()->route('admin.pages.posts.index', ['locale' => app()->getLocale(), 'page' => $page->id])
            ->with('success', 'Post updated successfully!');
    }

    /**
     * Remove a post
     */
    public function destroy(Page $page, Post $post)
    {
        if (!$page->supportsPost() || $post->page_id !== $page->id) {
            return redirect()->back()->with('error', 'Invalid post or page.');
        }

        // Delete associated files
        $this->deletePostFiles($post, $page->type_id);
        
        // Delete the post (attributes will be deleted via cascade)
        $post->delete();

        return redirect()->route('admin.pages.posts.index', ['locale' => app()->getLocale(), 'page' => $page->id])
            ->with('success', 'Post deleted successfully!');
    }

    /**
     * Save post attributes
     */
    private function savePostAttributes(Post $post, Request $request, $pageTypeId)
    {
        // Debug: Check if post has ID
        if (!$post->id) {
            Log::error('Post ID is null in savePostAttributes', ['post' => $post->toArray()]);
            throw new \Exception('Post ID is null in savePostAttributes');
        }
        
        $translatableAttributes = PageTypeService::getTranslatableAttributes($pageTypeId);
        $nonTranslatableAttributes = PageTypeService::getNonTranslatableAttributes($pageTypeId);
        
        // Save translatable attributes
        foreach ($translatableAttributes as $key => $config) {
            foreach (config('app.locales') as $locale) {
                $value = $request->input("{$locale}.{$key}");
                
                if ($value !== null) {
                    PostAttribute::updateOrCreate(
                        [
                            'post_id' => $post->id,
                            'attribute_key' => $key,
                            'locale' => $locale
                        ],
                        [
                            'attribute_value' => $value
                        ]
                    );
                }
            }
        }
        
        // Save non-translatable attributes
        foreach ($nonTranslatableAttributes as $key => $config) {
            $value = $request->input($key);
            
            // Handle file uploads
            if ($config['type'] === 'image' && $request->hasFile($key)) {
                // Delete old file if exists
                $oldAttribute = PostAttribute::where('post_id', $post->id)
                    ->where('attribute_key', $key)
                    ->whereNull('locale')
                    ->first();
                    
                if ($oldAttribute && $oldAttribute->attribute_value) {
                    Storage::disk('public')->delete($oldAttribute->attribute_value);
                }
                
                // Store new file
                $file = $request->file($key);
                $filename = time() . '_' . $file->getClientOriginalName();
                $path = $file->storeAs('posts', $filename, 'public');
                $value = $path;
            }
            
            if ($value !== null) {
                PostAttribute::updateOrCreate(
                    [
                        'post_id' => $post->id,
                        'attribute_key' => $key,
                        'locale' => null
                    ],
                    [
                        'attribute_value' => $value
                    ]
                );
            }
        }
    }
    
    /**
     * Delete post files
     */
    private function deletePostFiles(Post $post, $pageTypeId)
    {
        $nonTranslatableAttributes = PageTypeService::getNonTranslatableAttributes($pageTypeId);
        
        foreach ($nonTranslatableAttributes as $key => $config) {
            if ($config['type'] === 'image') {
                $attribute = PostAttribute::where('post_id', $post->id)
                    ->where('attribute_key', $key)
                    ->whereNull('locale')
                    ->first();
                    
                if ($attribute && $attribute->attribute_value) {
                    Storage::disk('public')->delete($attribute->attribute_value);
                }
            }
        }
    }
}
