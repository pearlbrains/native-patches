<?php

namespace Pearlbrains\NativePatches\Commands;

use Native\Mobile\Plugins\Commands\NativePluginHookCommand;

class PreCompileCommand extends NativePluginHookCommand
{
    protected $signature = 'nativephp:native-patches:pre-compile';

    protected $description = 'Patch generated native files before compilation';

    public function handle(): int
    {
        if ($this->isAndroid()) {
            $this->patchAndroidBackButton();
            $this->patchAudioPlugin();
        }

        if ($this->isIos()) {
            $this->patchIosBackButton();
        }

        return self::SUCCESS;
    }

    /**
     * Android: replace the default webView.goBack() callback with one that
     * asks window.handleNativeBack() first (defined in app.vue / f7ready).
     *
     * Returns true  → something was closed (modal/sheet/popup/player) — skip navigation.
     * Returns false → nothing was open — proceed with native back.
     */
    private function patchAndroidBackButton(): void
    {
        $path = $this->buildPath()
            .'/app/src/main/java/com/nativephp/mobile/ui/MainActivity.kt';

        if (! file_exists($path)) {
            $this->warn('Android: MainActivity.kt not found — skipping back-button patch.');

            return;
        }

        $original = <<<'KOTLIN'
        onBackPressedDispatcher.addCallback(this) {
            if (webView.canGoBack()) {
                webView.goBack()
            } else {
                finish()
            }
        }
KOTLIN;

        $patched = <<<'KOTLIN'
        onBackPressedDispatcher.addCallback(this) {
            webView.evaluateJavascript(
                "typeof window.handleNativeBack === 'function' ? String(window.handleNativeBack()) : 'false'"
            ) { result ->
                val handled = result?.trim()?.trim('"') == "true"
                if (!handled) {
                    if (webView.canGoBack()) {
                        webView.goBack()
                    } else {
                        finish()
                    }
                }
            }
        }
KOTLIN;

        $this->applyPatch($path, $original, $patched, 'Android back-button');
    }

    /**
     * iOS: intercept WKWebView back-forward navigation in decidePolicyFor
     * so that window.handleNativeBack() gets a chance to close modals first.
     *
     * The guard block is unique enough to serve as the patch anchor. We insert
     * the interception right after it, before the existing scheme-routing logic.
     */
    private function patchIosBackButton(): void
    {
        $path = $this->buildPath().'/NativePHP/ContentView.swift';

        if (! file_exists($path)) {
            $this->warn('iOS: ContentView.swift not found — skipping back-button patch.');

            return;
        }

        $original = <<<'SWIFT'
            guard let url = navigationAction.request.url else {
                decisionHandler(.allow)
                return
            }

            let scheme = url.scheme?.lowercased() ?? ""
SWIFT;

        $patched = <<<'SWIFT'
            guard let url = navigationAction.request.url else {
                decisionHandler(.allow)
                return
            }

            // Intercept back navigation — ask JS to handle modals/sheets/popups first.
            // handleNativeBack() (defined in app.vue) returns true when something was
            // closed, false when native back navigation should proceed normally.
            if navigationAction.navigationType == .backForward && webView.canGoBack {
                decisionHandler(.cancel)
                webView.evaluateJavaScript(
                    "typeof window.handleNativeBack === 'function' ? String(window.handleNativeBack()) : 'false'"
                ) { [weak webView] result, _ in
                    let handled = (result as? String)?
                        .trimmingCharacters(in: .whitespacesAndNewlines) == "true"
                    if !handled {
                        webView?.goBack()
                    }
                }
                return
            }

            let scheme = url.scheme?.lowercased() ?? ""
SWIFT;

        $this->applyPatch($path, $original, $patched, 'iOS back-button');
    }

    /**
     * Fix theunwindfront/nativephp-audio's AudioFunctions.kt so that JSONArray/JSONObject
     * parameters from BridgeRouter are correctly converted instead of failing the unsafe cast.
     *
     * Without this, Audio.setPlaylist always returns {success: false} and nothing plays.
     */
    private function patchAudioPlugin(): void
    {
        $path = $this->buildPath()
            .'/app/src/main/java/com/theunwindfront/audio/AudioFunctions.kt';

        if (! file_exists($path)) {
            $this->warn('Android: AudioFunctions.kt not found — skipping audio plugin patch.');

            return;
        }

        $helpers = <<<'KOTLIN'
        private const val EVENT_PREFIX = "Theunwindfront\\Audio\\Events\\"

        @Suppress("UNCHECKED_CAST")
        private fun jsonObjectToMap(obj: JSONObject): Map<String, Any> {
            val map = mutableMapOf<String, Any>()
            for (key in obj.keys()) {
                map[key] = obj.get(key)
            }
            return map
        }

        @Suppress("UNCHECKED_CAST")
        private fun jsonArrayToList(arr: JSONArray): List<Map<String, Any>> {
            val list = mutableListOf<Map<String, Any>>()
            for (i in 0 until arr.length()) {
                val item = arr.get(i)
                if (item is JSONObject) list.add(jsonObjectToMap(item))
            }
            return list
        }

        private fun sendEvent(name: String, payload: Map<String, Any>) {
KOTLIN;

        $this->applyPatch(
            $path,
            '        private const val EVENT_PREFIX = "Theunwindfront\\\\Audio\\\\Events\\\\"'."\n\n        private fun sendEvent(name: String, payload: Map<String, Any>) {",
            $helpers,
            'AudioPlugin helpers',
            'private fun jsonArrayToList'
        );

        $this->applyPatch(
            $path,
            '            val items = parameters["items"] as? List<Map<String, Any>> ?: return mapOf("success" to false)',
            <<<'KOTLIN'
            val raw = parameters["items"]
            val items: List<Map<String, Any>> = when (raw) {
                is JSONArray -> jsonArrayToList(raw)
                is List<*> -> @Suppress("UNCHECKED_CAST") (raw as? List<Map<String, Any>>) ?: return mapOf("success" to false)
                else -> return mapOf("success" to false)
            }
KOTLIN,
            'AudioPlugin SetPlaylist',
            'is JSONArray -> jsonArrayToList(raw)'
        );

        $this->applyPatch(
            $path,
            '            val track = parameters["track"] as? Map<String, Any> ?: return mapOf("success" to false)',
            <<<'KOTLIN'
            val raw = parameters["track"]
            val track: Map<String, Any> = when (raw) {
                is JSONObject -> jsonObjectToMap(raw)
                is Map<*, *> -> @Suppress("UNCHECKED_CAST") (raw as? Map<String, Any>) ?: return mapOf("success" to false)
                else -> return mapOf("success" to false)
            }
KOTLIN,
            'AudioPlugin AppendTrack',
            'is JSONObject -> jsonObjectToMap(raw)'
        );
    }

    private function applyPatch(string $path, string $original, string $patched, string $label, string $guard = 'handleNativeBack'): void
    {
        $contents = file_get_contents($path);

        if (str_contains($contents, $guard)) {
            $this->info("{$label} patch already applied.");

            return;
        }

        if (! str_contains($contents, $original)) {
            $this->warn("{$label} patch: expected anchor not found — skipping.");

            return;
        }

        file_put_contents($path, str_replace($original, $patched, $contents));

        $this->info("Patched: {$label}");
    }
}
