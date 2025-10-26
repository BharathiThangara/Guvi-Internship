$(document).ready(function() {
    const sessionToken = localStorage.getItem('sessionToken');
    if (sessionToken) {
        window.location.href = 'profile.html';
    }
    
    $('#loginForm').on('submit', function(e) {
        e.preventDefault();
        
        const formData = {
            username: $('#username').val(),
            password: $('#password').val()
        };
        
        $.ajax({
            url: 'php/login.php',
            type: 'POST',
            dataType: 'json',
            contentType: 'application/json',
            data: JSON.stringify(formData),
            success: function(response) {
                if (response.success) {
                    localStorage.setItem('sessionToken', response.sessionToken);
                    localStorage.setItem('username', response.username);
                    
                    showMessage(response.message, 'success');
                    setTimeout(function() {
                        window.location.href = 'profile.html';
                    }, 1000);
                } else {
                    showMessage(response.message, 'danger');
                }
            },
            error: function(xhr, status, error) {
                showMessage('Login failed. Please try again.', 'danger');
                console.error('Error:', error);
            }
        });
    });
    
    function showMessage(message, type) {
        const messageDiv = $('#loginMessage');
        messageDiv.removeClass('d-none alert-success alert-danger alert-warning');
        messageDiv.addClass('alert-' + type);
        messageDiv.text(message);
        messageDiv.fadeIn();
    }
});