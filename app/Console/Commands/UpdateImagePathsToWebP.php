<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class UpdateImagePathsToWebP extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'images:update-db-paths';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update all image paths in database from old extensions (jpg, png, gif) to .webp';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting database image path updates...');
        $this->newLine();

        $totalUpdated = 0;

        // Update product_images table
        $this->info('Updating product_images table...');
        $productImages = DB::table('product_images')->get();
        foreach ($productImages as $image) {
            $oldPath = $image->image_name;
            $newPath = $this->convertPathToWebP($oldPath);

            if ($oldPath !== $newPath) {
                DB::table('product_images')
                    ->where('id', $image->id)
                    ->update(['image_name' => $newPath]);
                $totalUpdated++;
            }
        }
        $this->info("✓ Updated {$totalUpdated} product images");

        // Update banner_images table
        $bannerCount = 0;
        if (DB::getSchemaBuilder()->hasTable('banner_images')) {
            $this->info('Updating banner_images table...');
            $bannerImages = DB::table('banner_images')->get();
            foreach ($bannerImages as $image) {
                $oldPath = $image->image_name;
                $newPath = $this->convertPathToWebP($oldPath);

                if ($oldPath !== $newPath) {
                    DB::table('banner_images')
                        ->where('id', $image->id)
                        ->update(['image_name' => $newPath]);
                    $bannerCount++;
                    $totalUpdated++;
                }
            }
            $this->info("✓ Updated {$bannerCount} banner images");
        }

        // Update categories table
        $categoryCount = 0;
        if (DB::getSchemaBuilder()->hasTable('categories')) {
            $this->info('Updating categories table...');
            $categories = DB::table('categories')->whereNotNull('icon')->get();
            foreach ($categories as $category) {
                $oldPath = $category->icon;
                $newPath = $this->convertPathToWebP($oldPath);

                if ($oldPath !== $newPath) {
                    DB::table('categories')
                        ->where('id', $category->id)
                        ->update(['icon' => $newPath]);
                    $categoryCount++;
                    $totalUpdated++;
                }
            }
            $this->info("✓ Updated {$categoryCount} category icons");
        }

        // Update web_users table (avatars)
        $avatarCount = 0;
        if (DB::getSchemaBuilder()->hasTable('web_users')) {
            $this->info('Updating web_users table (avatars)...');
            $users = DB::table('web_users')->whereNotNull('avatar')->get();
            foreach ($users as $user) {
                $oldPath = $user->avatar;
                $newPath = $this->convertPathToWebP($oldPath);

                if ($oldPath !== $newPath) {
                    DB::table('web_users')
                        ->where('id', $user->id)
                        ->update(['avatar' => $newPath]);
                    $avatarCount++;
                    $totalUpdated++;
                }
            }
            $this->info("✓ Updated {$avatarCount} user avatars");
        }

        // Update retailer_shops table
        $shopCount = 0;
        if (DB::getSchemaBuilder()->hasTable('retailer_shops')) {
            $this->info('Updating retailer_shops table...');
            $shops = DB::table('retailer_shops')->get();
            foreach ($shops as $shop) {
                $updated = false;

                if ($shop->avatar) {
                    $oldPath = $shop->avatar;
                    $newPath = $this->convertPathToWebP($oldPath);
                    if ($oldPath !== $newPath) {
                        DB::table('retailer_shops')
                            ->where('id', $shop->id)
                            ->update(['avatar' => $newPath]);
                        $updated = true;
                    }
                }

                if ($shop->cover_image) {
                    $oldPath = $shop->cover_image;
                    $newPath = $this->convertPathToWebP($oldPath);
                    if ($oldPath !== $newPath) {
                        DB::table('retailer_shops')
                            ->where('id', $shop->id)
                            ->update(['cover_image' => $newPath]);
                        $updated = true;
                    }
                }

                if ($updated) {
                    $shopCount++;
                    $totalUpdated++;
                }
            }
            $this->info("✓ Updated {$shopCount} retailer shops");
        }

        // Update post_attributes table (images)
        $postAttrCount = 0;
        if (DB::getSchemaBuilder()->hasTable('post_attributes')) {
            $this->info('Updating post_attributes table...');
            $postAttributes = DB::table('post_attributes')
                ->where('attribute_value', 'like', '%.jpg')
                ->orWhere('attribute_value', 'like', '%.jpeg')
                ->orWhere('attribute_value', 'like', '%.png')
                ->orWhere('attribute_value', 'like', '%.gif')
                ->get();

            foreach ($postAttributes as $attr) {
                $oldPath = $attr->attribute_value;
                $newPath = $this->convertPathToWebP($oldPath);

                if ($oldPath !== $newPath) {
                    DB::table('post_attributes')
                        ->where('id', $attr->id)
                        ->update(['attribute_value' => $newPath]);
                    $postAttrCount++;
                    $totalUpdated++;
                }
            }
            $this->info("✓ Updated {$postAttrCount} post attributes");
        }

        // Update page_options_images table
        $pageOptionCount = 0;
        if (DB::getSchemaBuilder()->hasTable('page_options_images')) {
            $this->info('Updating page_options_images table...');
            $pageOptionImages = DB::table('page_options_images')->get();
            foreach ($pageOptionImages as $image) {
                $oldPath = $image->image_name;
                $newPath = $this->convertPathToWebP($oldPath);

                if ($oldPath !== $newPath) {
                    DB::table('page_options_images')
                        ->where('id', $image->id)
                        ->update(['image_name' => $newPath]);
                    $pageOptionCount++;
                    $totalUpdated++;
                }
            }
            $this->info("✓ Updated {$pageOptionCount} page option images");
        }

        $this->newLine();
        $this->info('════════════════════════════════════════');
        $this->info("  Total database records updated: {$totalUpdated}");
        $this->info('════════════════════════════════════════');
        $this->newLine();
        $this->info('✅ Database image paths updated successfully!');

        return Command::SUCCESS;
    }

    /**
     * Convert image path from old extension to .webp
     */
    private function convertPathToWebP(string $path): string
    {
        // Replace common image extensions with .webp
        return preg_replace('/\.(jpg|jpeg|png|gif)$/i', '.webp', $path);
    }
}
