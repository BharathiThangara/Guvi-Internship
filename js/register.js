$(document).ready(function() {
    $('#registerForm').on('submit', function(e) {
        e.preventDefault();
        
        const password = $('#password').val();
        const confirmPassword = $('#confirmPassword').val();
        
        if (password !== confirmPassword) {
            showMessage('Passwords do not match!', 'danger');
            return;
        }
        
        const formData = {
            username: $('#username').val(),
            email: $('#email').val(),
            password: password
        };
        
        $.ajax({
            url: 'php/register.php',
            type: 'POST',
            dataType: 'json',
            contentType: 'application/json',
            data: JSON.stringify(formData),
            success: function(response) {
                if (response.success) {
                    showMessage(response.message, 'success');
                    setTimeout(function() {
                        window.location.href = 'login.html';
                    }, 1500);
                } else {
                    showMessage(response.message, 'danger');
                }
            },
            error: function(xhr, status, error) {
                showMessage('Registration failed. Please try again.', 'danger');
                console.error('Error:', error);
            }
        });
    });
    
    function showMessage(message, type) {
        const messageDiv = $('#registerMessage');
        messageDiv.removeClass('d-none alert-success alert-danger alert-warning');
        messageDiv.addClass('alert-' + type);
        messageDiv.text(message);
        messageDiv.fadeIn();
    }
});