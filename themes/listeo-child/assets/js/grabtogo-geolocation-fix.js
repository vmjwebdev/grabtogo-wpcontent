/**
 * GrabToGo Geolocation Fix for AppMySite WebView
 * Version: 2.0.0 - Enhanced with throttling and better queue management
 * Prevents glitching and looping in WebView environments
 */
(function($) {
    'use strict';
    
    // Global namespace for geolocation management
    window.GrabToGoGeo = window.GrabToGoGeo || {
        isRequesting: false,
        lastRequest: 0,
        requestQueue: [],
        lastPosition: null,
        lastPositionTime: 0,
        initialized: false,
        failedAttempts: 0
    };
    
    // Configuration
    const CONFIG = {
        REQUEST_COOLDOWN: 2000,  // Minimum time between requests (ms)
        TIMEOUT_WEBVIEW: 20000,  // Timeout for WebView (ms)
        TIMEOUT_BROWSER: 10000,  // Timeout for regular browser (ms)
        CACHE_DURATION: 300000,  // Cache position for 5 minutes
        MAX_RETRY: 3,           // Maximum retry attempts
        DEBUG: (typeof grabtogo_geo_config !== 'undefined' && grabtogo_geo_config.debug_mode) || false
    };
    
    // Logging helper
    function log(message, data = null) {
        if (CONFIG.DEBUG || window.location.hash === '#debug') {
            console.log('[GrabToGo Geo] ' + message, data || '');
        }
    }
    
    // Enhanced WebView detection
    function isInWebView() {
        const ua = navigator.userAgent || navigator.vendor || window.opera;
        const webViewPatterns = [
            'AppMySite',
            'wv',
            'WebView',
            'Android.*Version.*Chrome.*Mobile',
            'iPhone.*AppleWebKit.*CriOS',
            'Android.*; wv\\)',
        ];
        
        return webViewPatterns.some(pattern => new RegExp(pattern, 'i').test(ua)) ||
               (window.webkit && window.webkit.messageHandlers) ||
               (window.ReactNativeWebView) ||
               (window.flutter_inappwebview);
    }
    
    // Main geolocation handler with throttling
    function requestGeolocation(successCallback, errorCallback, forceNew = false) {
        const now = Date.now();
        
        log('Geolocation request initiated', {forceNew, isWebView: isInWebView()});
        
        // Check cooldown period
        if (!forceNew && (now - window.GrabToGoGeo.lastRequest < CONFIG.REQUEST_COOLDOWN)) {
            log('Request throttled, cooldown active');
            if (window.GrabToGoGeo.lastPosition) {
                log('Using cached position from cooldown');
                if (successCallback) successCallback(window.GrabToGoGeo.lastPosition);
            }
            return false;
        }
        
        // Check for cached position
        if (!forceNew && window.GrabToGoGeo.lastPosition && 
            (now - window.GrabToGoGeo.lastPositionTime < CONFIG.CACHE_DURATION)) {
            log('Using cached position (still fresh)');
            if (successCallback) successCallback(window.GrabToGoGeo.lastPosition);
            return true;
        }
        
        // Prevent multiple simultaneous requests
        if (window.GrabToGoGeo.isRequesting) {
            log('Request already in progress, queuing');
            window.GrabToGoGeo.requestQueue.push({
                success: successCallback,
                error: errorCallback
            });
            return false;
        }
        
        // Check if geolocation is available
        if (!navigator.geolocation) {
            log('Geolocation not supported');
            showNotification('Geolocation is not supported by your browser', 'error');
            if (errorCallback) errorCallback({code: 0, message: 'Geolocation not supported'});
            return false;
        }
        
        // Mark as requesting
        window.GrabToGoGeo.isRequesting = true;
        window.GrabToGoGeo.lastRequest = now;
        
        // Show loading state
        $('body').addClass('grabtogo-locating');
        showNotification('Getting your location...', 'info');
        
        const options = {
            enableHighAccuracy: false, // Start with low accuracy for speed
            timeout: isInWebView() ? CONFIG.TIMEOUT_WEBVIEW : CONFIG.TIMEOUT_BROWSER,
            maximumAge: 60000 // Accept positions up to 1 minute old
        };
        
        log('Requesting geolocation with options', options);
        
        const geoSuccess = function(position) {
            log('Geolocation success', {
                lat: position.coords.latitude,
                lng: position.coords.longitude,
                accuracy: position.coords.accuracy
            });
            
            // Reset failed attempts
            window.GrabToGoGeo.failedAttempts = 0;
            
            // Cache the position
            window.GrabToGoGeo.lastPosition = position;
            window.GrabToGoGeo.lastPositionTime = Date.now();
            window.GrabToGoGeo.isRequesting = false;
            
            // Remove loading state
            $('body').removeClass('grabtogo-locating');
            
            // Update Listeo fields
            updateListeoFields(position);
            
            // Execute callback
            if (successCallback) successCallback(position);
            
            // Process queued requests
            processQueue(position, null);
            
            showNotification('Location found!', 'success', 2000);
        };
        
        const geoError = function(error) {
            log('Geolocation error', error);
            
            window.GrabToGoGeo.failedAttempts++;
            window.GrabToGoGeo.isRequesting = false;
            $('body').removeClass('grabtogo-locating');
            
            // Retry with high accuracy if first attempt failed
            if (window.GrabToGoGeo.failedAttempts === 1 && error.code === error.TIMEOUT) {
                log('Retrying with high accuracy');
                window.GrabToGoGeo.isRequesting = false;
                options.enableHighAccuracy = true;
                options.timeout = CONFIG.TIMEOUT_WEBVIEW * 1.5;
                setTimeout(() => requestGeolocation(successCallback, errorCallback, true), 500);
                return;
            }
            
            handleGeolocationError(error);
            if (errorCallback) errorCallback(error);
            
            // Clear queue with error
            processQueue(null, error);
        };
        
        navigator.geolocation.getCurrentPosition(geoSuccess, geoError, options);
        
        return true;
    }
    
    // Process queued requests
    function processQueue(position, error) {
        const queue = [...window.GrabToGoGeo.requestQueue];
        window.GrabToGoGeo.requestQueue = [];
        
        log('Processing queue', {queueSize: queue.length});
        
        queue.forEach(function(request) {
            if (position && request.success) {
                request.success(position);
            } else if (error && request.error) {
                request.error(error);
            }
        });
    }
    
    // Update Listeo location fields
    function updateListeoFields(position) {
        const lat = position.coords.latitude;
        const lng = position.coords.longitude;
        
        log('Updating Listeo fields', {lat, lng});
        
        // Update hidden fields
        $('#_geolocation_lat, input[name="_geolocation_lat"]').val(lat);
        $('#_geolocation_long, input[name="_geolocation_long"]').val(lng);
        
        // Update map if exists
        if (typeof window.map !== 'undefined' && window.map) {
            const pos = new google.maps.LatLng(lat, lng);
            window.map.setCenter(pos);
            window.map.setZoom(12);
        }
        
        // Reverse geocode
        if (typeof google !== 'undefined' && google.maps && google.maps.Geocoder) {
            const geocoder = new google.maps.Geocoder();
            const pos = new google.maps.LatLng(lat, lng);
            
            geocoder.geocode({'latLng': pos}, function(results, status) {
                if (status === google.maps.GeocoderStatus.OK && results[0]) {
                    const address = results[0].formatted_address;
                    
                    log('Reverse geocode result', address);
                    
                    // Update all location fields
                    $('#location_search, #_address, input[name="location_search"]').val(address);
                    
                    // Store in data attributes
                    $('#location_search').data('lastlat', lat).data('lastlng', lng);
                    
                    // Trigger search update if on listings page
                    const $container = $('#listeo-listings-container');
                    if ($container.length) {
                        $container.triggerHandler('update_results', [1, false]);
                    }
                }
            });
        }
    }
    
    // Handle geolocation errors
    function handleGeolocationError(error) {
        let message = '';
        const isWebView = isInWebView();
        
        switch(error.code) {
            case error.PERMISSION_DENIED:
                message = isWebView ? 
                    'Location access denied. Please enable location in Settings > App Permissions > Location and restart the app.' :
                    'Location access denied. Please enable location services for this website.';
                break;
            case error.POSITION_UNAVAILABLE:
                message = 'Location unavailable. Please ensure GPS is enabled and try again.';
                break;
            case error.TIMEOUT:
                message = isWebView ?
                    'Location request timed out. Please ensure you have a good signal and try again.' :
                    'Location request timed out. Please try again.';
                break;
            default:
                message = 'Unable to get location. Please try again.';
        }
        
        showNotification(message, 'error', 8000);
    }
    
    // Show notification
    function showNotification(message, type = 'info', duration = 5000) {
        // Remove existing notifications
        $('.grabtogo-geo-notification').remove();
        
        const notification = $('<div class="grabtogo-geo-notification ' + type + '">' +
            '<span>' + message + '</span>' +
            '<button class="close">&times;</button>' +
            '</div>');
        
        $('body').append(notification);
        
        // Force reflow
        notification[0].offsetHeight;
        
        notification.addClass('show');
        
        // Auto-hide after duration
        if (duration > 0) {
            setTimeout(function() {
                notification.removeClass('show');
                setTimeout(function() {
                    notification.remove();
                }, 300);
            }, duration);
        }
        
        // Close button
        notification.find('.close').on('click', function() {
            notification.removeClass('show');
            setTimeout(function() {
                notification.remove();
            }, 300);
        });
    }
    
    // Initialize
    $(document).ready(function() {
        if (window.GrabToGoGeo.initialized) {
            log('Already initialized, skipping');
            return;
        }
        
        window.GrabToGoGeo.initialized = true;
        log('Initializing GrabToGo Geo Fix v2.0', {
            isWebView: isInWebView(),
            userAgent: navigator.userAgent
        });
        
        // Add WebView class to body
        if (isInWebView()) {
            $('body').addClass('grabtogo-webview');
            
            // Disable Listeo auto-geolocation
            if (typeof listeo_core !== 'undefined') {
                listeo_core.maps_autolocate = false;
            }
        }
        
        // Wait for other scripts to load
        setTimeout(function() {
            // Remove ALL existing handlers
            const selectors = '.geoLocation, .main-search-input-item.location a, .location:not(.add-listing-section) a, .form-field-_address-container a, #geoLocation';
            
            // Unbind everything
            $(selectors).off();
            $(document).off('click.geolocation touchstart.geolocation');
            
            // Add our single unified handler
            $(document).on('click.grabtogo touchstart.grabtogo', selectors, function(e) {
                e.preventDefault();
                e.stopPropagation();
                e.stopImmediatePropagation();
                
                log('Location button clicked', this.className);
                
                requestGeolocation(
                    function(position) {
                        log('Location obtained successfully');
                    },
                    function(error) {
                        log('Location request failed', error);
                    }
                );
                
                return false;
            });
            
            // Override any geolocate functions
            if (typeof window.geolocate !== 'undefined') {
                const originalGeolocate = window.geolocate;
                window.geolocate = function() {
                    log('Intercepted geolocate() call');
                    requestGeolocation();
                };
            }
            
356:        }, 500);
357:        
358:    });
359:    
360:    // ============ EXPOSE GLOBAL GEOLOCATION FUNCTION ============
361:    window.requestGeolocation = requestGeolocation;
362:    
363: })(jQuery);
364: 
365: // Add CSS
366: (function() {
    const styles = `<style>
    body.grabtogo-locating::after {
        content: 'Getting location...';
        position: fixed;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
        background: rgba(0,0,0,0.8);
        color: white;
        padding: 20px 40px;
        border-radius: 8px;
        z-index: 99999;
        font-size: 16px;
    }
    
    .grabtogo-geo-notification {
        position: fixed;
        top: 80px;
        right: -400px;
        max-width: 350px;
        padding: 15px 20px;
        background: #fff;
        border-radius: 8px;
        box-shadow: 0 4px 12px rgba(0,0,0,0.15);
        z-index: 99999;
        transition: right 0.3s ease;
        display: flex;
        align-items: center;
        gap: 10px;
    }
    
    .grabtogo-geo-notification.show {
        right: 20px;
    }
    
    .grabtogo-geo-notification.success {
        border-left: 4px solid #28a745;
        background: #f0fff4;
    }
    
    .grabtogo-geo-notification.error {
        border-left: 4px solid #dc3545;
        background: #fff5f5;
    }
    
    .grabtogo-geo-notification.info {
        border-left: 4px solid #007bff;
        background: #f0f8ff;
    }
    
    .grabtogo-geo-notification .close {
        background: none;
        border: none;
        font-size: 20px;
        cursor: pointer;
        padding: 0;
        margin-left: auto;
        color: #666;
    }
    
    .grabtogo-webview .geoLocation,
    .grabtogo-webview .location a,
    .geoLocation,
    .location a {
        -webkit-tap-highlight-color: transparent !important;
        touch-action: manipulation !important;
    }
    
    @media (max-width: 768px) {
        .grabtogo-geo-notification {
            left: 10px;
            right: 10px !important;
            max-width: calc(100% - 20px);
        }
    }
    </style>`;
    
    if (!document.getElementById('grabtogo-geo-styles')) {
        const styleEl = document.createElement('div');
        styleEl.id = 'grabtogo-geo-styles';
        styleEl.innerHTML = styles;
        document.head.appendChild(styleEl);
    }
})();

// ============ EXPOSE GLOBAL GEOLOCATION FUNCTION ============
window.requestGeolocation = requestGeolocation;