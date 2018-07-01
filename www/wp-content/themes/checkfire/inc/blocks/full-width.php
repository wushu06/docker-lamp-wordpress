<!-- full-width width block -->
<div class="block_full-width" >

	<div class="block_small_container">

		<div class="row">
			<div class="col-md-12">
                <h5 class="block_full-width_small-title"><?php echo theme( 'small_title' ); ?></h5>

				<div class="block_full-width_title ">
					<h1>
						<?php echo theme( 'title' ); ?>
					</h1>
                    <div class="title-separator"></div>
				</div>
				<div class="block_full-width_content">
					<?php echo theme( 'content' ); ?>
				</div>
                <?php if(theme( 'button' )) : ?>
                <div class="block_full-width_button">
                    <a class="white-button hvr-shutter-out-vertical" href="<?php echo theme( 'button_url' ); ?>">
                        <?php echo theme( 'button' ); ?>
                    </a>
                </div>
                <?php endif; ?>

			</div>
		</div>

	</div>
	<?php if(is_page('Contact us')): ?>
        <div class="block_full-width_map">
            <div id="map"></div>
        </div>
	<?php endif; ?>
</div>

<script src="https://www.google.com/maps/embed/v1/MODE?key=AIzaSyC-9om8EYM5Rv09R6tV5XX9XVzLEzoZLgU&parameters"></script>

<script type="text/javascript" src="https://maps.googleapis.com/maps/api/js"></script>
<script type="text/javascript">
    // When the window has finished loading create our google map below
    google.maps.event.addDomListener(window, 'load', init);

    function init() {
        // Basic options for a simple Google Map
        // For more options see: https://developers.google.com/maps/documentation/javascript/reference#MapOptions
        var mapOptions = {
            // How zoomed in you want the map to start at (always required)
            zoom: 11,

            // The latitude and longitude to center the map (always required)
            center: new google.maps.LatLng(51.5895459, -3.2257906), // New York

            // How you would like to style the map.
            // This is where you would paste any style found on Snazzy Maps.
            styles: [{"featureType":"administrative","elementType":"labels.text.fill","stylers":[{"color":"#444444"}]},{"featureType":"landscape","elementType":"all","stylers":[{"color":"#f2f2f2"}]},
                {"featureType":"poi","elementType":"all","stylers":[{"visibility":"off"}]},{"featureType":"road","elementType":"all","stylers":[{"saturation":-100},{"lightness":45}]},{"featureType":"road.highway",
                    "elementType":"all","stylers":[{"visibility":"simplified"}]},{"featureType":"road.highway","elementType":"geometry","stylers":[{"visibility":"simplified"},{"color":"#ff6a6a"},{"lightness":"0"}]},
                {"featureType":"road.highway","elementType":"geometry.fill","stylers":[{"color":"#ee3123"}]},{"featureType":"road.highway","elementType":"geometry.stroke","stylers":[{"color":"#ee3123"}]},{"featureType":"road.highway","elementType":"labels.text","stylers":[{"visibility":"on"}]},{"featureType":"road.highway","elementType":"labels.icon","stylers":[{"visibility":"on"}]},{"featureType":"road.arterial","elementType":"all","stylers":[{"visibility":"on"}]},{"featureType":"road.arterial","elementType":"geometry.fill","stylers":[{"color":"#ee3123"},{"lightness":"62"}]},{"featureType":"road.arterial","elementType":"labels.icon","stylers":[{"visibility":"off"}]},{"featureType":"road.local","elementType":"geometry.fill","stylers":[{"lightness":"75"}]},{"featureType":"transit","elementType":"all","stylers":[{"visibility":"off"}]},{"featureType":"transit.line","elementType":"all","stylers":[{"visibility":"on"}]},{"featureType":"transit.station.bus","elementType":"all","stylers":[{"visibility":"on"}]},{"featureType":"transit.station.rail","elementType":"all","stylers":[{"visibility":"on"}]},{"featureType":"transit.station.rail","elementType":"labels.icon","stylers":[{"weight":"0.01"},{"hue":"#ff0028"},{"lightness":"0"}]},{"featureType":"water","elementType":"all","stylers":[{"visibility":"on"},{"color":"#fff"},{"lightness":"25"},{"saturation":"-23"}]}]
        };

        // Get the HTML DOM element that will contain your map
        // We are using a div with id="map" seen below in the <body>
        var mapElement = document.getElementById('map');

        // Create the Google Map using our element and options defined above
        var map = new google.maps.Map(mapElement, mapOptions);

        // Let's also add a marker while we're at it
        var marker = new google.maps.Marker({
            position: map.getCenter(),
            icon: '<?php echo get_template_directory_uri() ?>/assets/images/marker.png',
            map: map
        });
    }
</script>