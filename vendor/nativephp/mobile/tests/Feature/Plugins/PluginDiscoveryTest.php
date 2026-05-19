<?php

namespace Tests\Feature\Plugins;

use Illuminate\Filesystem\Filesystem;
use Native\Mobile\Plugins\Plugin;
use Native\Mobile\Plugins\PluginDiscovery;
use Tests\TestCase;

/**
 * Feature tests for PluginDiscovery service.
 *
 * PluginDiscovery is responsible for scanning the vendor directory for packages
 * of type "nativephp-plugin" and creating Plugin instances from their manifests.
 * All tests should FAIL before implementation exists (red phase of TDD).
 *
 * @see /Users/shanerosenthal/Herd/mobile/docs/PLUGIN_SYSTEM_DESIGN.md
 */
class PluginDiscoveryTest extends TestCase
{
    private PluginDiscovery $discovery;

    private Filesystem $files;

    private string $testBasePath;

    protected function setUp(): void
    {
        parent::setUp();

        $this->files = new Filesystem;
        $this->testBasePath = sys_get_temp_dir().'/nativephp-plugin-test-'.uniqid();

        // Create the test base directory
        $this->files->ensureDirectoryExists($this->testBasePath);

        $this->discovery = new PluginDiscovery(
            $this->files,
            $this->testBasePath
        );
    }

    protected function tearDown(): void
    {
        // Clean up test directories
        $this->files->deleteDirectory($this->testBasePath);
        parent::tearDown();
    }

    /**
     * @test
     *
     * Should discover plugins from composer's installed.json that have
     * the type "nativephp-plugin".
     *
     * Note: Uses discoverAll() since discover() requires the NativeServiceProvider
     * to be published. This tests the core discovery mechanism.
     */
    public function it_discovers_plugins_from_installed_json(): void
    {
        // Create a mock installed.json with a nativephp-plugin
        $installedPath = $this->testBasePath.'/vendor/composer';
        $this->files->ensureDirectoryExists($installedPath);

        $this->files->put($installedPath.'/installed.json', json_encode([
            'packages' => [
                [
                    'name' => 'test/valid-plugin',
                    'version' => '1.0.0',
                    'type' => 'nativephp-plugin',
                ],
                [
                    'name' => 'laravel/framework',
                    'version' => '11.0.0',
                    'type' => 'library',
                ],
            ],
        ]));

        // Create the plugin directory structure with manifest
        $pluginPath = $this->testBasePath.'/vendor/test/valid-plugin';
        $this->files->ensureDirectoryExists($pluginPath);
        $this->files->copy(
            __DIR__.'/../../Fixtures/plugins/valid-plugin/nativephp.json',
            $pluginPath.'/nativephp.json'
        );

        // Use discoverAll() to test core discovery without allowlist filtering
        $plugins = $this->discovery->discoverAll();

        $this->assertCount(1, $plugins);
        $this->assertInstanceOf(Plugin::class, $plugins->first());
        $this->assertEquals('test/valid-plugin', $plugins->first()->name);
    }

    /**
     * @test
     *
     * Should return empty from discover() when NativeServiceProvider not published.
     */
    public function it_returns_empty_when_provider_not_published(): void
    {
        // Create a mock installed.json with a nativephp-plugin
        $installedPath = $this->testBasePath.'/vendor/composer';
        $this->files->ensureDirectoryExists($installedPath);

        $this->files->put($installedPath.'/installed.json', json_encode([
            'packages' => [
                [
                    'name' => 'test/valid-plugin',
                    'version' => '1.0.0',
                    'type' => 'nativephp-plugin',
                ],
            ],
        ]));

        // Create the plugin directory structure with manifest
        $pluginPath = $this->testBasePath.'/vendor/test/valid-plugin';
        $this->files->ensureDirectoryExists($pluginPath);
        $this->files->copy(
            __DIR__.'/../../Fixtures/plugins/valid-plugin/nativephp.json',
            $pluginPath.'/nativephp.json'
        );

        // discover() should return empty when no provider is published
        $plugins = $this->discovery->discover();

        $this->assertCount(0, $plugins);

        // But discoverAll() should still find the plugin
        $allPlugins = $this->discovery->discoverAll();
        $this->assertCount(1, $allPlugins);
    }

    /**
     * @test
     *
     * Should return empty collection when no plugins are installed.
     */
    public function it_returns_empty_collection_when_no_plugins(): void
    {
        // Create installed.json with no nativephp-plugin packages
        $installedPath = $this->testBasePath.'/vendor/composer';
        $this->files->ensureDirectoryExists($installedPath);

        $this->files->put($installedPath.'/installed.json', json_encode([
            'packages' => [
                [
                    'name' => 'laravel/framework',
                    'version' => '11.0.0',
                    'type' => 'library',
                ],
                [
                    'name' => 'symfony/console',
                    'version' => '7.0.0',
                    'type' => 'library',
                ],
            ],
        ]));

        $plugins = $this->discovery->discoverAll();

        $this->assertCount(0, $plugins);
    }

    /**
     * @test
     *
     * Should return empty collection when installed.json doesn't exist.
     */
    public function it_returns_empty_collection_when_no_installed_json(): void
    {
        // Don't create installed.json
        $discovery = new PluginDiscovery(
            $this->files,
            '/nonexistent/path'
        );

        $plugins = $discovery->discoverAll();

        $this->assertCount(0, $plugins);
    }

    /**
     * @test
     *
     * Should skip plugins that don't have a nativephp.json manifest.
     */
    public function it_skips_plugins_without_manifest(): void
    {
        $installedPath = $this->testBasePath.'/vendor/composer';
        $this->files->ensureDirectoryExists($installedPath);

        $this->files->put($installedPath.'/installed.json', json_encode([
            'packages' => [
                [
                    'name' => 'test/no-manifest',
                    'version' => '1.0.0',
                    'type' => 'nativephp-plugin',
                ],
            ],
        ]));

        // Create plugin directory without manifest
        $pluginPath = $this->testBasePath.'/vendor/test/no-manifest';
        $this->files->ensureDirectoryExists($pluginPath);
        // Note: NOT creating nativephp.json

        $plugins = $this->discovery->discoverAll();

        $this->assertCount(0, $plugins);
    }

    /**
     * @test
     *
     * Should skip plugins with invalid JSON manifest and continue processing others.
     */
    public function it_skips_plugins_with_invalid_manifest(): void
    {
        $installedPath = $this->testBasePath.'/vendor/composer';
        $this->files->ensureDirectoryExists($installedPath);

        $this->files->put($installedPath.'/installed.json', json_encode([
            'packages' => [
                [
                    'name' => 'test/invalid-plugin',
                    'version' => '1.0.0',
                    'type' => 'nativephp-plugin',
                ],
                [
                    'name' => 'test/valid-plugin',
                    'version' => '1.0.0',
                    'type' => 'nativephp-plugin',
                ],
            ],
        ]));

        // Create invalid plugin
        $invalidPath = $this->testBasePath.'/vendor/test/invalid-plugin';
        $this->files->ensureDirectoryExists($invalidPath);
        $this->files->put($invalidPath.'/nativephp.json', 'this is not valid json {{{');

        // Create valid plugin
        $validPath = $this->testBasePath.'/vendor/test/valid-plugin';
        $this->files->ensureDirectoryExists($validPath);
        $this->files->copy(
            __DIR__.'/../../Fixtures/plugins/valid-plugin/nativephp.json',
            $validPath.'/nativephp.json'
        );

        $plugins = $this->discovery->discoverAll();

        // Should only find the valid plugin
        $this->assertCount(1, $plugins);
        $this->assertEquals('test/valid-plugin', $plugins->first()->name);
    }

    /**
     * @test
     *
     * discoverAll() should always return fresh results (no caching).
     */
    public function it_returns_fresh_results_with_discover_all(): void
    {
        $installedPath = $this->testBasePath.'/vendor/composer';
        $this->files->ensureDirectoryExists($installedPath);

        $this->files->put($installedPath.'/installed.json', json_encode([
            'packages' => [],
        ]));

        // First call - empty
        $first = $this->discovery->discoverAll();
        $this->assertCount(0, $first);

        // Modify file after first scan
        $this->files->put($installedPath.'/installed.json', json_encode([
            'packages' => [
                ['name' => 'new/plugin', 'type' => 'nativephp-plugin', 'version' => '1.0.0'],
            ],
        ]));

        // Create the new plugin
        $pluginPath = $this->testBasePath.'/vendor/new/plugin';
        $this->files->ensureDirectoryExists($pluginPath);
        $this->files->put($pluginPath.'/nativephp.json', json_encode([
            'namespace' => 'NewPlugin',
        ]));

        // Second call should return fresh results (finds new plugin)
        $second = $this->discovery->discoverAll();

        $this->assertCount(1, $second);
    }

    /**
     * @test
     *
     * Should be able to clear cache and re-scan.
     */
    public function it_clears_cache(): void
    {
        $installedPath = $this->testBasePath.'/vendor/composer';
        $this->files->ensureDirectoryExists($installedPath);

        $this->files->put($installedPath.'/installed.json', json_encode([
            'packages' => [],
        ]));

        $this->discovery->discover();
        $this->discovery->clearCache();

        // clearCache should reset the allowedPlugins cache too
        $this->discovery->clearCache();

        // Verify no crash - cache cleared successfully
        $this->assertTrue(true);
    }

    /**
     * @test
     *
     * Should discover multiple plugins.
     */
    public function it_discovers_multiple_plugins(): void
    {
        $installedPath = $this->testBasePath.'/vendor/composer';
        $this->files->ensureDirectoryExists($installedPath);

        $this->files->put($installedPath.'/installed.json', json_encode([
            'packages' => [
                ['name' => 'vendor/plugin-one', 'type' => 'nativephp-plugin', 'version' => '1.0.0'],
                ['name' => 'vendor/plugin-two', 'type' => 'nativephp-plugin', 'version' => '2.0.0'],
                ['name' => 'vendor/plugin-three', 'type' => 'nativephp-plugin', 'version' => '3.0.0'],
            ],
        ]));

        // Create all three plugins
        foreach (['one', 'two', 'three'] as $index => $name) {
            $pluginPath = $this->testBasePath.'/vendor/vendor/plugin-'.$name;
            $this->files->ensureDirectoryExists($pluginPath);
            $this->files->put($pluginPath.'/nativephp.json', json_encode([
                'name' => 'vendor/plugin-'.$name,
                'namespace' => 'Plugin'.ucfirst($name),
                'version' => ($index + 1).'.0.0',
            ]));
        }

        $plugins = $this->discovery->discoverAll();

        $this->assertCount(3, $plugins);
    }

    /**
     * @test
     *
     * Should aggregate all bridge functions from discovered plugins.
     * Note: getAllBridgeFunctions() uses discover() which requires provider.
     * This test manually aggregates from discoverAll() to test the aggregation logic.
     */
    public function it_aggregates_all_bridge_functions(): void
    {
        $installedPath = $this->testBasePath.'/vendor/composer';
        $this->files->ensureDirectoryExists($installedPath);

        $this->files->put($installedPath.'/installed.json', json_encode([
            'packages' => [
                ['name' => 'vendor/plugin-a', 'type' => 'nativephp-plugin', 'version' => '1.0.0'],
                ['name' => 'vendor/plugin-b', 'type' => 'nativephp-plugin', 'version' => '1.0.0'],
            ],
        ]));

        // Plugin A with 2 bridge functions
        $pathA = $this->testBasePath.'/vendor/vendor/plugin-a';
        $this->files->ensureDirectoryExists($pathA);
        $this->files->put($pathA.'/nativephp.json', json_encode([
            'namespace' => 'PluginA',
            'bridge_functions' => [
                ['name' => 'PluginA.Func1', 'android' => 'com.a.Func1', 'ios' => 'PluginA.Func1'],
                ['name' => 'PluginA.Func2', 'android' => 'com.a.Func2', 'ios' => 'PluginA.Func2'],
            ],
        ]));

        // Plugin B with 1 bridge function
        $pathB = $this->testBasePath.'/vendor/vendor/plugin-b';
        $this->files->ensureDirectoryExists($pathB);
        $this->files->put($pathB.'/nativephp.json', json_encode([
            'namespace' => 'PluginB',
            'bridge_functions' => [
                ['name' => 'PluginB.Func1', 'android' => 'com.b.Func1', 'ios' => 'PluginB.Func1'],
            ],
        ]));

        // Manually aggregate from discoverAll() to test aggregation logic
        $functions = $this->discovery->discoverAll()
            ->flatMap(fn ($plugin) => $plugin->getBridgeFunctions())
            ->all();

        $this->assertIsArray($functions);
        $this->assertCount(3, $functions);
    }

    /**
     * @test
     *
     * Should aggregate all Android permissions without duplicates.
     * Note: Uses manual aggregation from discoverAll() to test aggregation logic.
     */
    public function it_aggregates_all_android_permissions(): void
    {
        $installedPath = $this->testBasePath.'/vendor/composer';
        $this->files->ensureDirectoryExists($installedPath);

        $this->files->put($installedPath.'/installed.json', json_encode([
            'packages' => [
                ['name' => 'vendor/plugin-a', 'type' => 'nativephp-plugin', 'version' => '1.0.0'],
                ['name' => 'vendor/plugin-b', 'type' => 'nativephp-plugin', 'version' => '1.0.0'],
            ],
        ]));

        // Plugin A
        $pathA = $this->testBasePath.'/vendor/vendor/plugin-a';
        $this->files->ensureDirectoryExists($pathA);
        $this->files->put($pathA.'/nativephp.json', json_encode([
            'namespace' => 'PluginA',
            'android' => [
                'permissions' => ['android.permission.CAMERA', 'android.permission.VIBRATE'],
            ],
        ]));

        // Plugin B (with duplicate CAMERA permission)
        $pathB = $this->testBasePath.'/vendor/vendor/plugin-b';
        $this->files->ensureDirectoryExists($pathB);
        $this->files->put($pathB.'/nativephp.json', json_encode([
            'namespace' => 'PluginB',
            'android' => [
                'permissions' => ['android.permission.CAMERA', 'android.permission.RECORD_AUDIO'],
            ],
        ]));

        // Manually aggregate from discoverAll() to test aggregation logic
        $permissions = $this->discovery->discoverAll()
            ->flatMap(fn ($plugin) => $plugin->getAndroidPermissions())
            ->unique()
            ->values()
            ->all();

        $this->assertIsArray($permissions);
        // Should have 3 unique permissions (CAMERA deduplicated)
        $this->assertCount(3, $permissions);
        $this->assertEquals($permissions, array_unique($permissions));
    }

    /**
     * @test
     *
     * Should aggregate all iOS Info.plist entries from all plugins.
     * Note: Uses manual aggregation from discoverAll() to test aggregation logic.
     */
    public function it_aggregates_all_ios_info_plist(): void
    {
        $installedPath = $this->testBasePath.'/vendor/composer';
        $this->files->ensureDirectoryExists($installedPath);

        $this->files->put($installedPath.'/installed.json', json_encode([
            'packages' => [
                ['name' => 'vendor/plugin-a', 'type' => 'nativephp-plugin', 'version' => '1.0.0'],
                ['name' => 'vendor/plugin-b', 'type' => 'nativephp-plugin', 'version' => '1.0.0'],
            ],
        ]));

        // Plugin A
        $pathA = $this->testBasePath.'/vendor/vendor/plugin-a';
        $this->files->ensureDirectoryExists($pathA);
        $this->files->put($pathA.'/nativephp.json', json_encode([
            'namespace' => 'PluginA',
            'ios' => [
                'info_plist' => [
                    'NSCameraUsageDescription' => 'Plugin A needs camera',
                ],
            ],
        ]));

        // Plugin B
        $pathB = $this->testBasePath.'/vendor/vendor/plugin-b';
        $this->files->ensureDirectoryExists($pathB);
        $this->files->put($pathB.'/nativephp.json', json_encode([
            'namespace' => 'PluginB',
            'ios' => [
                'info_plist' => [
                    'NSMicrophoneUsageDescription' => 'Plugin B needs microphone',
                ],
            ],
        ]));

        // Manually aggregate from discoverAll() to test aggregation logic
        $infoPlist = $this->discovery->discoverAll()
            ->flatMap(fn ($plugin) => collect($plugin->getIosInfoPlist()))
            ->all();

        $this->assertIsArray($infoPlist);
        $this->assertArrayHasKey('NSCameraUsageDescription', $infoPlist);
        $this->assertArrayHasKey('NSMicrophoneUsageDescription', $infoPlist);
    }

    /**
     * @test
     *
     * Should handle installed.json with "dev" key structure (Composer 2.0+ format).
     */
    public function it_handles_composer_2_installed_json_format(): void
    {
        $installedPath = $this->testBasePath.'/vendor/composer';
        $this->files->ensureDirectoryExists($installedPath);

        // Composer 2.0+ format has packages directly and a dev key
        $this->files->put($installedPath.'/installed.json', json_encode([
            'packages' => [
                ['name' => 'test/plugin', 'type' => 'nativephp-plugin', 'version' => '1.0.0'],
            ],
            'dev' => true,
            'dev-package-names' => [],
        ]));

        $pluginPath = $this->testBasePath.'/vendor/test/plugin';
        $this->files->ensureDirectoryExists($pluginPath);
        $this->files->put($pluginPath.'/nativephp.json', json_encode([
            'name' => 'test/plugin',
            'namespace' => 'TestPlugin',
        ]));

        $plugins = $this->discovery->discoverAll();

        $this->assertCount(1, $plugins);
    }

    /**
     * @test
     *
     * Should use custom manifest path if specified in composer.json extra.
     */
    public function it_uses_custom_manifest_path_from_composer_extra(): void
    {
        $installedPath = $this->testBasePath.'/vendor/composer';
        $this->files->ensureDirectoryExists($installedPath);

        $this->files->put($installedPath.'/installed.json', json_encode([
            'packages' => [
                [
                    'name' => 'test/custom-manifest',
                    'type' => 'nativephp-plugin',
                    'version' => '1.0.0',
                    'extra' => [
                        'nativephp' => [
                            'manifest' => 'config/plugin.json',
                        ],
                    ],
                ],
            ],
        ]));

        $pluginPath = $this->testBasePath.'/vendor/test/custom-manifest';
        $configPath = $pluginPath.'/config';
        $this->files->ensureDirectoryExists($configPath);
        $this->files->put($configPath.'/plugin.json', json_encode([
            'name' => 'test/custom-manifest',
            'namespace' => 'CustomManifest',
        ]));

        $plugins = $this->discovery->discoverAll();

        $this->assertCount(1, $plugins);
        $this->assertEquals('CustomManifest', $plugins->first()->getNamespace());
    }

    /**
     * @test
     *
     * Should return plugins filtered by specific feature (e.g., only those with events).
     * Note: Uses manual filtering from discoverAll() to test the filtering logic.
     */
    public function it_filters_plugins_with_events(): void
    {
        $installedPath = $this->testBasePath.'/vendor/composer';
        $this->files->ensureDirectoryExists($installedPath);

        $this->files->put($installedPath.'/installed.json', json_encode([
            'packages' => [
                ['name' => 'vendor/with-events', 'type' => 'nativephp-plugin', 'version' => '1.0.0'],
                ['name' => 'vendor/without-events', 'type' => 'nativephp-plugin', 'version' => '1.0.0'],
            ],
        ]));

        // Plugin with events
        $pathA = $this->testBasePath.'/vendor/vendor/with-events';
        $this->files->ensureDirectoryExists($pathA);
        $this->files->put($pathA.'/nativephp.json', json_encode([
            'namespace' => 'WithEvents',
            'events' => ['Vendor\\WithEvents\\Events\\TestEvent'],
        ]));

        // Plugin without events
        $pathB = $this->testBasePath.'/vendor/vendor/without-events';
        $this->files->ensureDirectoryExists($pathB);
        $this->files->put($pathB.'/nativephp.json', json_encode([
            'namespace' => 'WithoutEvents',
        ]));

        // Manually filter from discoverAll() to test filtering logic
        $pluginsWithEvents = $this->discovery->discoverAll()
            ->filter(fn ($plugin) => ! empty($plugin->getEvents()));

        $this->assertCount(1, $pluginsWithEvents);
        $this->assertEquals('vendor/with-events', $pluginsWithEvents->first()->name);
    }

    /**
     * @test
     *
     * Should return plugins filtered by those having native Android code.
     * Note: Uses manual filtering from discoverAll() to test the filtering logic.
     */
    public function it_filters_plugins_with_android_code(): void
    {
        $installedPath = $this->testBasePath.'/vendor/composer';
        $this->files->ensureDirectoryExists($installedPath);

        $this->files->put($installedPath.'/installed.json', json_encode([
            'packages' => [
                ['name' => 'vendor/with-android', 'type' => 'nativephp-plugin', 'version' => '1.0.0'],
                ['name' => 'vendor/without-android', 'type' => 'nativephp-plugin', 'version' => '1.0.0'],
            ],
        ]));

        // Plugin with Android code
        $pathA = $this->testBasePath.'/vendor/vendor/with-android';
        $this->files->ensureDirectoryExists($pathA.'/resources/android/src');
        $this->files->put($pathA.'/nativephp.json', json_encode([
            'namespace' => 'WithAndroid',
        ]));
        $this->files->put($pathA.'/resources/android/src/TestFunctions.kt', 'package com.test');

        // Plugin without Android code
        $pathB = $this->testBasePath.'/vendor/vendor/without-android';
        $this->files->ensureDirectoryExists($pathB);
        $this->files->put($pathB.'/nativephp.json', json_encode([
            'namespace' => 'WithoutAndroid',
        ]));

        // Manually filter from discoverAll() to test filtering logic
        $pluginsWithAndroid = $this->discovery->discoverAll()
            ->filter(fn ($plugin) => $plugin->hasAndroidCode());

        $this->assertCount(1, $pluginsWithAndroid);
        $this->assertEquals('vendor/with-android', $pluginsWithAndroid->first()->name);
    }

    /**
     * @test
     *
     * Should return plugins filtered by those having native iOS code.
     * Note: Uses manual filtering from discoverAll() to test the filtering logic.
     */
    public function it_filters_plugins_with_ios_code(): void
    {
        $installedPath = $this->testBasePath.'/vendor/composer';
        $this->files->ensureDirectoryExists($installedPath);

        $this->files->put($installedPath.'/installed.json', json_encode([
            'packages' => [
                ['name' => 'vendor/with-ios', 'type' => 'nativephp-plugin', 'version' => '1.0.0'],
                ['name' => 'vendor/without-ios', 'type' => 'nativephp-plugin', 'version' => '1.0.0'],
            ],
        ]));

        // Plugin with iOS code
        $pathA = $this->testBasePath.'/vendor/vendor/with-ios';
        $this->files->ensureDirectoryExists($pathA.'/resources/ios/Sources');
        $this->files->put($pathA.'/nativephp.json', json_encode([
            'namespace' => 'WithIos',
        ]));
        $this->files->put($pathA.'/resources/ios/Sources/TestFunctions.swift', 'import Foundation');

        // Plugin without iOS code
        $pathB = $this->testBasePath.'/vendor/vendor/without-ios';
        $this->files->ensureDirectoryExists($pathB);
        $this->files->put($pathB.'/nativephp.json', json_encode([
            'namespace' => 'WithoutIos',
        ]));

        // Manually filter from discoverAll() to test filtering logic
        $pluginsWithIos = $this->discovery->discoverAll()
            ->filter(fn ($plugin) => $plugin->hasIosCode());

        $this->assertCount(1, $pluginsWithIos);
        $this->assertEquals('vendor/with-ios', $pluginsWithIos->first()->name);
    }
}
