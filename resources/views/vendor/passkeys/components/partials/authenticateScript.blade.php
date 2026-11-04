{{-- resources/views/vendor/passkeys/components/partials/authenticateScript.blade.php --}}
<script>
    async function authenticateWithPasskey(remember = false) {
        try {
            const response = await fetch('{{ route('passkeys.authentication_options') }}')
            const options = await response.json();
            const startAuthenticationResponse = await startAuthentication({ optionsJSON: options });
            const form = document.getElementById('passkey-login-form');

            form.addEventListener('formdata', ({formData}) => {
                formData.set('remember', remember);
                formData.set('start_authentication_response', JSON.stringify(startAuthenticationResponse));
            });
            form.submit();
        } catch (error) {
            Livewire.dispatch('passkeyActionFailed', {
                message: error.name === 'NotAllowedError'
                    ? 'Passkey sign-in was cancelled or timed out.'
                    : 'Something went wrong while signing in with your passkey.',
            });
        }
    }
</script>