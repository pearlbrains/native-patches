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

    private function applyPatch(string $path, string $original, string $patched, string $label): void
    {
        $contents = file_get_contents($path);

        if (str_contains($contents, 'handleNativeBack')) {
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
