$(document).ready(function() {
    const sessionToken = localStorage.getItem('sessionToken');
    
    if (!sessionToken) {
        window.location.href = 'login.html';
        return;
    }
    
    loadProfile();
    
    function loadProfile() {
        $.ajax({
            url: 'php/profile.php',
            type: 'GET',
            dataType: 'json',
            headers: {
                'Authorization': sessionToken
            },
            success: function(response) {
                if (response.success) {
                    $('#username').val(response.data.username || '');
                    $('#email').val(response.data.email || '');
                    $('#age').val(response.data.age || '');
                    $('#dob').val(response.data.dob || '');
                    $('#contact').val(response.data.contact || '');
                    $('#address').val(response.data.address || '');
                } else {
                    showMessage(response.message, 'danger');
                    if (response.message === 'Invalid session') {
                        setTimeout(function() {
                            logout();
                        }, 1500);
                    }
                }
            },
            error: function(xhr, status, error) {
                showMessage('Failed to load profile data.', 'danger');
                console.error('Error:', error);
            }
        });
    }
    
    $('#profileForm').on('submit', function(e) {
        e.preventDefault();
        
        const formData = {
            age: $('#age').val(),
            dob: $('#dob').val(),
            contact: $('#contact').val(),
            address: $('#address').val()
        };
        
        $.ajax({
            url: 'php/profile.php',
            type: 'POST',
            dataType: 'json',
            contentType: 'application/json',
            headers: {
                'Authorization': sessionToken
            },
            data: JSON.stringify(formData),
            success: function(response) {
                if (response.success) {
                    showMessage(response.message, 'success');
                    // Redirect to view profile page after 1 second
                    setTimeout(function() {
                        window.location.href = 'view_profile.html';
                    }, 1000);
                } else {
                    showMessage(response.message, 'danger');
                    if (response.message === 'Invalid session') {
                        setTimeout(function() {
                            logout();
                        }, 1500);
                    }
                }
            },
            error: function(xhr, status, error) {
                showMessage('Failed to update profile.', 'danger');
                console.error('Error:', error);
            }
        });
    });
    
    $('#viewProfileBtn').on('click', function() {
        window.location.href = 'view_profile.html';
    });
    
    $('#logoutBtn').on('click', function() {
        logout();
    });
    
    function logout() {
        $.ajax({
            url: 'php/logout.php',
            type: 'POST',
            headers: {
                'Authorization': sessionToken
            },
            complete: function() {
                localStorage.removeItem('sessionToken');
                localStorage.removeItem('username');
                window.location.href = 'login.html';
            }
        });
    }
    
    function showMessage(message, type) {
        const messageDiv = $('#profileMessage');
        messageDiv.removeClass('d-none alert-success alert-danger alert-warning');
        messageDiv.addClass('alert-' + type);
        messageDiv.text(message);
        messageDiv.fadeIn();
        
        setTimeout(function() {
            messageDiv.fadeOut();
        }, 3000);
    }
});