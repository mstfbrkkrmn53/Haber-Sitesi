// Tema değiştirme
function toggleTheme() {
    const html = document.documentElement;
    const currentTheme = html.getAttribute('data-theme');
    const newTheme = currentTheme === 'dark' ? 'light' : 'dark';
    html.setAttribute('data-theme', newTheme);
    localStorage.setItem('theme', newTheme);
}

// Sayfa yüklendiğinde tema kontrolü
document.addEventListener('DOMContentLoaded', () => {
    const savedTheme = localStorage.getItem('theme');
    if (savedTheme) {
        document.documentElement.setAttribute('data-theme', savedTheme);
    }

    // Tema değiştirme butonu
    const themeToggle = document.createElement('button');
    themeToggle.className = 'theme-toggle';
    themeToggle.innerHTML = '🌓';
    themeToggle.onclick = toggleTheme;
    document.body.appendChild(themeToggle);
});

// Lazy loading için Intersection Observer
const lazyImages = document.querySelectorAll('img[data-src]');
const imageObserver = new IntersectionObserver((entries, observer) => {
    entries.forEach(entry => {
        if (entry.isIntersecting) {
            const img = entry.target;
            img.src = img.dataset.src;
            img.removeAttribute('data-src');
            observer.unobserve(img);
        }
    });
});

lazyImages.forEach(img => imageObserver.observe(img));

// Mobil menü
const mobileMenuButton = document.createElement('button');
mobileMenuButton.className = 'mobile-menu-button';
mobileMenuButton.innerHTML = '☰';
document.querySelector('header .container').prepend(mobileMenuButton);

mobileMenuButton.addEventListener('click', () => {
    const nav = document.querySelector('nav');
    nav.classList.toggle('active');
});

// Sayfa yüklendiğinde animasyon
document.addEventListener('DOMContentLoaded', () => {
    const elements = document.querySelectorAll('.news-card, .news-item');
    elements.forEach((element, index) => {
        element.style.animationDelay = `${index * 0.1}s`;
    });
});

// Sonsuz kaydırma
let isLoading = false;
let currentPage = 1;

window.addEventListener('scroll', () => {
    if (isLoading) return;

    const { scrollTop, scrollHeight, clientHeight } = document.documentElement;
    if (scrollTop + clientHeight >= scrollHeight - 100) {
        loadMoreContent();
    }
});

async function loadMoreContent() {
    isLoading = true;
    currentPage++;

    try {
        const response = await fetch(`/api/news?page=${currentPage}`);
        const data = await response.json();

        if (data.news.length > 0) {
            const newsGrid = document.querySelector('.news-grid');
            data.news.forEach(news => {
                const article = createNewsCard(news);
                newsGrid.appendChild(article);
            });
        }
    } catch (error) {
        console.error('Haberler yüklenirken hata oluştu:', error);
    } finally {
        isLoading = false;
    }
}

function createNewsCard(news) {
    const article = document.createElement('article');
    article.className = 'news-card';
    article.innerHTML = `
        <div class="news-image">
            <img src="${news.resim}" alt="${news.baslik}" loading="lazy">
        </div>
        <div class="news-content">
            <span class="category">${news.kategori_adi}</span>
            <h3><a href="/haber/${news.slug}">${news.baslik}</a></h3>
            <p>${news.ozet}</p>
            <div class="news-meta">
                <span><i class="fas fa-user"></i> ${news.yazar_adi}</span>
                <span><i class="fas fa-clock"></i> ${news.created_at}</span>
                <span><i class="fas fa-eye"></i> ${news.goruntulenme}</span>
            </div>
        </div>
    `;
    return article;
}

// Arama önerileri
const searchInput = document.querySelector('.search-input');
const searchSuggestions = document.querySelector('.search-suggestions');

if (searchInput && searchSuggestions) {
    let searchTimeout;

    searchInput.addEventListener('input', (e) => {
        clearTimeout(searchTimeout);
        const query = e.target.value.trim();

        if (query.length < 2) {
            searchSuggestions.style.display = 'none';
            return;
        }

        searchTimeout = setTimeout(async () => {
            try {
                const response = await fetch(`/api/search/suggestions?q=${encodeURIComponent(query)}`);
                const data = await response.json();

                if (data.suggestions.length > 0) {
                    searchSuggestions.innerHTML = data.suggestions
                        .map(suggestion => `<div class="suggestion">${suggestion}</div>`)
                        .join('');
                    searchSuggestions.style.display = 'block';
                } else {
                    searchSuggestions.style.display = 'none';
                }
            } catch (error) {
                console.error('Arama önerileri yüklenirken hata oluştu:', error);
            }
        }, 300);
    });

    // Önerilere tıklama
    searchSuggestions.addEventListener('click', (e) => {
        if (e.target.classList.contains('suggestion')) {
            searchInput.value = e.target.textContent;
            searchSuggestions.style.display = 'none';
            document.querySelector('.search-form').submit();
        }
    });

    // Dışarı tıklandığında önerileri kapat
    document.addEventListener('click', (e) => {
        if (!searchInput.contains(e.target) && !searchSuggestions.contains(e.target)) {
            searchSuggestions.style.display = 'none';
        }
    });
}

// Bildirim sistemi
function showNotification(message, type = 'info') {
    const notification = document.createElement('div');
    notification.className = `notification ${type}`;
    notification.textContent = message;

    document.body.appendChild(notification);

    setTimeout(() => {
        notification.classList.add('show');
    }, 100);

    setTimeout(() => {
        notification.classList.remove('show');
        setTimeout(() => {
            notification.remove();
        }, 300);
    }, 3000);
}

// Form doğrulama
function validateForm(form) {
    const inputs = form.querySelectorAll('input[required], textarea[required]');
    let isValid = true;

    inputs.forEach(input => {
        if (!input.value.trim()) {
            isValid = false;
            input.classList.add('error');
            
            const errorMessage = document.createElement('div');
            errorMessage.className = 'error-message';
            errorMessage.textContent = 'Bu alan zorunludur';
            
            input.parentNode.appendChild(errorMessage);
        } else {
            input.classList.remove('error');
            const errorMessage = input.parentNode.querySelector('.error-message');
            if (errorMessage) {
                errorMessage.remove();
            }
        }
    });

    return isValid;
}

// Form gönderimi
document.querySelectorAll('form').forEach(form => {
    form.addEventListener('submit', async (e) => {
        e.preventDefault();

        if (!validateForm(form)) {
            showNotification('Lütfen tüm zorunlu alanları doldurun', 'error');
            return;
        }

        const formData = new FormData(form);
        const submitButton = form.querySelector('button[type="submit"]');
        submitButton.disabled = true;

        try {
            const response = await fetch(form.action, {
                method: form.method,
                body: formData
            });

            const data = await response.json();

            if (data.success) {
                showNotification(data.message, 'success');
                if (data.redirect) {
                    window.location.href = data.redirect;
                }
            } else {
                showNotification(data.message, 'error');
            }
        } catch (error) {
            showNotification('Bir hata oluştu', 'error');
        } finally {
            submitButton.disabled = false;
        }
    });
});

// DOM yüklendiğinde çalışacak fonksiyonlar
document.addEventListener('DOMContentLoaded', function() {
    // Mobil menü toggle
    const mobileMenuToggle = document.querySelector('.mobile-menu-toggle');
    const nav = document.querySelector('nav');
    
    if (mobileMenuToggle && nav) {
        mobileMenuToggle.addEventListener('click', function() {
            nav.classList.toggle('active');
            this.classList.toggle('active');
        });
    }

    // Bildirimleri otomatik kapat
    const notifications = document.querySelectorAll('.notification');
    notifications.forEach(notification => {
        setTimeout(() => {
            notification.style.opacity = '0';
            setTimeout(() => {
                notification.remove();
            }, 300);
        }, 5000);
    });

    // Form validasyonu
    const forms = document.querySelectorAll('form[data-validate]');
    forms.forEach(form => {
        form.addEventListener('submit', function(e) {
            if (!validateForm(this)) {
                e.preventDefault();
            }
        });
    });

    // Lazy loading için Intersection Observer
    if ('IntersectionObserver' in window) {
        const imageObserver = new IntersectionObserver((entries, observer) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    const img = entry.target;
                    img.src = img.dataset.src;
                    img.classList.remove('lazy');
                    observer.unobserve(img);
                }
            });
        });

        document.querySelectorAll('img.lazy').forEach(img => {
            imageObserver.observe(img);
        });
    }

    // Smooth scroll
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function(e) {
            e.preventDefault();
            const target = document.querySelector(this.getAttribute('href'));
            if (target) {
                target.scrollIntoView({
                    behavior: 'smooth',
                    block: 'start'
                });
            }
        });
    });

    // Tooltip'leri etkinleştir
    const tooltips = document.querySelectorAll('[data-tooltip]');
    tooltips.forEach(tooltip => {
        tooltip.addEventListener('mouseenter', function() {
            const tooltipText = this.getAttribute('data-tooltip');
            const tooltipEl = document.createElement('div');
            tooltipEl.className = 'tooltip';
            tooltipEl.textContent = tooltipText;
            document.body.appendChild(tooltipEl);

            const rect = this.getBoundingClientRect();
            tooltipEl.style.top = rect.top - tooltipEl.offsetHeight - 10 + 'px';
            tooltipEl.style.left = rect.left + (rect.width - tooltipEl.offsetWidth) / 2 + 'px';
        });

        tooltip.addEventListener('mouseleave', function() {
            const tooltipEl = document.querySelector('.tooltip');
            if (tooltipEl) {
                tooltipEl.remove();
            }
        });
    });
});

// Form validasyon fonksiyonu
function validateForm(form) {
    let isValid = true;
    const requiredFields = form.querySelectorAll('[required]');
    
    requiredFields.forEach(field => {
        if (!field.value.trim()) {
            isValid = false;
            showError(field, 'Bu alan zorunludur');
        } else {
            clearError(field);
        }

        // Email validasyonu
        if (field.type === 'email' && field.value) {
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            if (!emailRegex.test(field.value)) {
                isValid = false;
                showError(field, 'Geçerli bir email adresi giriniz');
            }
        }

        // Şifre validasyonu
        if (field.type === 'password' && field.value) {
            if (field.value.length < 6) {
                isValid = false;
                showError(field, 'Şifre en az 6 karakter olmalıdır');
            }
        }
    });

    return isValid;
}

// Hata mesajı gösterme
function showError(field, message) {
    const errorDiv = field.parentElement.querySelector('.error-message') || document.createElement('div');
    errorDiv.className = 'error-message';
    errorDiv.textContent = message;
    
    if (!field.parentElement.querySelector('.error-message')) {
        field.parentElement.appendChild(errorDiv);
    }
    
    field.classList.add('error');
}

// Hata mesajını temizleme
function clearError(field) {
    const errorDiv = field.parentElement.querySelector('.error-message');
    if (errorDiv) {
        errorDiv.remove();
    }
    field.classList.remove('error');
}

// Bildirim gösterme fonksiyonu
function showNotification(message, type = 'info') {
    const notification = document.createElement('div');
    notification.className = `notification ${type}`;
    notification.innerHTML = `
        <i class="fas ${getNotificationIcon(type)}"></i>
        <span>${message}</span>
    `;
    
    document.body.appendChild(notification);
    
    setTimeout(() => {
        notification.style.opacity = '0';
        setTimeout(() => {
            notification.remove();
        }, 300);
    }, 5000);
}

// Bildirim tipine göre icon seçme
function getNotificationIcon(type) {
    switch (type) {
        case 'success':
            return 'fa-check-circle';
        case 'error':
            return 'fa-exclamation-circle';
        case 'warning':
            return 'fa-exclamation-triangle';
        default:
            return 'fa-info-circle';
    }
}

// AJAX istek fonksiyonu
async function fetchData(url, options = {}) {
    try {
        const response = await fetch(url, {
            ...options,
            headers: {
                'Content-Type': 'application/json',
                ...options.headers
            }
        });

        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }

        const data = await response.json();
        return data;
    } catch (error) {
        console.error('Fetch error:', error);
        showNotification('Bir hata oluştu. Lütfen tekrar deneyin.', 'error');
        throw error;
    }
}

// Tarih formatlama fonksiyonu
function formatDate(date, format = 'DD.MM.YYYY HH:mm') {
    const d = new Date(date);
    
    const day = String(d.getDate()).padStart(2, '0');
    const month = String(d.getMonth() + 1).padStart(2, '0');
    const year = d.getFullYear();
    const hours = String(d.getHours()).padStart(2, '0');
    const minutes = String(d.getMinutes()).padStart(2, '0');
    
    return format
        .replace('DD', day)
        .replace('MM', month)
        .replace('YYYY', year)
        .replace('HH', hours)
        .replace('mm', minutes);
}

// Sayfa yükleme animasyonu
function showLoading() {
    const loading = document.createElement('div');
    loading.className = 'loading';
    document.body.appendChild(loading);
}

function hideLoading() {
    const loading = document.querySelector('.loading');
    if (loading) {
        loading.remove();
    }
}

// Sayfa geçiş animasyonu
function pageTransition() {
    document.body.classList.add('page-transition');
    setTimeout(() => {
        document.body.classList.remove('page-transition');
    }, 500);
}

// Responsive menü
function toggleMenu() {
    const nav = document.querySelector('nav');
    const toggle = document.querySelector('.mobile-menu-toggle');
    
    if (nav && toggle) {
        nav.classList.toggle('active');
        toggle.classList.toggle('active');
    }
}

// Scroll to top butonu
function scrollToTop() {
    window.scrollTo({
        top: 0,
        behavior: 'smooth'
    });
}

// Scroll to top butonunu göster/gizle
window.addEventListener('scroll', function() {
    const scrollTopBtn = document.querySelector('.scroll-top');
    if (scrollTopBtn) {
        if (window.pageYOffset > 300) {
            scrollTopBtn.classList.add('show');
        } else {
            scrollTopBtn.classList.remove('show');
        }
    }
});

// Sayfa yüklendiğinde scroll to top butonunu ekle
document.addEventListener('DOMContentLoaded', function() {
    const scrollTopBtn = document.createElement('button');
    scrollTopBtn.className = 'scroll-top';
    scrollTopBtn.innerHTML = '<i class="fas fa-arrow-up"></i>';
    scrollTopBtn.addEventListener('click', scrollToTop);
    document.body.appendChild(scrollTopBtn);
}); 