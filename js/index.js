document.addEventListener('DOMContentLoaded', function() {
    const registerBtn = document.getElementById('registerBtn');
    const loginBtn = document.getElementById('loginBtn');
    
    registerBtn.addEventListener('click', function() {
        window.location.href = 'register.html';
    });
    
    loginBtn.addEventListener('click', function() {
        window.location.href = 'login.html';
    });
});