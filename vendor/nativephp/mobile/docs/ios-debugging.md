# iOS Debugging Guide

NativePHP Mobile includes a built-in debug service for iOS development that enables hot reload, log retrieval, and file syncing - even on real devices connected via USB.

## Features

- **Hot Reload**: Automatically sync file changes to your running app
- **Log Retrieval**: Fetch Laravel and debug logs from the device
- **Real Device Support**: Full debugging support via USB using `iproxy`
- **Dynamic Port Selection**: Automatically finds available ports to avoid conflicts

## Requirements

### For Simulators
No additional requirements - everything works out of the box.

### For Real Devices
Install `libimobiledevice` for USB communication:

```bash
brew install libimobiledevice
```

This provides `iproxy` for USB port tunneling and `idevice_id` for device discovery.

## Commands

### `native:tail` - View Logs

Retrieve logs from a running iOS or Android app.

```bash
# Interactive - prompts for platform
php artisan native:tail

# iOS - auto-discovers debug service
php artisan native:tail ios

# iOS with specific UDID (auto-detects simulator vs real device)
php artisan native:tail ios --udid=00008110-000164482141801E

# Show more lines
php artisan native:tail ios --lines=200

# Follow mode (like tail -f)
php artisan native:tail ios --follow

# View debug logs instead of Laravel logs
php artisan native:tail ios --type=debug

# Android
php artisan native:tail android
```

#### Options

| Option | Description |
|--------|-------------|
| `platform` | `ios` or `android` |
| `--udid=` | Device UDID (optional - auto-discovers if not provided) |
| `--type=` | Log type: `laravel` (default) or `debug` |
| `--lines=` | Number of lines to show (default: 50) |
| `--follow` | Continuously poll for new logs |

### `native:watch` - Hot Reload

Watch for file changes and sync them to the running app.

```bash
# Interactive - prompts for platform and target
php artisan native:watch

# iOS - auto-detects simulators and connected devices
php artisan native:watch ios

# iOS with specific UDID (auto-detects if it's a simulator or real device)
php artisan native:watch ios B4FE1569-E68A-467C-8513-67AC85B17D27
```

The command automatically:
- Lists all running simulators AND connected real devices
- Auto-detects device type based on UDID format
- Sets up USB tunnel (iproxy) for real devices
- Uses direct file sync for simulators

#### Options

| Option | Description |
|--------|-------------|
| `platform` | `ios` or `android` |
| `target` | Device/simulator UDID (optional - prompts if not provided) |

### `native:run` with `--watch`

Build, deploy, and start watching in one command:

```bash
# Build and run with hot reload (works for both simulators and real devices)
php artisan native:run ios --watch
```

After the app launches, it automatically starts watching for file changes.

## Configuration

### Environment Variables

Add these to your `.env` file:

```env
# Preferred debug service port (default: 9000)
# If unavailable, will scan 9000-9999 for an open port
NATIVEPHP_DEBUG_PORT=9000
```

### Config File

In `config/nativephp.php`:

```php
/*
|--------------------------------------------------------------------------
| Debug Service Port
|--------------------------------------------------------------------------
|
| The preferred port for the iOS debug service (hot reload, log retrieval).
| If this port is unavailable, the service will scan ports 9000-9999 to
| find an available one. The actual port used is saved and can be
| discovered automatically by the development tools.
|
*/
'debug_port' => env('NATIVEPHP_DEBUG_PORT', 9000),

/*
|--------------------------------------------------------------------------
| Hot Reload Configuration
|--------------------------------------------------------------------------
*/
'hot_reload' => [
    'watch_paths' => [
        'app',
        'resources',
        'routes',
        'config',
        'public',
    ],

    'exclude_patterns' => [
        '\.git',
        'storage',
        'tests',
        'nativephp',
        'credentials',
        'node_modules',
        '\.swp',
        '\.tmp',
        '~',
        '\.log',
    ],
],
```

## How It Works

### Debug Service Architecture

The iOS app includes a `DebugService` that runs only in DEBUG builds. It:

1. Listens on a TCP port (default 9000, or scans 9000-9999)
2. Accepts JSON commands over the socket
3. Saves its active port to a file for discovery

### Communication Protocol

Commands are JSON objects with a `type` field:

```json
// Trigger hot reload
{"type": "reload"}

// Get logs
{"type": "logs", "logType": "laravel", "lines": 100}

// Push a file
{"type": "file", "path": "app/Models/User.php", "content": "base64..."}

// Health check
{"type": "ping"}
```

Responses are JSON with a `status` field:

```json
{"status": "ok", "type": "pong", "port": 9000}
{"status": "ok", "type": "logs", "content": "..."}
{"status": "error", "message": "Something went wrong"}
```

### Simulator Workflow

1. App starts and DebugService binds to the configured port
2. DebugService sends a "ready" callback to `localhost:PORT+1` (instant notification)
3. Dev tools receive the callback and know the app is ready immediately
4. Commands sent directly via localhost (shared network)

### Real Device Workflow

1. App starts and DebugService binds to configured port
2. Dev machine runs `iproxy` to tunnel the port over USB
3. Dev tools poll the debug service until it responds (up to 30 seconds)
4. Commands sent through the USB tunnel via localhost

Note: The "ready" callback only works for simulators (shared localhost). Real devices use polling since the device cannot directly reach the host over USB.

```
┌─────────────────┐     USB      ┌─────────────────┐
│   Dev Machine   │─────────────▶│   iOS Device    │
│                 │              │                 │
│  localhost:9000 │◀── iproxy ──│  localhost:9000 │
│                 │              │  (DebugService) │
└─────────────────┘              └─────────────────┘
```

## Port Configuration

The debug service uses a deterministic port configuration:

1. **Build Time**: When you run `php artisan native:run ios` with a debug build, it ensures `NATIVEPHP_DEBUG_PORT` is set in your `.env` file (defaults to 9000 if not configured)
2. **App Startup**: The iOS app reads this port from the bundled `.env` file and binds the debug service to it
3. **Dev Tools**:
   - **Simulators**: Connect directly to `localhost:NATIVEPHP_DEBUG_PORT` (simulator shares network with host)
   - **Real Devices**: Use `iproxy` to tunnel from any available local port to `NATIVEPHP_DEBUG_PORT` on the device

The port in `.env` is the device-side port. For real devices, the local port used for the tunnel can be different and is automatically selected.

## Troubleshooting

### "Could not connect to iOS debug service"

- Ensure the app is running on the device/simulator
- Verify the app was built in DEBUG mode (not release)
- For real devices, check USB connection and trust settings

### "iproxy not found"

Install libimobiledevice:
```bash
brew install libimobiledevice
```

### "No running iOS simulators found"

Start a simulator and launch the app first:
```bash
php artisan native:run ios
```

### Port conflicts

If you're running multiple apps, they'll automatically use different ports. You can also set a specific port:

```env
NATIVEPHP_DEBUG_PORT=9001
```

### Logs not updating

- Check that you're viewing the correct log type (`--type=laravel` vs `--type=debug`)
- Laravel logs are in `storage/logs/laravel.log`
- Debug logs are Swift-side logs in `nativephp_debug.log`

## Log Types

| Type | Description | Location |
|------|-------------|----------|
| `laravel` | Laravel application logs | `storage/logs/laravel.log` |
| `debug` | Swift-side debug logs | `nativephp_debug.log` |

The debug logs include:
- App startup sequence
- PHP environment setup
- Bridge function calls
- Debug service status
- File write operations during hot reload
