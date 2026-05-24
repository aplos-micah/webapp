<?php

/**
 * RichTextArea
 *
 * Renders a WYSIWYG-enabled textarea (Jodit) wrapped in a form-group div.
 * Jodit is initialized automatically by app.js on any element with data-wysiwyg.
 * Setting readonly=true suppresses the editor and renders a plain textarea.
 *
 * Config keys:
 *   name        string  — name attribute (required)
 *   id          string  — id attribute; defaults to name
 *   label       string  — label text; omit or set '' to suppress the <label>
 *   value       string  — pre-filled HTML content
 *   placeholder string  — placeholder text
 *   rows        int     — fallback textarea rows before Jodit loads (default 6)
 *   required    bool    — adds required attribute (default false)
 *   readonly    bool    — renders plain textarea without data-wysiwyg (default false)
 *   class       string  — extra CSS classes appended to the textarea
 *   preset      string  — toolbar preset: 'simple' (default), 'moderate', or 'full'
 *
 * Usage:
 *   <?= RichTextArea::render([
 *       'name'        => 'content',
 *       'label'       => 'Article Content',
 *       'value'       => $article['content'] ?? '',
 *       'placeholder' => 'Write here…',
 *       'rows'        => 20,
 *       'required'    => true,
 *   ]) ?>
 */
class RichTextArea
{
    public static function render(array $cfg): string
    {
        $name        = $cfg['name']        ?? '';
        $id          = $cfg['id']          ?? $name;
        $label       = $cfg['label']       ?? '';
        $value       = $cfg['value']       ?? '';
        $placeholder = $cfg['placeholder'] ?? '';
        $rows        = (int) ($cfg['rows'] ?? 6);
        $required    = !empty($cfg['required']);
        $readonly    = !empty($cfg['readonly']);
        $extra       = trim($cfg['class']  ?? '');
        $preset      = in_array($cfg['preset'] ?? '', ['moderate', 'full'], true)
                           ? $cfg['preset']
                           : 'simple';

        $e = [self::class, 'escape'];

        $classes = 'input' . ($extra !== '' ? ' ' . $extra : '');

        $attrs  = ' id="'   . $e($id)   . '"';
        $attrs .= ' name="' . $e($name) . '"';
        $attrs .= ' class="' . $e($classes) . '"';
        $attrs .= ' rows="'  . $rows . '"';
        if (!$readonly) {
            $attrs .= ' data-wysiwyg="' . $preset . '"';
        }
        if ($placeholder !== '') {
            $attrs .= ' placeholder="' . $e($placeholder) . '"';
        }
        if ($required) {
            $attrs .= ' required';
        }

        $html = '<div class="form-group">';
        if ($label !== '') {
            $html .= '<label class="form-label" for="' . $e($id) . '">' . $e($label) . '</label>';
        }
        $html .= '<textarea' . $attrs . '>' . $e($value) . '</textarea>';
        $html .= '</div>';

        return $html;
    }

    private static function escape(string $v): string
    {
        return htmlspecialchars($v, ENT_QUOTES, 'UTF-8');
    }
}
