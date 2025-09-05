<!DOCTYPE html>
<html>
<head>
    <title>Test Registration</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
</head>
<body class="bg-gray-100 min-h-screen flex items-center justify-center p-4">
    <div class="bg-white p-8 rounded-lg shadow-md w-full max-w-md">
        <h1 class="text-2xl font-bold mb-6 text-center">Test Registration</h1>
        
        <div id="message" class="mb-4 p-4 rounded hidden"></div>
        
        <form id="registerForm" class="space-y-4">
            @csrf
            
            <div>
                <label for="first_name" class="block text-sm font-medium text-gray-700">First Name</label>
                <input type="text" id="first_name" name="first_name" required
                       class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
            </div>
            
            <div>
                <label for="email" class="block text-sm font-medium text-gray-700">Email</label>
                <input type="email" id="email" name="email" required
                       class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
            </div>
            
            <div>
                <label for="password" class="block text-sm font-medium text-gray-700">Password</label>
                <input type="password" id="password" name="password" required minlength="8"
                       class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
            </div>
            
            <div>
                <label for="password_confirmation" class="block text-sm font-medium text-gray-700">Confirm Password</label>
                <input type="password" id="password_confirmation" name="password_confirmation" required minlength="8"
                       class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
            </div>
            
            <div>
                <button type="submit"
                        class="w-full flex justify-center py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                    Register
                </button>
            </div>
        </form>
        
        <div id="verificationInfo" class="mt-4 p-4 bg-blue-50 rounded-md hidden">
            <h3 class="font-medium text-blue-800">Registration Successful!</h3>
            <p class="text-sm text-blue-700 mt-1">Please check your email for the verification link.</p>
            <button id="resendVerification" class="mt-2 text-sm text-blue-600 hover:text-blue-800">Resend Verification Email</button>
        </div>
    </div>

    <script>
        document.getElementById('registerForm').addEventListener('submit', async function(e) {
            e.preventDefault();
            
            const formData = {
                first_name: document.getElementById('first_name').value,
                email: document.getElementById('email').value,
                password: document.getElementById('password').value,
                password_confirmation: document.getElementById('password_confirmation').value
            };
            
            try {
                const response = await fetch('/register', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify(formData)
                });
                
                const data = await response.json();
                
                if (response.ok) {
                    document.getElementById('registerForm').classList.add('hidden');
                    document.getElementById('verificationInfo').classList.remove('hidden');
                    
                    // Store email for potential resend
                    sessionStorage.setItem('pendingVerificationEmail', formData.email);
                } else {
                    showMessage(data.message || 'Registration failed. Please try again.', 'error');
                }
            } catch (error) {
                showMessage('An error occurred. Please try again.', 'error');
                console.error('Registration error:', error);
            }
        });
        
        document.getElementById('resendVerification').addEventListener('click', async function() {
            const email = sessionStorage.getItem('pendingVerificationEmail');
            if (!email) return;
            
            try {
                const response = await fetch('/resend-verification', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({ email })
                });
                
                const data = await response.json();
                showMessage(data.message || 'Verification email resent.', 'success');
            } catch (error) {
                showMessage('Failed to resend verification email.', 'error');
                console.error('Resend error:', error);
            }
        });
        
        function showMessage(message, type = 'info') {
            const messageDiv = document.getElementById('message');
            messageDiv.textContent = message;
            messageDiv.className = `p-4 rounded ${type === 'error' ? 'bg-red-100 text-red-700' : 'bg-green-100 text-green-700'}`;
            messageDiv.classList.remove('hidden');
            
            // Auto-hide after 5 seconds
            setTimeout(() => {
                messageDiv.classList.add('hidden');
            }, 5000);
        }
        
        // Check for verification token in URL (for email verification callback)
        if (window.location.pathname.startsWith('/verify-email/')) {
            const token = window.location.pathname.split('/').pop();
            verifyEmailToken(token);
        }
        
        async function verifyEmailToken(token) {
            try {
                const response = await fetch(`/verify-email/${token}`, {
                    method: 'GET',
                    headers: {
                        'Accept': 'application/json'
                    }
                });
                
                const data = await response.json();
                showMessage(data.message || 'Email verified successfully!', 'success');
                
                if (data.redirect) {
                    setTimeout(() => {
                        window.location.href = data.redirect;
                    }, 2000);
                }
            } catch (error) {
                showMessage('Failed to verify email. The link may be invalid or expired.', 'error');
                console.error('Verification error:', error);
            }
        }
    </script>
</body>
</html>
