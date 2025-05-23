<?php

/**
 * Load a CSS file for a specific app
 * @param string $app The app name
 * @param string $file The CSS file name without extension
 */
function style($app, $file) {
    $cssPath = __DIR__ . "/{$app}/{$file}.css";
    if (file_exists($cssPath)) {
        echo "<link rel='stylesheet' href='{$app}/{$file}.css'>";
    }
}

/**
 * Load a JavaScript file for a specific app
 * @param string $app The app name
 * @param string $file The JavaScript file name without extension
 */
function script($app, $file) {
    $jsPath = __DIR__ . "/{$app}/{$file}.js";
    if (file_exists($jsPath)) {
        echo "<script src='{$app}/{$file}.js'></script>";
    }
} 