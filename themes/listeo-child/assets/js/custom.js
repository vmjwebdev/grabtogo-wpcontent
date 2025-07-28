jQuery(function($) {
    const form          = $('#gtg-reg-form');
    const emailField    = $('#gtg_email');
    const otpInput      = $('#gtg_otp_input');
    const sendOTPBtn    = $('#send_otp_btn');
    const verifyOTPBtn  = $('#verify_otp_btn');
    const otpSection    = $('#otp_section');
    const registerBtn   = $('#gtg_submit_btn');
    const otpStatus     = $('#otp_status');

    // Initially disable register button
    registerBtn.prop('disabled', true);

    // ===== Client-side validation: GST & Phone on submit =====
    form.on('submit', function(e) {
        const gst   = $('input[name="gtg_gst_number"]').val().trim();
        const phone = $('input[name="gtg_whatsapp"]').val().trim();
        const gstRegex   = /^[0-9]{2}[A-Z]{5}[0-9]{4}[A-Z][A-Z0-9]Z[A-Z0-9]$/;
        const phoneRegex = /^[6-9]\d{9}$/;

        if (!phoneRegex.test(phone)) {
            e.preventDefault();
            alert('Please enter a valid 10-digit Indian mobile number starting with 6-9.');
            return false;
        }
        if (!gstRegex.test(gst)) {
            e.preventDefault();
            alert('Please enter a valid 15-character GSTIN (e.g., 22ABCDE1234F1Z5).');
            return false;
        }
    });

    // ===== Send OTP =====
    sendOTPBtn.on('click', function () {
        const email = emailField.val().trim();
        if (!email) {
            alert('Please enter your email before requesting OTP.');
            return;
        }

        $.ajax({
            url: gtg_ajax.ajax_url,
            method: 'POST',
            data: {
                action: 'gtg_send_otp',
                email: email
            },
            beforeSend: function () {
                sendOTPBtn.text('Sending...').prop('disabled', true);
            },
            success: function (res) {
                if (res.success) {
                    otpStatus.html('<span style="color:green;">OTP sent to your email.</span>');
                    otpSection.show();
                } else {
                    otpStatus.html('<span style="color:red;">' + res.data + '</span>');
                }
                sendOTPBtn.text('Resend OTP').prop('disabled', false);
            }
        });
    });

    // ===== Verify OTP =====
    verifyOTPBtn.on('click', function () {
        const email = emailField.val().trim();
        const otp   = otpInput.val().trim();
        if (!otp) {
            alert('Please enter the OTP.');
            return;
        }

        $.ajax({
            url: gtg_ajax.ajax_url,
            method: 'POST',
            data: {
                action: 'gtg_verify_otp',
                email: email,
                otp: otp
            },
            success: function (res) {
                if (res.success) {
                    otpStatus.html('<span style="color:green;">OTP verified successfully.</span>');
                    sendOTPBtn.hide();
                    verifyOTPBtn.hide();
                    otpSection.hide();
                    registerBtn.prop('disabled', false);
                } else {
                    otpStatus.html('<span style="color:red;">' + res.data + '</span>');
                }
            }
        });
    });

    // ===== Toggle password visibility =====
    form.on('click', '.gtg-toggle-password', function() {
        const $inp  = $(this).siblings('input[type="password"],input[type="text"]');
        const isPwd = $inp.attr('type') === 'password';
        $inp.attr('type', isPwd ? 'text' : 'password');
        $(this).toggleClass('fa-eye fa-eye-slash');
    });

    // ============ STORIES FUNCTIONALITY ============
    
    let userCity = localStorage.getItem('gtg_user_city') || '';
    
    // Get user's geolocation for stories
    function getUserLocation() {
        if (navigator.geolocation && !userCity) {
            navigator.geolocation.getCurrentPosition(function(position) {
                // You could use a geocoding service here to convert lat/lng to city
                // For now, we'll try to get city from user profile or ask
                const savedCity = $('body').data('user-city') || prompt('Enter your city to see local stories:');
                if (savedCity) {
                    userCity = savedCity;
                    localStorage.setItem('gtg_user_city', userCity);
                    loadStories();
                }
            }, function() {
                // Fallback if geolocation fails
                const savedCity = prompt('Enter your city to see local stories:');
                if (savedCity) {
                    userCity = savedCity;
                    localStorage.setItem('gtg_user_city', userCity);
                    loadStories();
                }
            });
        } else if (userCity) {
            loadStories();
        }
    }
    
    // Load stories for user's location
    function loadStories() {
        const storiesContainer = $('#stories-container');
        if (!storiesContainer.length || !userCity) return;
        
        $.ajax({
            url: gtg_ajax.ajax_url,
            method: 'POST',
            data: {
                action: 'gtg_get_location_stories',
                user_city: userCity
            },
            success: function(res) {
                if (res.success && res.data.length > 0) {
                    let storiesHtml = '';
                    res.data.forEach(function(story) {
                        storiesHtml += `
                            <div class="story-item" data-story='${JSON.stringify(story)}'>
                                <div class="story-avatar">
                                    <img src="${story.media_url}" alt="${story.vendor_name}" 
                                         onerror="this.src='data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iNDAiIGhlaWdodD0iNDAiIHZpZXdCb3g9IjAgMCA0MCA0MCIgZmlsbD0ibm9uZSIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIj4KPHJlY3Qgd2lkdGg9IjQwIiBoZWlnaHQ9IjQwIiByeD0iMjAiIGZpbGw9IiNmNWY1ZjUiLz4KPHN2ZyB4PSI4IiB5PSI4IiB3aWR0aD0iMjQiIGhlaWdodD0iMjQiIHZpZXdCb3g9IjAgMCAyNCAyNCIgZmlsbD0ibm9uZSI+CjxwYXRoIGQ9Ik0xMiAxMkM5LjUgMTIgOC4yNSAxMSA3IDEwTTEyIDEyQzE0LjUgMTIgMTUuNzUgMTEgMTcgMTBNMTIgMTJWMTZNMTIgMTZINFYyMEgyMFYxNkgxMiIgc3Ryb2tlPSIjNjY2IiBzdHJva2Utd2lkdGg9IjEuNSIgc3Ryb2tlLWxpbmVjYXA9InJvdW5kIiBzdHJva2UtbGluZWpvaW49InJvdW5kIi8+Cjwvc3ZnPgo8L3N2Zz4K';">
                                </div>
                                <div class="story-vendor-name">${story.vendor_name}</div>
                            </div>
                        `;
                    });
                    storiesContainer.html(storiesHtml);
                } else {
                    storiesContainer.html('<div class="story-loading">No stories in your area yet</div>');
                }
            },
            error: function() {
                storiesContainer.html('<div class="story-loading">Failed to load stories</div>');
            }
        });
    }
    
    // Story modal functionality
    function createStoryModal() {
        if (!$('.story-modal').length) {
            $('body').append(`
                <div class="story-modal">
                    <div class="story-content">
                        <button class="story-close">&times;</button>
                        <img class="story-media" src="" alt="">
                        <div class="story-info">
                            <div class="story-vendor"></div>
                            <div class="story-caption"></div>
                        </div>
                    </div>
                </div>
            `);
        }
    }
    
    // Click handler for story items
    $(document).on('click', '.story-item', function() {
        const storyData = $(this).data('story');
        if (!storyData) return;
        
        createStoryModal();
        const modal = $('.story-modal');
        
        modal.find('.story-media').attr('src', storyData.media_url);
        modal.find('.story-vendor').text(storyData.vendor_name);
        modal.find('.story-caption').text(storyData.caption || '');
        modal.addClass('active');
        
        // Auto-hide after 5 seconds
        setTimeout(function() {
            modal.removeClass('active');
        }, 5000);
    });
    
    // Close story modal
    $(document).on('click', '.story-close, .story-modal', function(e) {
        if (e.target === this) {
            $('.story-modal').removeClass('active');
        }
    });
    
    // Initialize stories on page load
    if ($('.gtg-stories-carousel').length) {
        getUserLocation();
    }
    
    // ============ VENDOR STORY UPLOAD ============
    
    $('#gtg-story-form').on('submit', function(e) {
        e.preventDefault();
        
        const formData = new FormData();
        const fileInput = $('#story_media')[0];
        const caption = $('#story_caption').val();
        const expiryHours = $('#expiry_hours').val();
        
        if (!fileInput.files[0]) {
            alert('Please select an image or video to upload.');
            return;
        }
        
        // File size check (10MB)
        if (fileInput.files[0].size > 10 * 1024 * 1024) {
            alert('File size must be less than 10MB.');
            return;
        }
        
        formData.append('action', 'gtg_upload_vendor_story');
        formData.append('story_media', fileInput.files[0]);
        formData.append('caption', caption);
        formData.append('expiry_hours', expiryHours);
        
        const submitBtn = $(this).find('button[type="submit"]');
        const originalText = submitBtn.html();
        
        $.ajax({
            url: gtg_ajax.ajax_url,
            method: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            beforeSend: function() {
                submitBtn.html('<i class="fas fa-spinner fa-spin"></i> Uploading...').prop('disabled', true);
            },
            success: function(res) {
                if (res.success) {
                    alert('Story uploaded successfully!');
                    $('#gtg-story-form')[0].reset();
                    loadVendorStories();
                } else {
                    alert('Error: ' + res.data);
                }
            },
            error: function() {
                alert('Upload failed. Please try again.');
            },
            complete: function() {
                submitBtn.html(originalText).prop('disabled', false);
            }
        });
    });
    
    // Load vendor's own stories
    function loadVendorStories() {
        const vendorList = $('#vendor-stories-list');
        if (!vendorList.length) return;
        
        $.ajax({
            url: gtg_ajax.ajax_url,
            method: 'POST',
            data: {
                action: 'gtg_get_vendor_stories'
            },
            beforeSend: function() {
                vendorList.html('<div class="gtg-loading">Loading your stories...</div>');
            },
            success: function(res) {
                if (res.success && res.data.length > 0) {
                    let storiesHtml = '';
                    res.data.forEach(function(story) {
                        storiesHtml += `
                            <div class="vendor-story-item">
                                <div class="story-meta">
                                    <span class="story-date">${story.created}</span>
                                    <span class="story-status ${story.status}">${story.status}</span>
                                </div>
                                <div class="story-content">
                                    ${story.media_url ? `<img src="${story.media_url}" class="story-preview" alt="Story preview">` : ''}
                                    <div class="story-text">
                                        <h4>${story.title}</h4>
                                        ${story.caption ? `<p>${story.caption}</p>` : ''}
                                        <small>Expires: ${new Date(story.expiry).toLocaleDateString()}</small>
                                    </div>
                                </div>
                            </div>
                        `;
                    });
                    vendorList.html(storiesHtml);
                } else {
                    vendorList.html('<div class="gtg-message">No stories yet. Create your first story to engage with customers!</div>');
                }
            },
            error: function() {
                vendorList.html('<div class="gtg-message error">Failed to load stories. Please try again.</div>');
            }
        });
    }
    
    // Initialize vendor stories if on dashboard
    if ($('#vendor-stories-list').length) {
        loadVendorStories();
    }
    
    // ============ MOBILE UX ENHANCEMENTS ============
    
    // Add touch feedback for mobile cards
    if ('ontouchstart' in window) {
        $(document).on('touchstart', 'ul.products li.product, .story-item, .category-item', function() {
            $(this).addClass('touch-active');
        });
        
        $(document).on('touchend touchcancel', 'ul.products li.product, .story-item, .category-item', function() {
            const $this = $(this);
            setTimeout(function() {
                $this.removeClass('touch-active');
            }, 150);
        });
    }
    
    // Smooth scroll for horizontal carousels
    $('.stories-container, .gtg-category-bar').on('wheel', function(e) {
        if (Math.abs(e.originalEvent.deltaX) > Math.abs(e.originalEvent.deltaY)) {
            e.preventDefault();
            this.scrollLeft += e.originalEvent.deltaX;
        }
    });
    
    // Add CSS for touch feedback
    if (!$('#touch-feedback-css').length) {
        $('head').append(`
            <style id="touch-feedback-css">
                .touch-active {
                    transform: scale(0.98) !important;
                    transition: transform 0.1s ease !important;
                }
            </style>
        `);
    }
    
    // ============ PERFORMANCE OPTIMIZATIONS ============
    
    // Lazy loading for images
    function addLazyLoading() {
        $('img[data-src]').each(function() {
            const img = $(this);
            if (isElementInViewport(img[0])) {
                img.attr('src', img.data('src')).removeAttr('data-src');
            }
        });
    }
    
    function isElementInViewport(el) {
        const rect = el.getBoundingClientRect();
        return (
            rect.top >= 0 &&
            rect.left >= 0 &&
            rect.bottom <= (window.innerHeight || document.documentElement.clientHeight) &&
            rect.right <= (window.innerWidth || document.documentElement.clientWidth)
        );
    }
    
    // Debounced scroll handler
    let scrollTimer;
    $(window).on('scroll', function() {
        clearTimeout(scrollTimer);
        scrollTimer = setTimeout(function() {
            addLazyLoading();
        }, 100);
    });
    
    // Initial lazy loading check
    addLazyLoading();
    
    // ============ CONNECTION MONITORING ============
    
    function showConnectionStatus(status) {
        const statusEl = $('#connection-status');
        if (!statusEl.length) return;
        
        statusEl.removeClass('online offline hide')
                .addClass(status)
                .text(status === 'online' ? 'Back online' : 'You are offline');
        
        // Auto-hide after 3 seconds
        setTimeout(function() {
            statusEl.addClass('hide');
        }, 3000);
    }
    
    // Monitor connection status
    window.addEventListener('online', function() {
        showConnectionStatus('online');
        // Reload stories when back online
        if ($('.gtg-stories-carousel').length) {
            loadStories();
        }
    });
    
    window.addEventListener('offline', function() {
        showConnectionStatus('offline');
    });
    
    // ============ PWA INSTALL PROMPT ============
    
    let deferredPrompt;
    
    window.addEventListener('beforeinstallprompt', function(e) {
        console.log('PWA install prompt available');
        e.preventDefault();
        deferredPrompt = e;
        
        // Show custom install button
        showInstallPrompt();
    });
    
    function showInstallPrompt() {
        // Only show on mobile
        if (!('ontouchstart' in window)) return;
        
        // Don't show if already installed
        if (window.matchMedia('(display-mode: standalone)').matches) return;
        
        // Create install prompt
        const installPrompt = $(`
            <div class="gtg-install-prompt" id="install-prompt">
                <div class="install-content">
                    <div class="install-icon">📱</div>
                    <div class="install-text">
                        <strong>Install GrabToGo</strong>
                        <p>Get the full app experience</p>
                    </div>
                    <button class="install-btn" id="install-btn">Install</button>
                    <button class="install-close" id="install-close">&times;</button>
                </div>
            </div>
        `);
        
        $('body').append(installPrompt);
        
        // Add styles if not present
        if (!$('#install-prompt-css').length) {
            $('head').append(`
                <style id="install-prompt-css">
                    .gtg-install-prompt {
                        position: fixed;
                        bottom: 20px;
                        left: 20px;
                        right: 20px;
                        background: linear-gradient(45deg, #ff6f00, #ff8f00);
                        color: white;
                        border-radius: 16px;
                        box-shadow: 0 8px 20px rgba(255,111,0,0.3);
                        z-index: 10000;
                        animation: slideUp 0.3s ease;
                    }
                    
                    .install-content {
                        display: flex;
                        align-items: center;
                        padding: 16px;
                        gap: 12px;
                        position: relative;
                    }
                    
                    .install-icon {
                        font-size: 24px;
                    }
                    
                    .install-text {
                        flex: 1;
                    }
                    
                    .install-text strong {
                        display: block;
                        font-size: 16px;
                        margin-bottom: 2px;
                    }
                    
                    .install-text p {
                        margin: 0;
                        font-size: 13px;
                        opacity: 0.9;
                    }
                    
                    .install-btn {
                        background: rgba(255,255,255,0.2);
                        border: 1px solid rgba(255,255,255,0.3);
                        color: white;
                        padding: 8px 16px;
                        border-radius: 20px;
                        font-weight: 600;
                        font-size: 14px;
                    }
                    
                    .install-close {
                        position: absolute;
                        top: 8px;
                        right: 8px;
                        background: none;
                        border: none;
                        color: white;
                        font-size: 18px;
                        width: 30px;
                        height: 30px;
                        border-radius: 50%;
                        display: flex;
                        align-items: center;
                        justify-content: center;
                    }
                    
                    @keyframes slideUp {
                        from { transform: translateY(100%); opacity: 0; }
                        to { transform: translateY(0); opacity: 1; }
                    }
                </style>
            `);
        }
        
        // Handle install button click
        $('#install-btn').on('click', function() {
            if (deferredPrompt) {
                deferredPrompt.prompt();
                deferredPrompt.userChoice.then(function(choiceResult) {
                    console.log('Install prompt result:', choiceResult.outcome);
                    deferredPrompt = null;
                    $('#install-prompt').remove();
                });
            }
        });
        
        // Handle close button
        $('#install-close').on('click', function() {
            $('#install-prompt').remove();
            // Don't show again for this session
            sessionStorage.setItem('gtg_install_dismissed', 'true');
        });
        
        // Auto-hide after 10 seconds
        setTimeout(function() {
            $('#install-prompt').fadeOut(function() {
                $(this).remove();
            });
        }, 10000);
    }
    
    // Track app install
    window.addEventListener('appinstalled', function(evt) {
        console.log('GrabToGo app was installed');
        $('#install-prompt').remove();
        
        // Track with analytics if available
        if (typeof gtag !== 'undefined') {
            gtag('event', 'app_install', {
                'method': 'pwa'
            });
        }
    });
    
    // ============ PERFORMANCE MONITORING ============
    
    // Monitor page load performance
    window.addEventListener('load', function() {
        if ('performance' in window) {
            const perfData = performance.getEntriesByType('navigation')[0];
            const loadTime = perfData.loadEventEnd - perfData.loadEventStart;
            
            console.log('Page load time:', loadTime + 'ms');
            
            // Track slow loads
            if (loadTime > 3000) {
                console.warn('Slow page load detected');
            }
        }
    });
    
    // Service worker cache cleanup
    if ('serviceWorker' in navigator) {
        // Clean up cache weekly
        const lastCleanup = localStorage.getItem('gtg_last_cache_cleanup');
        const oneWeek = 7 * 24 * 60 * 60 * 1000;
        
        if (!lastCleanup || (Date.now() - parseInt(lastCleanup)) > oneWeek) {
            navigator.serviceWorker.ready.then(function(registration) {
                registration.active.postMessage({
                    type: 'CLEANUP_CACHE'
                });
                localStorage.setItem('gtg_last_cache_cleanup', Date.now().toString());
            });
        }
    }
    
});