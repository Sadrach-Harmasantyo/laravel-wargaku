import preset from '../../../../vendor/filament/filament/tailwind.config.preset'

export default {
    presets: [preset],
    content: [
        './app/Filament/Warga/**/*.php',
        './resources/views/filament/warga/**/*.blade.php',
        './vendor/filament/**/*.blade.php',
    ],
}
