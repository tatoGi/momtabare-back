<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;
use Stichoza\GoogleTranslate\GoogleTranslate;

class TranslationService
{
    protected $translator;

    public function __construct()
    {
        $this->translator = new GoogleTranslate;
    }

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
                'translated' => $translated,
            ]);

            return $translated;

        } catch (\Exception $e) {
            Log::error('Translation failed', [
                'error' => $e->getMessage(),
                'text' => $text,
                'from' => $sourceLang,
                'to' => $targetLang,
            ]);

            return $text;
        }
    }

    public function translateProductFields(array $data, string $sourceLang, string $targetLang): array
    {
        $translated = [];

        if (isset($data['name']) && ! empty($data['name'])) {
            $translated['name'] = $this->translate($data['name'], $sourceLang, $targetLang);
        }

        if (isset($data['description']) && ! empty($data['description'])) {
            $translated['description'] = $this->translate($data['description'], $sourceLang, $targetLang);
        }

        if (isset($data['location']) && ! empty($data['location'])) {
            $translated['location'] = $this->translate($data['location'], $sourceLang, $targetLang);
        }

        if (isset($data['local_additional']) && is_array($data['local_additional'])) {
            $translated['local_additional'] = [];
            foreach ($data['local_additional'] as $key => $value) {
                if (! empty($value)) {
                    $translated['local_additional'][$key] = $this->translate($value, $sourceLang, $targetLang);
                } else {
                    $translated['local_additional'][$key] = $value;
                }
            }
        }

        return $translated;
    }

    public function translatePageFields(array $data, string $sourceLang, string $targetLang): array
    {
        $translated = [];

        if (isset($data['title']) && ! empty($data['title'])) {
            $translated['title'] = $this->translate($data['title'], $sourceLang, $targetLang);
        }

        if (isset($data['slug']) && ! empty($data['slug'])) {
            $translated['slug'] = $this->translate($data['slug'], $sourceLang, $targetLang);
            $translated['slug'] = strtolower(str_replace(' ', '-', $translated['slug']));
        }

        if (isset($data['keywords']) && ! empty($data['keywords'])) {
            $translated['keywords'] = $this->translate($data['keywords'], $sourceLang, $targetLang);
        }

        if (isset($data['desc']) && ! empty($data['desc'])) {
            // Extract text content from HTML but preserve structure
            $plainText = strip_tags($data['desc']);
            if (! empty(trim($plainText))) {
                // Translate the plain text
                $translatedText = $this->translate($plainText, $sourceLang, $targetLang);

                // If original had HTML tags, wrap translation in basic paragraph tags
                if ($data['desc'] !== $plainText) {
                    $translated['desc'] = '<p>'.nl2br($translatedText).'</p>';
                } else {
                    $translated['desc'] = $translatedText;
                }
            } else {
                $translated['desc'] = $data['desc'];
            }
        }

        return $translated;
    }

    /**
     * Auto-translate category fields from one language to another
     *
     * @param  array  $data  Array containing the translatable fields
     * @param  string  $sourceLang  Source language code (ka or en)
     * @param  string  $targetLang  Target language code (ka or en)
     * @return array Translated data
     */
    public function translateCategoryFields(array $data, string $sourceLang, string $targetLang): array
    {
        $translated = [];

        // Translate title
        if (isset($data['title']) && ! empty($data['title'])) {
            $translated['title'] = $this->translate($data['title'], $sourceLang, $targetLang);
        }

        // Translate slug
        if (isset($data['slug']) && ! empty($data['slug'])) {
            $translated['slug'] = $this->translate($data['slug'], $sourceLang, $targetLang);
            $translated['slug'] = strtolower(str_replace(' ', '-', $translated['slug']));
        }

        // Translate description
        if (isset($data['description']) && ! empty($data['description'])) {
            // Extract text content from HTML but preserve structure
            $plainText = strip_tags($data['description']);
            if (! empty(trim($plainText))) {
                // Translate the plain text
                $translatedText = $this->translate($plainText, $sourceLang, $targetLang);

                // If original had HTML tags, wrap translation in basic paragraph tags
                if ($data['description'] !== $plainText) {
                    $translated['description'] = '<p>'.nl2br($translatedText).'</p>';
                } else {
                    $translated['description'] = $translatedText;
                }
            } else {
                $translated['description'] = $data['description'];
            }
        }

        return $translated;
    }

    /**
     * Auto-translate banner fields from one language to another
     *
     * @param  array  $data  Array containing the translatable fields
     * @param  string  $sourceLang  Source language code (ka or en)
     * @param  string  $targetLang  Target language code (ka or en)
     * @return array Translated data
     */
    public function translateBannerFields(array $data, string $sourceLang, string $targetLang): array
    {
        $translated = [];

        // Translate title
        if (isset($data['title']) && ! empty($data['title'])) {
            $translated['title'] = $this->translate($data['title'], $sourceLang, $targetLang);
        }

        // Translate slug
        if (isset($data['slug']) && ! empty($data['slug'])) {
            $translated['slug'] = $this->translate($data['slug'], $sourceLang, $targetLang);
            $translated['slug'] = strtolower(str_replace(' ', '-', $translated['slug']));
        }

        // Translate description
        if (isset($data['desc']) && ! empty($data['desc'])) {
            // Extract text content from HTML but preserve structure
            $plainText = strip_tags($data['desc']);
            if (! empty(trim($plainText))) {
                // Translate the plain text
                $translatedText = $this->translate($plainText, $sourceLang, $targetLang);

                // If original had HTML tags, wrap translation in basic paragraph tags
                if ($data['desc'] !== $plainText) {
                    $translated['desc'] = '<p>'.nl2br($translatedText).'</p>';
                } else {
                    $translated['desc'] = $translatedText;
                }
            } else {
                $translated['desc'] = $data['desc'];
            }
        }

        return $translated;
    }

    /**
     * Auto-translate post fields from one language to another
     *
     * @param  array  $data  Array containing the translatable fields
     * @param  string  $sourceLang  Source language code (ka or en)
     * @param  string  $targetLang  Target language code (ka or en)
     * @return array Translated data
     */
    public function translatePostFields(array $data, string $sourceLang, string $targetLang): array
    {
        $translated = [];

        // Dynamically translate all fields provided
        foreach ($data as $fieldName => $fieldValue) {
            if (empty($fieldValue)) {
                continue;
            }

            // Special handling for slug fields - lowercase and replace spaces
            if (str_contains($fieldName, 'slug')) {
                $translated[$fieldName] = $this->translate($fieldValue, $sourceLang, $targetLang);
                $translated[$fieldName] = strtolower(str_replace(' ', '-', $translated[$fieldName]));

                continue;
            }

            // Check if field contains HTML content
            $plainText = strip_tags($fieldValue);

            if ($plainText !== $fieldValue && ! empty(trim($plainText))) {
                // Field has HTML tags - translate plain text and wrap back
                $translatedText = $this->translate($plainText, $sourceLang, $targetLang);
                $translated[$fieldName] = '<p>'.nl2br($translatedText).'</p>';
            } else {
                // Plain text field - translate directly
                $translated[$fieldName] = $this->translate($fieldValue, $sourceLang, $targetLang);
            }
        }

        return $translated;
    }
}
