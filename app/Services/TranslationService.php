<?php

namespace App\Services;

use Stichoza\GoogleTranslate\GoogleTranslate;
use Illuminate\Support\Facades\Log;

class TranslationService
{
    protected $translator;

    public function __construct()
    {
        $this->translator = new GoogleTranslate();
    }

    /**
     * Translate text from source language to target language
     *
     * @param string $text
     * @param string $sourceLang (e.g., 'ka' for Georgian, 'en' for English)
     * @param string $targetLang
     * @return string|null
     */
    public function translate(string $text, string $sourceLang, string $targetLang): ?string
    {
        try {
            if (empty(trim($text))) {
                return null;
            }

            $this->translator->setSource($sourceLang);
            $this->translator->setTarget($targetLang);

            $translated = $this->translator->translate($text);

            Log::info("Translation: {$sourceLang} -> {$targetLang}", [
                'original' => $text,
                'translated' => $translated
            ]);

            return $translated;

        } catch (\Exception $e) {
            Log::error('Translation failed', [
                'error' => $e->getMessage(),
                'text' => $text,
                'from' => $sourceLang,
                'to' => $targetLang
            ]);

            // Return original text if translation fails
            return $text;
        }
    }

    /**
     * Auto-translate product fields from one language to another
     *
     * @param array $data Array containing the translatable fields
     * @param string $sourceLang Source language code (ka or en)
     * @param string $targetLang Target language code (ka or en)
     * @return array Translated data
     */
    public function translateProductFields(array $data, string $sourceLang, string $targetLang): array
    {
        $translated = [];

        // Translate main fields
        if (isset($data['name']) && !empty($data['name'])) {
            $translated['name'] = $this->translate($data['name'], $sourceLang, $targetLang);
        }

        if (isset($data['description']) && !empty($data['description'])) {
            $translated['description'] = $this->translate($data['description'], $sourceLang, $targetLang);
        }

        if (isset($data['location']) && !empty($data['location'])) {
            $translated['location'] = $this->translate($data['location'], $sourceLang, $targetLang);
        }

        // Translate additional fields (brand, color, size, style, etc.)
        if (isset($data['local_additional']) && is_array($data['local_additional'])) {
            $translated['local_additional'] = [];
            foreach ($data['local_additional'] as $key => $value) {
                if (!empty($value)) {
                    $translated['local_additional'][$key] = $this->translate($value, $sourceLang, $targetLang);
                } else {
                    $translated['local_additional'][$key] = $value;
                }
            }
        }

        return $translated;
    }
}
