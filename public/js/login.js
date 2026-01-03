
        // Feather Icons
        feather.replace();

        // Toggle password visibility
        function togglePassword() {
            const passInput = document.getElementById('password');
            const eyeIcon = document.getElementById('eyeIcon');
            const type = passInput.getAttribute('type') === 'password' ? 'text' : 'password';
            passInput.setAttribute('type', type);
            eyeIcon.setAttribute('data-feather', type === 'password' ? 'eye' : 'eye-off');
            feather.replace(); // refresh icon
        }

        // Optional: Toast helper (for login feedback)
        function showToast(message, type = 'info') {
            const toast = document.getElementById('toast');
            toast.textContent = message;
            toast.className = 'show';
            setTimeout(() => toast.classList.remove('show'), 3000);
        }

        // Form submit handling (prevent default for demo)
        document.getElementById('loginForm').addEventListener('submit', function(e) {
            // e.preventDefault(); // uncomment if handling via JS/AJAX
            // showToast('Mengirim data login...', 'info');
        });
