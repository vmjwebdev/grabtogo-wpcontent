/**
 * GrabToGo Geolocation Fix for Listeo + AppMySite
 * Version: 1.0.0
 * Prevents glitching and looping in WebView environments
 */
(function($) {
    'use strict';
    
    // Global namespace for geolocation management
    window.GrabToGoGeo = {
        isRequesting: false,
        lastPosition: null,
        requestQueue: [],
        initialized: false
    };
    
    // Detect WebView/AppMySite environment
    function isInWebView() {
        const ua = navigator.userAgent || navigator.vendor || window.opera;
        return /AppMySite|wv|WebView/i.test(ua) || 
               (window.webkit && window.webkit.messageHandlers) ||
               (window.ReactNativeWebView);
    }
    
    // Centralized geolocation handler
    function unifiedGeolocationHandler(callback, errorCallback) {
        // Prevent multiple simultaneous requests
        if (window.GrabToGoGeo.isRequesting) {
            console.log('[GrabToGo] Queuing geolocation request');
            window.GrabToGoGeo.requestQueue.push({callback, errorCallback});
            return false;
        }
        
        // Check if geolocation is available
        if (!navigator.geolocation) {
            console.error('[GrabToGo] Geolocation not supported');
            if (errorCallback) errorCallback('NOT_SUPPORTED');
            return false;
        }
        
        window.GrabToGoGeo.isRequesting = true;
        
        // Show loading state
        $('body').addClass('grabtogo-locating');
        
        const options = {
            enableHighAccuracy: true,
            timeout: isInWebView() ? 15000 : 10000, // Longer timeout for WebView
            maximumAge: 300000 // 5 minutes cache
        };
        
        navigator.geolocation.getCurrentPosition(
            function(position) {
                // Success
                window.GrabToGoGeo.isRequesting = false;
                window.GrabToGoGeo.lastPosition = position;
                $('body').removeClass('grabtogo-locating');
                
                // Execute callback
                if (callback) callback(position);
                
                // Process queued requests
                processGeolocationQueue(position);
            },
            function(error) {
                // Error
                window.GrabToGoGeo.isRequesting = false;
                $('body').removeClass('grabtogo-locating');
                
                handleGeolocationError(error);
                if (errorCallback) errorCallback(error);
                
                // Clear queue
                window.GrabToGoGeo.requestQueue = [];
            },
            options
        );
    }
    
    // Process queued geolocation requests
    function processGeolocationQueue(position) {
        if (window.GrabToGoGeo.requestQueue.length > 0) {
            const queue = window.GrabToGoGeo.requestQueue;
            window.GrabToGoGeo.requestQueue = [];
            
            queue.forEach(function(request) {
                if (request.callback) {
                    request.callback(position);
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
                    'Location access denied. Please enable location services in your device settings and restart the app.' :
                    'Location access denied. Please enable location services for this website.';
                break;
            case error.POSITION_UNAVAILABLE:
                message = 'Location information is currently unavailable. Please try again.';
                break;
            case error.TIMEOUT:
                message = 'Location request timed out. Please check your connection and try again.';
                break;
            default:
                message = 'Unable to retrieve your location. Please try again.';
        }
        
        // Show user-friendly notification
        showGeolocationNotification(message, 'error');
    }
    
    // Show notification
    function showGeolocationNotification(message, type = 'info') {
        // Remove existing notifications
        $('.grabtogo-geo-notification').remove();
        
        const notification = $('<div class="grabtogo-geo-notification ' + type + '">' +
            '<span>' + message + '</span>' +
            '<button class="close">&times;</button>' +
            '</div>');
        
        $('body').append(notification);
        
        setTimeout(function() {
            notification.addClass('show');
        }, 100);
        
        // Auto-hide after 5 seconds
        setTimeout(function() {
            notification.removeClass('show');
            setTimeout(function() {
                notification.remove();
            }, 300);
        }, 5000);
        
        // Close button
        notification.find('.close').on('click', function() {
            notification.removeClass('show');
            setTimeout(function() {
                notification.remove();
            }, 300);
        });
    }
    
    // Initialize on document ready
    $(document).ready(function() {
        if (window.GrabToGoGeo.initialized) return;
        window.GrabToGoGeo.initialized = true;
        
        // Remove ALL existing geolocation event handlers first
        $('.geoLocation, .main-search-input-item.location a, .location:not(.add-listing-section) a, .form-field-_address-container a, #kerala_near_me')
            .off('click.geolocation touchstart.geolocation');
        
        // Add single unified handler for Listeo geolocation buttons
        $(document).on('click.grabtogo', '.geoLocation, .main-search-input-item.location a, .location:not(.add-listing-section) a, .form-field-_address-container a', function(e) {
            e.preventDefault();
            e.stopPropagation();
            
            unifiedGeolocationHandler(function(position) {
                updateListeoLocation(position);
            });
            
            return false;
        });
        
        // Special handler for Kerala Near Me button (from your custom.js)
        $(document).on('click.grabtogo', '#kerala_near_me', function(e) {
            e.preventDefault();
            e.stopPropagation();
            
            const $btn = $(this);
            $btn.html('<i class="fa fa-spinner fa-spin"></i> Getting location...').prop('disabled', true);
            
            unifiedGeolocationHandler(
                function(position) {
                    // Update Kerala filters
                    if (window.currentFilters) {
                        window.currentFilters.user_lat = position.coords.latitude;
                        window.currentFilters.user_lng = position.coords.longitude;
                        window.currentFilters.page = 1;
                    }
                    
                    $btn.html('<i class="fa fa-location-arrow"></i> Near Me').prop('disabled', false);
                    
                    // Trigger Kerala listings load
                    if (typeof loadListings === 'function') {
                        loadListings(true);
                    }
                },
                function(error) {
                    $btn.html('<i class="fa fa-location-arrow"></i> Near Me').prop('disabled', false);
                }
            );
            
            return false;
        });
        
        // Disable auto-geolocation in WebView
        if (isInWebView() && typeof listeo_core !== 'undefined') {
            listeo_core.maps_autolocate = false;
        }
    });
    
    // Update Listeo location fields
    function updateListeoLocation(position) {
        const lat = position.coords.latitude;
        const lng = position.coords.longitude;
        const pos = new google.maps.LatLng(lat, lng);
        
        // Update map if exists
        if (typeof map !== 'undefined' && map) {
            map.setCenter(pos);
            map.setZoom(12);
        }
        
        // Update hidden fields
        $('#_geolocation_lat').val(lat);
        $('#_geolocation_long').val(lng);
        
        // Reverse geocode
        if (typeof google !== 'undefined' && google.maps) {
            const geocoder = new google.maps.Geocoder();
            geocoder.geocode({'latLng': pos}, function(results, status) {
                if (status === google.maps.GeocoderStatus.OK && results[1]) {
                    const address = results[1].formatted_address;
                    
                    // Update all location fields
                    $('#location_search, #_address, #keyword_search').val(address);
                    
                    // Store in data attributes for later use
                    $('#location_search').data('lastlat', lat).data('lastlng', lng);
                    
                    // Trigger search update
                    const $target = $('div#listeo-listings-container');
                    if ($target.length) {
                        $target.triggerHandler('update_results', [1, false]);
                    }
                    
                    // Show success notification
                    showGeolocationNotification('Location updated successfully!', 'success');
                }
            });
        }
    }
    
    // WebView-specific fixes
    if (isInWebView()) {
        // Add WebView class to body
        $('body').addClass('grabtogo-webview');
        
        // Override Listeo's mainMap function to prevent auto-geolocation
        const originalMainMap = window.mainMap;
        window.mainMap = function() {
            if (originalMainMap) {
                // Temporarily disable auto-geolocation
                const autolocate = listeo_core.maps_autolocate;
                listeo_core.maps_autolocate = false;
                
                originalMainMap.apply(this, arguments);
                
                // Restore setting after map loads
                setTimeout(function() {
                    listeo_core.maps_autolocate = autolocate;
                }, 1000);
            }
        };
    }
    
})(jQuery);

// Add required CSS
(function() {
    const styles = `
    <style>
    /* Loading state */
    body.grabtogo-locating {
        position: relative;
    }
    
    body.grabtogo-locating::after {
        content: '';
        position: fixed;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: rgba(0,0,0,0.3);
        z-index: 99998;
        cursor: wait;
    }
    
    /* Notification styles */
    .grabtogo-geo-notification {
        position: fixed;
        top: 20px;
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
    }
    
    .grabtogo-geo-notification.error {
        border-left: 4px solid #dc3545;
    }
    
    .grabtogo-geo-notification .close {
        background: none;
        border: none;
        font-size: 20px;
        cursor: pointer;
        padding: 0;
        margin-left: auto;
        color: #999;
    }
    
    /* WebView specific styles */
    .grabtogo-webview .geoLocation,
    .grabtogo-webview .location a {
        -webkit-tap-highlight-color: transparent;
        touch-action: manipulation;
    }
    
    /* Mobile responsive */
    @media (max-width: 768px) {
        .grabtogo-geo-notification {
            left: 10px;
            right: 10px;
            max-width: none;
        }
        
        .grabtogo-geo-notification.show {
            right: 10px;
        }
    }
    </style>`;
    
    jQuery('head').append(styles);
})();