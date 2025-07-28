/* ----------------- Start Document ----------------- */
(function($){
"use strict";

$(document).ready(function(){

  var infoBox_ratingType = 'star-rating';
  var url;

  function getMarkers() {
        var arrMarkers = [];
        $('.listing-geo-data').each(function(index) {
          var point_address;
          if( $( this ).data('friendly-address') ){
            point_address = $( this ).data('friendly-address');
          } else {
            point_address = $( this ).data('address');
          }
          
          if( $( this ).is('a') ){
            url = $(this).find('a').attr('href');
          } else {
            url = $(this).find('a').attr('href');
          }

          if( $( this ).data('longitude') ) {
            // badges can be in listing-list-small-badges-container, or listing-small-list-badges-container or listing-small-badges-container
            // add a check for each of them and use first on that is found
            var badges;
            if( $( this ).find('.listing-list-small-badges-container').length > 0 ) {
              badges = $( this ).find('.listing-list-small-badges-container').html();
            } else if( $( this ).find('.listing-small-list-badges-container').length > 0 ) {
               badges = $( this ).find('.listing-small-list-badges-container').html();
            } else {
              badges = $( this ).find('.listing-small-badges-container').html();
            }

            arrMarkers.push([
              locationData(
                $(this).find("a").attr("href")
                  ? $(this).find("a").attr("href")
                  : $(this).attr("href"),
                $(this).data("image"),
                $(this).data("title"),
                $(this).data("listing-type"),
                $(this).data("classifieds-price"),
                point_address,
                $(this).data("rating"),
                $(this).data("reviews"),
                badges
              ),
              $(this).data("longitude"),
              $(this).data("latitude"),
              1,
              $(this).data("icon"),
            ]);
          }
        });
        
        return arrMarkers;
    }

    function starsOutput(firstStar, secondStar, thirdStar, fourthStar, fifthStar) {
      return(''+
        '<span class="'+firstStar+'"></span>'+
        '<span class="'+secondStar+'"></span>'+
        '<span class="'+thirdStar+'"></span>'+
        '<span class="'+fourthStar+'"></span>'+
        '<span class="'+fifthStar+'"></span>');
    }

  function locationData(locationURL,locationImg,locationTitle,locationType, locationPrice, locationAddress, locationRating, locationRatingCounter, badges) {
         
      var output;
      var output_top;
      var output_bottom;
      output_top= ''+
            '<a href="'+ locationURL +'" class="leaflet-listing-img-container">'+
            '<div class="listing-small-badges-container">' + badges + '</div>' +
               '<img src="'+locationImg+'" alt="">'+
               '<div class="leaflet-listing-item-content">'+
                  '<h3>'+locationTitle+'</h3>'+
                  '<span>'+locationAddress+'</span>'+
               '</div>'+
               

            '</a>'+

            '<div class="leaflet-listing-content">'+
               '<div class="listing-title">';
               if(locationType == 'classifieds') {
                      output_bottom = '<div class="classifieds-price-infobox">'+locationPrice+'</div>'+
                         '</div>'+
                      '</div>';
               } else {
                    if(locationRating>0){

                // Rating Stars Output
                
                    var fiveStars = starsOutput('star','star','star','star','star');

                    var fourHalfStars = starsOutput('star','star','star','star','star half');
                    var fourStars = starsOutput('star','star','star','star','star empty');

                    var threeHalfStars = starsOutput('star','star','star','star half','star empty');
                    var threeStars = starsOutput('star','star','star','star empty','star empty');

                    var twoHalfStars = starsOutput('star','star','star half','star empty','star empty');
                    var twoStars = starsOutput('star','star','star empty','star empty','star empty');

                    var oneHalfStar = starsOutput('star','star half','star empty','star empty','star empty');
                    var oneStar = starsOutput('star','star empty','star empty','star empty','star empty');

                // Rules
                    var stars;
                    if (locationRating >= 4.75) {
                        stars = fiveStars;
                    } else if (locationRating >= 4.25) {
                        stars = fourHalfStars;
                    } else if (locationRating >= 3.75) {
                        stars = fourStars;
                    } else if (locationRating >= 3.25) {
                        stars = threeHalfStars;
                    } else if (locationRating >= 2.75) {
                        stars = threeStars;
                    } else if (locationRating >= 2.25) {
                        stars = twoHalfStars;
                    } else if (locationRating >= 1.75) {
                        stars = twoStars;
                    } else if (locationRating >= 1.25) {
                        stars = oneHalfStar;
                    } else if (locationRating < 1.25) {
                        stars = oneStar;
                    }
              

                      output_bottom = '<div class="'+infoBox_ratingType+'" data-rating="'+locationRating+'"><div class="rating-counter">('+locationRatingCounter+' '+listeo_core.maps_reviews_text+')</div>'+stars+'</div>'+
                         '</div>'+
                      '</div>';
                  } else {
                    output_bottom = '<div class="'+infoBox_ratingType+'"><span class="not-rated">'+listeo_core.maps_noreviews_text+'</span></div>'+
                         '</div>'+
                      '</div>';
                  }
               }
            
        output = output_top+output_bottom;
       

        return output;
    }

     window.L_DISABLE_3D = true 
        
       
      // console.log(locations);
      var group;
      var marker;
      var locations;
      var markerArray = [];
      

      var latlngStr = listeo_core.centerPoint.split(",",2);
      var lat = parseFloat(latlngStr[0]);
      var lng = parseFloat(latlngStr[1]);
      var mapZoomAttr = $('div#map').attr('data-map-zoom');
      var mapScrollAttr = $('div#map').attr('data-map-scroll');
     
      var zoomLevel = parseInt(mapZoomAttr);

      var markers;
      if ( $('div#map').hasClass('split-map') && !("ontouchstart" in document.documentElement)) {
        var mapOptions = {
          center: [lat,lng],
          zoom: zoomLevel,
          zoomControl: false,
          gestureHandling: false,
          tap: false
       }
      } else {
        var mapOptions = {
          center: [lat,lng],
          zoom: zoomLevel,
          zoomControl: false,
          gestureHandling: true,
          tap: false
       }
      }
     

      var _map = document.querySelectorAll("div#map");
      // if  querySelectorAll has found an element
      

      
     if (_map.length > 0) {
       window.map = L.map("map", mapOptions);

       switch (listeo_core.map_provider) {
         case "osm":
           L.tileLayer("https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png", {
             attribution:
               '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors',
           }).addTo(map);
           break;

         case "google":
           var roads = L.gridLayer
             .googleMutant({
               type: "roadmap", // valid values are 'roadmap', 'satellite', 'terrain' and 'hybrid'
               maxZoom: 18,
             })
             .addTo(map);

           break;

         case "mapbox":
           var accessToken = listeo_core.mapbox_access_token;
           var mapbox_style_url = listeo_core.mapbox_style_url;

           if (listeo_core.mapbox_retina) {
             L.tileLayer(mapbox_style_url + accessToken, {
               attribution:
                 " &copy;  <a href='https://www.mapbox.com/about/maps/'>Mapbox</a> &copy;  <a href='http://www.openstreetmap.org/copyright'>OpenStreetMap</a> <strong><a href='https://www.mapbox.com/map-feedback/' target='_blank'>Improve this map</a></strong>",
               maxZoom: 18,
               zoomOffset: -1,
               //detectRetina: true,
               tileSize: 512,
             }).addTo(map);
           } else {
             L.tileLayer(mapbox_style_url + accessToken, {
               attribution:
                 " &copy;  <a href='https://www.mapbox.com/about/maps/'>Mapbox</a> &copy;  <a href='http://www.openstreetmap.org/copyright'>OpenStreetMap</a> <strong><a href='https://www.mapbox.com/map-feedback/' target='_blank'>Improve this map</a></strong>",
               maxZoom: 18,
               //detectRetina: true,
               id: "mapbox.streets",
             }).addTo(map);
           }

           break;

         case "bing":
           var options = {
             bingMapsKey: listeo_core.bing_maps_key,
             imagerySet: "RoadOnDemand",
           };

           L.tileLayer.bing(options).addTo(map);
           break;

         case "thunderforest":
           var tileUrl =
               "https://tile.thunderforest.com/cycle/{z}/{x}/{y}{r}.png?apikey=" +
               listeo_core.thunderforest_api_key,
             layer = new L.TileLayer(tileUrl, { maxZoom: 18 });
           map.addLayer(layer);
           break;

         case "here":
           L.tileLayer
             .here({
               appId: listeo_core.here_app_id,
               appCode: listeo_core.here_app_code,
             })
             .addTo(map);
           break;
       }

       if ($("div#map").parent().parent().hasClass("fs-inner-container")) {
         var zoomOptions = {
           zoomInText: '<i class="fa fa-plus" aria-hidden="true"></i>',
           zoomOutText: '<i class="fa fa-minus" aria-hidden="true"></i>',
         };
         // Creating zoom control
         var zoom = L.control.zoom(zoomOptions);
         zoom.addTo(map);
       } else {
         map.scrollWheelZoom.disable();

         var zoomOptions = {
           zoomInText: '<i class="fa fa-plus" aria-hidden="true"></i>',
           zoomOutText: '<i class="fa fa-minus" aria-hidden="true"></i>',
         };
         // Creating zoom control
         var zoom = L.control.zoom(zoomOptions);
         zoom.addTo(map);
       }

       // Modify where the map is initialized (around line 472)
       if (window.map) {
         // Add flag to track if map was moved by user
         var userMovedMap = false;
         var initialLoadComplete = false;
         var ignoreMapEvents = false;

         // Set initial load complete after a short delay
         setTimeout(function() {
           initialLoadComplete = true;
           console.log('Map initial load complete');
         }, 5000);

         // Add movestart event to track user interaction
         window.map.on("movestart", function(e) {
           console.log('Map movestart', {
             initialLoadComplete: initialLoadComplete,
             hasOriginalEvent: !!e.originalEvent,
             type: e.type,
             target: e.target
           });
           // Only set userMovedMap if initial load is complete and event is triggered by user action
           if(initialLoadComplete && !!e.originalEvent) {
             userMovedMap = true;
             console.log('User moved map set to true');
           }
         });

         // NEW: Add event listener for dragstart to detect user initiated map movement
         window.map.on('dragstart', function(e) {
           if(ignoreMapEvents) return;
           console.log('Map dragstart detected, user moved map.');
           userMovedMap = true;
         });

         // NEW: Add event listener for zoomstart to detect user initiated zooming
         window.map.on('zoomstart', function(e) {
           if(ignoreMapEvents) return;
           console.log('Map zoomstart detected, user moved map.');
           userMovedMap = true;
         });

         // Add moveend event with debounce
         window.map.on(
           "moveend",
           debounce(function () {
             console.log('Map moveend', {
               initialLoadComplete: initialLoadComplete,
               userMovedMap: userMovedMap,
               mapBoundsSearch: listeo_core.map_bounds_search
             });
             if (!initialLoadComplete) {
               console.log('Skipping - initial load not complete');
               return;
             }
             if (userMovedMap && listeo_core.map_bounds_search == "on") {
               console.log('Processing map bounds search');
               var bounds = map.getBounds();
               var ne = bounds.getNorthEast();
               var sw = bounds.getSouthWest();

               // Add hidden inputs with bounds to the search form
               var searchForm = $("#listeo_core-search-form");
               searchForm
                 .find('.map-bounds, input[name="search_by_map_move"]')
                 .remove(); // remove old flags
               searchForm.append(
                 '<input type="hidden" class="map-bounds" name="map_bounds[ne_lat]" value="' +
                   ne.lat +
                   '">'
               );
               searchForm.append(
                 '<input type="hidden" class="map-bounds" name="map_bounds[ne_lng]" value="' +
                   ne.lng +
                   '">'
               );
               searchForm.append(
                 '<input type="hidden" class="map-bounds" name="map_bounds[sw_lat]" value="' +
                   sw.lat +
                   '">'
               );
               searchForm.append(
                 '<input type="hidden" class="map-bounds" name="map_bounds[sw_lng]" value="' +
                   sw.lng +
                   '">'
               );
               searchForm.append(
                 '<input type="hidden" name="search_by_map_move" value="true">'
               );

               // Trigger search update
               var target = $("div#listeo-listings-container");
               target.triggerHandler("update_results", [1, false]);
               userMovedMap = false;
             }
           }, 500)
         );
       }
     } 

      
      function debounce(func, wait) {
        var timeout;
        return function () {
          var context = this,
            args = arguments;
          clearTimeout(timeout);
          timeout = setTimeout(function () {
            func.apply(context, args);
          }, wait);
        };
      }
      
    function listingsMap(map){
        markers = L.markerClusterGroup({
            spiderfyOnMaxZoom: true,
            showCoverageOnHover: false,
            
          });
        locations = getMarkers();
       
        for (var i = 0; i < locations.length; i++) {

          var listeoIcon = L.divIcon({
              iconAnchor: [20, 51], // point of the icon which will correspond to marker's location
              popupAnchor: [0, -51],
              className: 'listeo-marker-icon',
              html:  '<div class="marker-container">'+
                               '<div class="marker-card">'+
                                  '<div class="front face">' + locations[i][4] + '</div>'+
                                  '<div class="back face">' + locations[i][4] + '</div>'+
                                  '<div class="marker-arrow"></div>'+
                               '</div>'+
                             '</div>'
              
            }
          );
            var popupOptions =
              {
              'maxWidth': '270',
              'className' : 'leaflet-infoBox'
              }
          
            marker = new L.marker([locations[i][1],locations[i][2]], {
                icon: listeoIcon,
                
              })
              .bindPopup(locations[i][0],popupOptions );
              //.addTo(map);
              marker.on('click', function(e){
                //alert('test');
               // L.DomUtil.addClass(marker._icon, 'clicked');
              });
              // map.on('popupopen', function (e) {
              //   L.DomUtil.addClass(e.popup._source._icon, 'clicked');
            

              // }).on('popupclose', function (e) {
              //   if(e.popup){
              //     L.DomUtil.removeClass(e.popup._source._icon, 'clicked');  
              //   }
                
              // });
              markers.addLayer(marker);

              markerArray.push(L.marker([locations[i][1], locations[i][2]]));
        }
        map.addLayer(markers);

    
        if(markerArray.length > 0 ){
          group = L.featureGroup(markerArray);
           if (
             listeo_core.maps_autofit == "on" &&
             !$('#listeo_core-search-form input[name="search_by_map_move"]')
               .length
           ) {
             map.fitBounds(group.getBounds());
           }
         
        }
    }

    $( '#listeo-listings-container' ).on( 'update_results_success', function (  ) {
        userMovedMap = false;
        ignoreMapEvents = true;
        setTimeout(function(){ ignoreMapEvents = false; }, 1500);
        if(window.map){
            map.closePopup();
            map.removeLayer(markers);
            markerArray = [];
            markers = false;
            map.closePopup();
            listingsMap(map);
        }
      
    });

    
    var map_id =  document.querySelectorAll('div#map');

    if (map_id.length > 0) {
      listingsMap(map);
    }

    if(listeo_core.map_provider != 'google') {
      $('.show-map-button').on('click', function(event) {
       event.preventDefault(); 
       $(".hide-map-on-mobile").toggleClass("map-active"); 
       var text_enabled = $(this).data('enabled');
       var text_disabled = $(this).data('disabled');
       if( $(".hide-map-on-mobile").hasClass('map-active')){
        $(this).text(text_disabled);
          
          // map.removeLayer(markers);
          // markerArray = [];
          // markers = false;
          // map.closePopup();
          // listingsMap(map);
       } else {
        $(this).text(text_enabled);
       }
      });
    }

   


    function submitPropertyMap(){
        
        window.L_DISABLE_3D = true 
        
        if(listeo_core.submitCenterPoint) {
          var latlngStr = listeo_core.submitCenterPoint.split(",",2);
          var lat = parseFloat(latlngStr[0]);
          var lng = parseFloat(latlngStr[1]);
          var curLocation = [lat, lng];
        } else {
          var curLocation = [-33.92, 151.25];
        }
        if($('#_geolocation_long').val() && $('#_geolocation_lat').val()) {
          curLocation = [parseFloat($('#_geolocation_lat').val()),parseFloat($( '#_geolocation_long' ).val())];
        }
        var listeoIcon = L.divIcon({
            iconAnchor: [20, 51], // point of the icon which will correspond to marker's location
            popupAnchor: [0, -51],
            className: 'listeo-marker-icon',
            html:  '<div class="marker-container no-marker-icon ">'+
                             '<div class="marker-card">'+
                                '<div class="front face"><i class="fa fa-map-pin"></i></div>'+
                                '<div class="back face"><i class="fa fa-map-pin"></i></div>'+
                                '<div class="marker-arrow"></div>'+
                             '</div>'+
                           '</div>'
            
          }
        );
        var mapOptions = {
            center: curLocation,
            zoom: 8,
            zoomControl: false,
            gestureHandling: true
         }
        window.submit_map = L.map('submit_map',mapOptions);


        var zoomOptions = {
           zoomInText: '<i class="fa fa-plus" aria-hidden="true"></i>',
           zoomOutText: '<i class="fa fa-minus" aria-hidden="true"></i>',
        };
        // Creating zoom control
        var zoom = L.control.zoom(zoomOptions);
        zoom.addTo(submit_map);
        
        
        switch(listeo_core.map_provider) {
          case 'osm':
              L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                  attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
              }).addTo(submit_map);
          break;
          
          case 'google':
            
              var roads = L.gridLayer.googleMutant({
                type: 'roadmap', // valid values are 'roadmap', 'satellite', 'terrain' and 'hybrid'
                maxZoom: 18
              }).addTo(submit_map);

          break;
          
          case 'mapbox':
      
              var accessToken = listeo_core.mapbox_access_token;
              var mapbox_style_url = listeo_core.mapbox_style_url;
              
              if(listeo_core.mapbox_retina){
                L.tileLayer(mapbox_style_url + accessToken, {
                    attribution: " &copy;  <a href='https://www.mapbox.com/about/maps/'>Mapbox</a> &copy;  <a href='http://www.openstreetmap.org/copyright'>OpenStreetMap</a> <strong><a href='https://www.mapbox.com/map-feedback/' target='_blank'>Improve this map</a></strong>",
                    maxZoom: 18,
                    zoomOffset: -1,
                    //detectRetina: true,
                    tileSize: 512,
                    
                }).addTo(submit_map);
              } else {

                L.tileLayer(mapbox_style_url + accessToken, {
                    attribution: " &copy;  <a href='https://www.mapbox.com/about/maps/'>Mapbox</a> &copy;  <a href='http://www.openstreetmap.org/copyright'>OpenStreetMap</a> <strong><a href='https://www.mapbox.com/map-feedback/' target='_blank'>Improve this map</a></strong>",
                    maxZoom: 18,
                    //detectRetina: true,
                    id: 'mapbox.streets',
                }).addTo(submit_map);
              }

             
          break;

          case 'bing':
              L.tileLayer.bing(listeo_core.bing_maps_key).addTo(submit_map)
          break;

          case 'thunderforest':
              var tileUrl = 'https://tile.thunderforest.com/cycle/{z}/{x}/{y}{r}.png?apikey='+listeo_core.thunderforest_api_key,
              layer = new L.TileLayer(tileUrl, {maxZoom: 18});
              submit_map.addLayer(layer);
          break;

          case 'here':
              L.tileLayer.here({appId: listeo_core.here_app_id, appCode: listeo_core.here_app_code}).addTo(submit_map);
          break;
        }

   
      

        submit_map.scrollWheelZoom.disable();

     
        
        window.submit_marker = new L.marker(curLocation, {
            draggable: 'true',
            icon: listeoIcon,
        });

        if(listeo_core.address_provider == 'osm'){
          if(listeo_core.country){
            var geocoder = new L.Control.Geocoder.Nominatim( {
                geocodingQueryParams: {countrycodes: listeo_core.country} //accept-language: 'en'
            });
          } else {
            var geocoder = new L.Control.Geocoder.Nominatim();  
          } 
          
          var output = [];
          $("#_address").attr('autocomplete', 'off').after('<div id="leaflet-geocode-cont"><ul></ul></div>');
          // $("#_address").on("focusout",function(e) {
          //     $("#leaflet-geocode-cont").removeClass('active');
          // });
          $("#_address").on("focusin",function(e) {
              $('.type-and-hit-enter').addClass('tip-visible');

              setTimeout(function(){
                     $('.type-and-hit-enter').removeClass('tip-visible');
                    //....and whatever else you need to do
               }, 3000);
          });
          $("#_address").on("keydown",function search(e) {

            $("#leaflet-geocode-cont").removeHighlight();
              if(e.keyCode == 13) {

                var query = $(this).val();
                // if query lenght is less than 3 characters, do not send request
                if(query.length < 3){ return false; }
                if(query){


                  geocoder.geocode(query, function(results) { 
                    
                    for (var i = 0; i < results.length; i++) {
                      
                      output.push('<li data-latitude="'+results[i].center.lat+'" data-longitude="'+results[i].center.lng+'" >'+results[i].name+'</li>');
                    }
                    output.push('<li class="powered-by-osm">Powered by <strong>OpenStreetMap</strong></li>');
                    $("#leaflet-geocode-cont").addClass('active');
                    $('#autocomplete-container').addClass("osm-dropdown-active");
                    $('#leaflet-geocode-cont ul').html(output);
                    var txt_to_hl = query.split(' ');
                    txt_to_hl.forEach(function (item) {
                      $('#leaflet-geocode-cont ul').highlight(item);
                    });
                    output = [];
                  });
                }
                if ($('#_address:focus').length){ return false; }
              }
          });

          $(".form-field-_address-container").on( "click", "#leaflet-geocode-cont ul li", function(e) {
              
              var newLatLng = new L.LatLng($(this).data('latitude'), $(this).data('longitude'));
              $("#_address").val($(this).text());
              submit_marker.setLatLng(newLatLng).update(); 
              submit_map.panTo(newLatLng);
              $("#_geolocation_lat").val($(this).data('latitude'));
              $("#_geolocation_long").val($(this).data('longitude'));
              $("#leaflet-geocode-cont").removeClass('active');
              $('#autocomplete-container').removeClass("osm-dropdown-active");
          });
        
          submit_marker.on('dragend', function(event) {
              var position = submit_marker.getLatLng();
              submit_marker.setLatLng(position, {
                draggable: 'true'
              }).bindPopup(position).update();

              geocoder.reverse(position, submit_map.options.crs.scale(submit_map.getZoom()), function(results) { 
                
                $("#_address").val(results[0].name);
              });
              $("#_geolocation_lat").val(position.lat);
              $("#_geolocation_long").val(position.lng).keyup();
          });
    }
    


        $("#_geolocation_lat").change(function() {    
            if( $("#_geolocation_long").val() ) {
              var position = [parseFloat($("#_geolocation_lat").val()), parseFloat($("#_geolocation_long").val())];
              submit_marker.setLatLng(position, {
                draggable: 'true'
              }).bindPopup(position).update();
              submit_map.panTo(position);  
            }
            
        });
        $("#_geolocation_long").change(function() {    
            if( $("#_geolocation_lat").val() ) {
              var position = [parseFloat($("#_geolocation_lat").val()), parseFloat($("#_geolocation_long").val())];
              submit_marker.setLatLng(position, {
                draggable: 'true'
              }).bindPopup(position).update();
              submit_map.panTo(position);  
            }
            
        });

        submit_map.addLayer(submit_marker);

        
    };

    var submit_map_cont =  document.getElementById('submit_map');
    if (typeof(submit_map_cont) != 'undefined' && submit_map_cont != null) {
        submitPropertyMap();
    }
      


    function singleListingMap() {

        var lng = parseFloat($( '#singleListingMap' ).data('longitude'));
        var lat =  parseFloat($( '#singleListingMap' ).data('latitude'));
        var singleMapIco =  "<i class='"+$('#singleListingMap').data('map-icon')+"'></i>";
        if($('#singleListingMap').data('map-icon-svg')){
          var   singleMapIco = $('#singleListingMap').data('map-icon-svg');
        }
        
        var map_single;
        var listeoIcon = L.divIcon({
            iconAnchor: [20, 51], // point of the icon which will correspond to marker's location
            popupAnchor: [0, -51],
            className: 'listeo-marker-icon',
            html:  '<div class="marker-container no-marker-icon ">'+
                             '<div class="marker-card">'+
                                '<div class="front face">' + singleMapIco + '</div>'+
                                '<div class="back face">' + singleMapIco + '</div>'+
                                '<div class="marker-arrow"></div>'+
                             '</div>'+
                           '</div>'
            
          }
        );
        var mapOptions = {
            center: [lat,lng],
            zoom: listeo_core.maps_single_zoom,
            zoomControl: false,
            gestureHandling: true
         }

        map_single = L.map('singleListingMap',mapOptions);
        var zoomOptions = {
           zoomInText: '<i class="fa fa-plus" aria-hidden="true"></i>',
           zoomOutText: '<i class="fa fa-minus" aria-hidden="true"></i>',
        };
        // Creating zoom control
        var zoom = L.control.zoom(zoomOptions);
        zoom.addTo(map_single);

        map_single.scrollWheelZoom.disable();
        if($('#singleListingMap-container').hasClass('circle-point')) {
           marker = new L.circleMarker([lat,lng], {
                radius:40
          }).addTo(map_single);
        } else {
          marker = new L.marker([lat,lng], {
                icon: listeoIcon,
          }).addTo(map_single);
  
        }
        
        switch(listeo_core.map_provider) {
          case 'osm':
              L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                  attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
              }).addTo(map_single);
            break;
         case 'google':
            
              var roads = L.gridLayer.googleMutant({
                type: 'roadmap', // valid values are 'roadmap', 'satellite', 'terrain' and 'hybrid'
                maxZoom: 18
              }).addTo(map_single);

            break;

       
          case 'mapbox':
              var accessToken = listeo_core.mapbox_access_token;
              var mapbox_style_url = listeo_core.mapbox_style_url;
              
              if(listeo_core.mapbox_retina){
                L.tileLayer(mapbox_style_url + accessToken, {
                    attribution: " &copy;  <a href='https://www.mapbox.com/about/maps/'>Mapbox</a> &copy;  <a href='http://www.openstreetmap.org/copyright'>OpenStreetMap</a> <strong><a href='https://www.mapbox.com/map-feedback/' target='_blank'>Improve this map</a></strong>",
                    maxZoom: 18,
                    zoomOffset: -1,
                    //detectRetina: true,
                    tileSize: 512,
                    
                }).addTo(map_single);
              } else {

                L.tileLayer(mapbox_style_url + accessToken, {
                    attribution: " &copy;  <a href='https://www.mapbox.com/about/maps/'>Mapbox</a> &copy;  <a href='http://www.openstreetmap.org/copyright'>OpenStreetMap</a> <strong><a href='https://www.mapbox.com/map-feedback/' target='_blank'>Improve this map</a></strong>",
                    maxZoom: 18,
                    //detectRetina: true,
                    id: 'mapbox.streets',
                }).addTo(map_single);
              }

                
            break;

          case 'bing':
              L.tileLayer.bing(listeo_core.bing_maps_key).addTo(map_single)
          break;

          case 'thunderforest':
              var tileUrl = 'https://tile.thunderforest.com/cycle/{z}/{x}/{y}{r}.png?apikey='+listeo_core.thunderforest_api_key,
              layer = new L.TileLayer(tileUrl, {maxZoom: 18});
              map_single.addLayer(layer);
          break;

          case 'here':
              L.tileLayer.here({appId: listeo_core.here_app_id, appCode: listeo_core.here_app_code}).addTo(map_single);
          break;
        }

         $(window).on('load resize', function () {
              setTimeout(function(){ map_single.invalidateSize()}, 100);
            });
    }



        // Single Listing Map Init
        var single_map_cont =  document.getElementById('singleListingMap');

        if (typeof(single_map_cont) != 'undefined' && single_map_cont != null) {
            
            singleListingMap();
           
     
        }
        
        
        
if(listeo_core.address_provider == 'osm'){
        if(listeo_core.country){
          var geocoder = new L.Control.Geocoder.Nominatim( {
              geocodingQueryParams: {countrycodes: listeo_core.country}
          });
        } else {
          var geocoder = new L.Control.Geocoder.Nominatim();  
        } 
        var output = [];
        $("#location_search").attr('autocomplete', 'off').after('<div id="leaflet-geocode-cont"><ul></ul></div>')
        var liSelected;
        var next;
        // OSM TIP
        $("#location_search")
        .on("mouseover",function() {
            if ( $(this).val().length < 10 ) {
                $('.type-and-hit-enter').addClass('tip-visible');
            } 
        }).on("mouseout",function(e) {
           setTimeout(function(){
                 $('.type-and-hit-enter').removeClass('tip-visible');
           }, 350);
        }).on("keyup",function(e) {

            if ( $(this).val().length < 10 ) {
                $('.type-and-hit-enter').addClass('tip-visible');
            } 
            if ( $(this).val().length > 10 ) {
                $('.type-and-hit-enter').removeClass('tip-visible tip-visible-focusin');
            }
            
            if(e.which === 40 || e.which === 38 ) {
              
              
            } else {
              $('#leaflet-geocode-cont ul li.selected').removeClass('selected');
            }
            // if(e.which !== 38 ) {
            //   console.log(e.which);
            //   //$('#leaflet-geocode-cont ul li.selected').removeClass('selected');
            // }

        }).on("keydown",function(e) {
              var li = $('#leaflet-geocode-cont ul li');
             if(e.which === 40){
              
                if(liSelected){
                    liSelected.removeClass('selected');
                    next = liSelected.next();
                    if(next.length > 0){
                        liSelected = next.addClass('selected');
                    }else{
                        liSelected = li.eq(0).addClass('selected');
                    }
                }else{
                    liSelected = li.eq(0).addClass('selected');
                }
            }else if(e.which === 38){
                if(liSelected){
                    liSelected.removeClass('selected');
                    next = liSelected.prev();
                    if(next.length > 0){
                        liSelected = next.addClass('selected');
                    }else{
                        liSelected = li.last().addClass('selected');
                    }
                }else{
                    liSelected = li.last().addClass('selected');
                }
            }
          
        });

        $("#location_search").on("focusin",function() {
            if ( $(this).val().length < 10 ) {
                $('.type-and-hit-enter').addClass('tip-visible-focusin');
            }
            if ( $(this).val().length > 10 ) {
                $('.type-and-hit-enter').removeClass('tip-visible-focusin');
            }
        }).on("focusout",function() {
            setTimeout(function(){
                $('.type-and-hit-enter').removeClass('tip-visible tip-visible-focusin');
            }, 350);
            if( $(this).val() == 0 ) {
              $('div#listeo-listings-container' ).triggerHandler( 'update_results', [ 1, false ] );
            }
        });
        
        $(".location .fa-map-marker").on("mouseover",function() {
            $('.type-and-hit-enter').removeClass('tip-visible-focusin tip-visible');
        })
        
        $('.type-and-click-btn').on("click",function search(e) {

             var query = $('#_address').val();
             //remove any empty space from start and end of the 'query' string

            query = query.replace(/^\s+|\s+$/gm,'');
             if (query.length < 3) {
               return false;
             }
              if(query){
                geocoder.geocode(query, function(results) { 
                  
                  for (var i = 0; i < results.length; i++) {
                    
                    output.push('<li data-latitude="'+results[i].center.lat+'" data-longitude="'+results[i].center.lng+'" >'+results[i].name+'</li>');
                  }
                  output.push('<li class="powered-by-osm">Powered by <strong>OpenStreetMap</strong></li>');
                  $("#leaflet-geocode-cont").addClass('active');
                  $('#autocomplete-container').addClass("osm-dropdown-active");
                  $('#leaflet-geocode-cont ul').html(output);
                  var txt_to_hl = query.split(' ');
                  txt_to_hl.forEach(function (item) {
                    $('#leaflet-geocode-cont ul').highlight(item);
                  });
                  output = [];
                });
              }
        });
        
        $("#location_search").on("keydown",function search(e) {

          
            if(e.keyCode == 13) {
                if($('#leaflet-geocode-cont ul li.selected').length>0){
                  $('#leaflet-geocode-cont ul li.selected').trigger('click').removeClass('selected');

                 return;
                 
                }

                var query = $(this).val();
                if (query.length < 3) {
                  return false;
                }
                if(query){
                  query = query.replace(/^\s+|\s+$/gm, "");
                geocoder.geocode(query, function(results) { 
                  
                  for (var i = 0; i < results.length; i++) {
                    
                    output.push('<li data-latitude="'+results[i].center.lat+'" data-longitude="'+results[i].center.lng+'" >'+results[i].name+'</li>');
                  }
                  output.push('<li class="powered-by-osm">Powered by <strong>OpenStreetMap</strong></li>');
                  $('#leaflet-geocode-cont ul').html(output);
                  var txt_to_hl = query.split(' ');
                  txt_to_hl.forEach(function (item) {
                    $('#leaflet-geocode-cont ul').highlight(item);
                  });
                  $('#autocomplete-container').addClass("osm-dropdown-active"); 
                  $("#leaflet-geocode-cont").addClass('active');
                  output = [];
                });
              }
            }
        });
         $("#listeo_core-search-form").on( "click", "#leaflet-geocode-cont ul li", function(e) {
            
            $("#location_search").val($(this).text());
            $("#leaflet-geocode-cont").removeClass('active');
            $('#autocomplete-container').removeClass("osm-dropdown-active");
            
            var newLatLng = new L.LatLng($(this).data('latitude'), $(this).data('longitude'));

            // check if map exists
            if(window.map){
            map.flyTo(newLatLng, 10);
            }
            var target   = $('div#listeo-listings-container' );
            target.triggerHandler( 'update_results', [ 1, false ] );
        });

        $('#listeo_core-search-form').on('submit', function(){
            if ($('#location_search:focus').length){ return false;}
        });

        if($("#location_search").val()) {
          var query = $("#location_search").val()
          query = query.replace(/^\s+|\s+$/gm, "");
          geocoder.geocode(query, function(results) { 
            
            if(map){
              map.flyTo(results[0].center, 10);  
            }
            
            
          });
        }

      

      
      var mouse_is_inside = false;

      $( "#location_search,#_address,#leaflet-geocode-cont" ).on( "mouseenter", function() {
          mouse_is_inside=true;
      });
      $( "#location_search,#_address,#leaflet-geocode-cont" ).on( "mouseleave", function() {
          mouse_is_inside=false;
      });

      $("body").mouseup(function(){
          if(! mouse_is_inside) $("#leaflet-geocode-cont").removeClass('active');
      });
}

      $(".geoLocation, #listeo_core-search-form .location a,.main-search-input-item.location a,.form-field-_address-container a").on("click", function (e) {
          e.preventDefault();
          
          geolocate();
      });

      function geolocate() {

          if (navigator.geolocation) {
              navigator.geolocation.getCurrentPosition(function (position) {
                  
                  var latitude = position.coords.latitude;
                  var longitude = position.coords.longitude;
                  var latlng = L.latLng(latitude, longitude);

                  if(listeo_core.address_provider == 'google'){
                      if(window.map){
                      map.flyTo([latitude,longitude],map.getZoom());
                      }
                      var pos = new google.maps.LatLng(position.coords.latitude, position.coords.longitude);
                      var geocoder = new google.maps.Geocoder();
                        $('#_geolocation_lat').val(latitude);
                        $('#_geolocation_long').val(longitude);
                      geocoder.geocode( { 'latLng': pos}, function(results, status) {
                        if (status == google.maps.GeocoderStatus.OK) {
                          if (results[1]) {
                            if($('#location_search').length){
                              if($('#location_search').val().length === 0 ) {
                                $('#location_search').val(results[1].formatted_address);
                              }
                            }
                             $("#_address").val(results[1].formatted_address);
                          }
                        
                    }
                  });
                    
                  } else {
                      if(listeo_core.address_provider == 'osm'){
                        if(listeo_core.country){
                          var geocoder = new L.Control.Geocoder.Nominatim( {
                              geocodingQueryParams: {countrycodes: listeo_core.country} //accept-language: 'en'
                          });
                        } else {
                          var geocoder = new L.Control.Geocoder.Nominatim();  
                        } 
                      }
                    if(window.map){
                      map.flyTo([latitude,longitude],10);
                      geocoder.reverse(latlng, map.options.crs.scale(map.getZoom()), function(results) { 
                        if($('#location_search').length){
                          if($('#location_search').val().length === 0 ) {
                            $("#location_search").val(results[0].name);
                          }  
                        }
                        
                        $("#_address").val(results[0].name);
                        $('#_geolocation_lat').val(latitude);
                        $('#_geolocation_long').val(longitude);
                        var newLatLng = new L.LatLng(latitude, longitude);
                        marker.setLatLng(newLatLng).update(); 
                        map.panTo(newLatLng);
                        var listing_results      = $('#listeo-listings-container');
                        listing_results.triggerHandler( 'update_results', [ 1, false ] );
                      });
                    } else {
                      geocoder.reverse(latlng, 73728, function (results) {
                        if ($("#location_search").length) {
                          if ($("#location_search").val().length === 0) {
                            $("#location_search").val(results[0].name);
                          }
                        }
                        $("#_address").val(results[0].name);
                        $("#_geolocation_lat").val(latitude);
                        $("#_geolocation_long").val(longitude);
                        var listing_results = $("#listeo-listings-container");
                        listing_results.triggerHandler("update_results", [
                          1,
                          false,
                        ]);
                      });

                    }
                 
                }
                 
              });
          }
      }
    
      if(listeo_core.maps_autolocate){
        
        $(".geoLocation, #listeo_core-search-form .location a,.main-search-input-item.location a").trigger('click')
      }
  });
})(this.jQuery);

jQuery.fn.highlight = function(pat) {
 function innerHighlight(node, pat) {
  var skip = 0;
  if (node.nodeType == 3) {
   var pos = node.data.toUpperCase().indexOf(pat);
   if (pos >= 0) {
    var spannode = document.createElement('span');
    spannode.className = 'highlight';
    var middlebit = node.splitText(pos);
    var endbit = middlebit.splitText(pat.length);
    var middleclone = middlebit.cloneNode(true);
    spannode.appendChild(middleclone);
    middlebit.parentNode.replaceChild(spannode, middlebit);
    skip = 1;
   }
  }
  else if (node.nodeType == 1 && node.childNodes && !/(script|style)/i.test(node.tagName)) {
   for (var i = 0; i < node.childNodes.length; ++i) {
    i += innerHighlight(node.childNodes[i], pat);
   }
  }
  return skip;
 }
 return this.each(function() {
  innerHighlight(this, pat.toUpperCase());
 });
};

jQuery.fn.removeHighlight = function() {
 function newNormalize(node) {
    for (var i = 0, children = node.childNodes, nodeCount = children.length; i < nodeCount; i++) {
        var child = children[i];
        if (child.nodeType == 1) {
            newNormalize(child);
            continue;
        }
        if (child.nodeType != 3) { continue; }
        var next = child.nextSibling;
        if (next == null || next.nodeType != 3) { continue; }
        var combined_text = child.nodeValue + next.nodeValue;
        new_node = node.ownerDocument.createTextNode(combined_text);
        node.insertBefore(new_node, child);
        node.removeChild(child);
        node.removeChild(next);
        i--;
        nodeCount--;
    }
 }

 return this.find("span.highlight").each(function() {
    var thisParent = this.parentNode;
    thisParent.replaceChild(this.firstChild, this);
    newNormalize(thisParent);
 }).end();
};