{{-- resources/views/vendor/passkeys/livewire/partials/createScript.blade.php --}}
@script
<script>
    Livewire.on('passkeyPropertiesValidated', async function (eventData) {
        const passkeyOptions = eventData[0].passkeyOptions;

        try {
            const passkey = await startRegistration({ optionsJSON: passkeyOptions });
            @this.call('storePasskey', JSON.stringify(passkey));
        } catch (error) {
            Livewire.dispatch('passkeyActionFailed', {
                message: error.name === 'NotAllowedError'
                    ? 'Passkey setup was cancelled or timed out.'
                    : 'Something went wrong while setting up your passkey.',
            });
        }
    });
</script>
@endscript