<x-filament-widgets::widget>
    <x-filament::section id="pwa-install-section" style="display: none; background: linear-gradient(135deg, #991b1b 0%, #7f1d1d 100%); color: white; border: none;">
        <div class="flex items-center justify-between gap-4">
            <div class="flex items-center gap-3">
                <div class="p-2 bg-white/20 rounded-lg">
                    <x-heroicon-o-arrow-down-tray class="w-6 h-6 text-white" />
                </div>
                <div>
                    <h2 class="text-lg font-bold leading-tight">Install Marlin POS</h2>
                    <p class="text-sm text-white/80">Install the app on your home screen for faster access and offline support.</p>
                </div>
            </div>
            <x-filament::button 
                id="pwa-install-button"
                color="white" 
                outlined
                style="background: white; color: #991b1b; font-weight: bold; border: none;"
            >
                Install Now
            </x-filament::button>
        </div>

        <script>
            let deferredPrompt;
            const installSection = document.getElementById('pwa-install-section');
            const installButton = document.getElementById('pwa-install-button');

            window.addEventListener('beforeinstallprompt', (e) => {
                // Prevent the mini-infobar from appearing on mobile
                e.preventDefault();
                // Stash the event so it can be triggered later.
                deferredPrompt = e;
                // Update UI notify the user they can install the PWA
                installSection.style.display = 'block';
            });

            installButton.addEventListener('click', async () => {
                if (!deferredPrompt) return;
                // Show the install prompt
                deferredPrompt.prompt();
                // Wait for the user to respond to the prompt
                const { outcome } = await deferredPrompt.userChoice;
                // Optionally, send analytics event with outcome of user choice
                console.log(`User response to the install prompt: ${outcome}`);
                // We've used the prompt, and can't use it again, throw it away
                deferredPrompt = null;
                // Hide the install section
                installSection.style.display = 'none';
            });

            window.addEventListener('appinstalled', () => {
                // Hide the app-provided install promotion
                installSection.style.display = 'none';
                // Clear the deferredPrompt so it can be garbage collected
                deferredPrompt = null;
                console.log('PWA was installed');
            });
        </script>
    </x-filament::section>
</x-filament-widgets::widget>
