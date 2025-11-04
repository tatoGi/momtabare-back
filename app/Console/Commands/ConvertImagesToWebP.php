<?php

namespace App\Console\Commands;

use App\Services\ImageService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class ConvertImagesToWebP extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'images:convert-to-webp
                            {--directory= : Specific directory to convert (e.g., products)}
                            {--quality=80 : WebP quality (0-100)}
                            {--keep-originals : Keep original images after conversion (by default they are deleted)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Convert existing images to WebP format and replace originals (use --keep-originals to preserve them)';

    protected ImageService $imageService;

    /**
     * Execute the console command.
     */
    public function handle(ImageService $imageService)
    {
        $this->imageService = $imageService;

        $directories = $this->option('directory')
            ? [$this->option('directory')]
            : ['products', 'avatars', 'banners', 'retailer/avatars', 'retailer/covers', 'retailer-shops/avatars', 'retailer-shops/covers'];

        $quality = (int) $this->option('quality');
        $deleteOriginals = !$this->option('keep-originals'); // Delete by default unless --keep-originals is set

        $this->info('Starting WebP conversion...');

        if ($deleteOriginals) {
            $this->warn('Original images will be DELETED after conversion.');
            $this->warn('Use --keep-originals flag to preserve them.');
        } else {
            $this->info('Original images will be KEPT (--keep-originals flag detected).');
        }

        $this->newLine();

        $totalConverted = 0;
        $totalFailed = 0;
        $totalSkipped = 0;

        foreach ($directories as $directory) {
            $this->info("Processing directory: {$directory}");

            if (!Storage::disk('public')->exists($directory)) {
                $this->warn("  Directory does not exist. Skipping...");
                continue;
            }

            $files = Storage::disk('public')->files($directory);
            $bar = $this->output->createProgressBar(count($files));
            $bar->start();

            foreach ($files as $file) {
                $ext = strtolower(pathinfo($file, PATHINFO_EXTENSION));

                // Skip if already WebP
                if ($ext === 'webp') {
                    $totalSkipped++;
                    $bar->advance();
                    continue;
                }

                // Only convert image files
                if (!in_array($ext, ['jpg', 'jpeg', 'png', 'gif'])) {
                    $totalSkipped++;
                    $bar->advance();
                    continue;
                }

                try {
                    // Convert to WebP and automatically delete original
                    $webpPath = $this->imageService->convertExistingToWebP($file, $quality, $deleteOriginals);
                    $totalConverted++;

                    $bar->advance();
                } catch (\Exception $e) {
                    $totalFailed++;
                    $bar->advance();
                    $this->newLine();
                    $this->error("  Failed: {$file} - {$e->getMessage()}");
                }
            }

            $bar->finish();
            $this->newLine(2);
        }

        // Summary
        $this->info('Conversion Summary:');
        $this->table(
            ['Status', 'Count'],
            [
                ['Converted', $totalConverted],
                ['Failed', $totalFailed],
                ['Skipped', $totalSkipped],
            ]
        );

        if ($deleteOriginals) {
            $this->info('✅ Original images have been replaced with WebP versions.');
        } else {
            $this->info('✅ Original images kept (--keep-originals flag used).');
        }

        $this->newLine();
        $this->info('✅ WebP conversion complete!');

        // Update database paths to match new WebP files
        if ($totalConverted > 0 && $deleteOriginals) {
            $this->newLine();
            $this->info('🔄 Updating database image paths...');
            $this->call('images:update-db-paths');
        }

        return Command::SUCCESS;
    }
}
