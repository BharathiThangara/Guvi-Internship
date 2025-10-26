$(document).ready(function() {
    const sessionToken = localStorage.getItem('sessionToken');
    
    if (!sessionToken) {
        window.location.href = 'login.html';
        return;
    }
    
    loadProfileView();
    
    function loadProfileView() {
        $.ajax({
            url: 'php/view_profile.php',
            type: 'GET',
            dataType: 'json',
            headers: {
                'Authorization': sessionToken
            },
            success: function(response) {
                $('#loadingSpinner').addClass('d-none');
                
                if (response.success) {
                    const data = response.data;
                    
                    if (response.source === 'redis') {
                        $('#cacheBadge').removeClass('d-none');
                    }
                    
                    const initial = data.username ? data.username.charAt(0).toUpperCase() : 'U';
                    $('#avatarInitial').text(initial);
                    
                    $('#displayUsername').text(data.username || 'Unknown');
                    $('#displayEmail').text(data.email || 'No email');
                    
                    $('#displayAge').text(data.age || 'Not specified');
                    $('#displayDob').text(data.dob ? formatDate(data.dob) : 'Not specified');
                    $('#displayContact').text(data.contact || 'Not specified');
                    $('#displayAddress').text(data.address || 'Not specified');
                    
                    $('#profileDetails').removeClass('d-none');
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
                $('#loadingSpinner').addClass('d-none');
                showMessage('Failed to load profile data.', 'danger');
                console.error('Error:', error);
            }
        });
    }
    
    $('#editProfileBtn').on('click', function() {
        window.location.href = 'profile.html';
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
    
    function formatDate(dateString) {
        if (!dateString) return 'Not specified';
        const date = new Date(dateString);
        const options = { year: 'numeric', month: 'long', day: 'numeric' };
        return date.toLocaleDateString('en-US', options);
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