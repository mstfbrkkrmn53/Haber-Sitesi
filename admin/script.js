// Mobile Menu Toggle
const mobileMenuToggle = document.querySelector('.mobile-menu-toggle');
const adminSidebar = document.querySelector('.admin-sidebar');

mobileMenuToggle.addEventListener('click', () => {
    adminSidebar.classList.toggle('active');
});

// Close sidebar when clicking outside on mobile
document.addEventListener('click', (e) => {
    if (window.innerWidth <= 768 && 
        !adminSidebar.contains(e.target) && 
        !mobileMenuToggle.contains(e.target)) {
        adminSidebar.classList.remove('active');
    }
});

// Modal Functions
const modal = document.getElementById('confirmationModal');
const modalClose = document.querySelector('.modal-close');
const confirmButton = document.getElementById('confirmButton');

function showModal(callback) {
    modal.style.display = 'block';
    confirmButton.onclick = () => {
        callback();
        hideModal();
    };
}

function hideModal() {
    modal.style.display = 'none';
}

modalClose.addEventListener('click', hideModal);
document.querySelector('[data-dismiss="modal"]').addEventListener('click', hideModal);

// Close modal when clicking outside
window.addEventListener('click', (e) => {
    if (e.target === modal) {
        hideModal();
    }
});

// Loading Spinner
const loadingSpinner = document.querySelector('.loading');

function showLoading() {
    loadingSpinner.style.display = 'block';
}

function hideLoading() {
    loadingSpinner.style.display = 'none';
}

// AJAX Functions
function ajaxRequest(url, method = 'GET', data = null) {
    showLoading();
    
    return fetch(url, {
        method: method,
        headers: {
            'Content-Type': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        },
        body: data ? JSON.stringify(data) : null
    })
    .then(response => response.json())
    .finally(() => hideLoading());
}

// Delete Confirmation
function confirmDelete(url, itemName) {
    showModal(() => {
        ajaxRequest(url, 'DELETE')
            .then(response => {
                if (response.success) {
                    showNotification('success', `${itemName} başarıyla silindi.`);
                    setTimeout(() => window.location.reload(), 1000);
                } else {
                    showNotification('error', response.message || 'Bir hata oluştu.');
                }
            })
            .catch(error => {
                showNotification('error', 'Bir hata oluştu.');
                console.error('Error:', error);
            });
    });
}

// Notification System
function showNotification(type, message) {
    const notification = document.createElement('div');
    notification.className = `alert alert-${type}`;
    notification.textContent = message;
    
    document.querySelector('.content').prepend(notification);
    
    setTimeout(() => {
        notification.remove();
    }, 3000);
}

// Form Validation
function validateForm(form) {
    const requiredFields = form.querySelectorAll('[required]');
    let isValid = true;
    
    requiredFields.forEach(field => {
        if (!field.value.trim()) {
            field.classList.add('is-invalid');
            isValid = false;
        } else {
            field.classList.remove('is-invalid');
        }
    });
    
    return isValid;
}

// Image Preview
function previewImage(input, previewElement) {
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        
        reader.onload = function(e) {
            previewElement.src = e.target.result;
        }
        
        reader.readAsDataURL(input.files[0]);
    }
}

// Rich Text Editor
if (document.querySelector('.rich-editor')) {
    ClassicEditor
        .create(document.querySelector('.rich-editor'))
        .catch(error => {
            console.error(error);
        });
}

// Data Tables
if (document.querySelector('.admin-table')) {
    new DataTable('.admin-table', {
        language: {
            url: '//cdn.datatables.net/plug-ins/1.13.4/i18n/tr.json'
        },
        responsive: true
    });
}

// Date Picker
if (document.querySelector('.date-picker')) {
    flatpickr('.date-picker', {
        dateFormat: 'd.m.Y',
        locale: 'tr'
    });
}

// Select2
if (document.querySelector('.select2')) {
    $('.select2').select2({
        theme: 'bootstrap-5'
    });
}

// File Upload Preview
const fileInputs = document.querySelectorAll('input[type="file"]');
fileInputs.forEach(input => {
    input.addEventListener('change', function() {
        const preview = document.querySelector(`#${this.dataset.preview}`);
        if (preview) {
            previewImage(this, preview);
        }
    });
});

// Form Submit Handler
const forms = document.querySelectorAll('form[data-ajax="true"]');
forms.forEach(form => {
    form.addEventListener('submit', function(e) {
        e.preventDefault();
        
        if (!validateForm(this)) {
            showNotification('error', 'Lütfen tüm gerekli alanları doldurun.');
            return;
        }
        
        const formData = new FormData(this);
        const url = this.action;
        const method = this.method;
        
        showLoading();
        
        fetch(url, {
            method: method,
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showNotification('success', data.message);
                if (data.redirect) {
                    setTimeout(() => window.location.href = data.redirect, 1000);
                }
            } else {
                showNotification('error', data.message);
            }
        })
        .catch(error => {
            showNotification('error', 'Bir hata oluştu.');
            console.error('Error:', error);
        })
        .finally(() => hideLoading());
    });
}); 