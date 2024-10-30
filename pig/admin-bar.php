<?php

class PIG_Bar {
	function __construct( ) {
		// Button stuff
		add_action( 'admin_bar_menu', array( $this, 'admin_bar_button' ), 100 );
		add_action( 'wp_after_admin_bar_render', array( $this, 'admin_bar_link' ) );

		// The panel
		add_action( 'wp_after_admin_bar_render', array( $this, 'pig_panel' ) );
	}

	function admin_bar_button() {
		global $wp_admin_bar;
		$title = apply_filters( 'pig_admin_bar_title', 'Play PIG, the Personal Information Game' );
		$wp_admin_bar->add_menu( array(
			'id'    => 'pig',
			'title' => '<span class="ab-icon" title="' . esc_attr( $title ) . '"></span>',
		) );
	}

	function admin_bar_link() {
		$toolbar_icon_url = apply_filters( 'pig_admin_bar_icon_url', plugins_url( 'pig.png', __FILE__ ) );
		$dancing_icon_url = apply_filters( 'pig_dancing_icon_url', plugins_url( 'pig.png', __FILE__ ) );
		$results_icon_url = apply_filters( 'pig_dancing_icon_url', plugins_url( 'pig-results.png', __FILE__ ) );
		?><script type="text/javascript">
		(function($){
		var pig = {
			initialized: false,
			load: function( node ) {
				var query = { action: 'pig' };

				if ( node ) {
					node = $(node);
					query.question    = node.data('question');
					query.answer      = node.data('answer');
					query.user_id     = node.data('user_id');
					query._ajax_nonce = node.data('_ajax_nonce');
				}

				pig.content.load( ajaxurl, query, function() {
					if ( $( this ).find( '.reward' ).length ) {
						pig.fly();
					}
				} );
			},
			toggle: function() {
				pig[ pig.wrapper.hasClass('pig-active') ? 'hide' : 'show' ]();
			},
			show: function() {
				pig.button.addClass('hover');
				pig.wrapper.addClass('pig-active');
				if ( ! pig.initialized )
					pig.load();
				pig.initialized = true;
			},
			hide: function() {
				pig.button.removeClass('hover');
				pig.wrapper.removeClass('pig-active');
			},
			fly: function() {
				pig.button.css( 'background-url', '' );
				fly = new Image();
				fly.src = '<?php echo esc_url( $toolbar_icon_url ); ?>';
				$( fly ).css(
					{
						position: 'absolute',
						left: ( jQuery( 'body' ).width() / 2 - 15 ) + 'px', // Same position as the button, below
						zIndex: '9999',
						height: '27px',
						width: '27px'
					}
				).click(function() { $( this ).remove(); });
				$( 'body' ).append( fly );
				$( fly ).animate(
					{
						top: ( jQuery( window ).height() / 2 - 150 ) + 'px',
						left: ( jQuery( 'body' ).width() / 2 - 150 ) + 'px',
						width: '300px', // you win a giant pig
						height: '300px'
					},
					{
						duration: 2500, // milliseconds
						complete: function() {
							$( this ).addClass( 'pig-dance' );
							setTimeout(
								function() {
									$( fly ).fadeOut( function() { $( this ).remove(); } );
								},
								4000 // 4 seconds of good piggin'
							);
						}
					}
				);
			}
		};

		$( function() {
			pig.content = $( '#pig-panel-content' );
			pig.button  = $( '#wp-admin-bar-pig' );
			pig.wrapper = $( '#pig-wrapper' );
			pig.overlay = $( '#pig-overlay' );

			// Reposition the button
			pig.button.css( 'left', ( jQuery( 'body' ).width() / 2 - 15 ) + 'px' );

			// Make the button trigger the panel
			pig.button.click( function( e ) {
				pig.toggle();
				e.preventDefault();
			});

			pig.overlay.click( pig.hide );

			pig.wrapper.on( 'click', '.pig-answer', function( e ) {
				// Don't run away
				e.preventDefault();

				// Dance, piggy
				$(this).parent().prepend( '<div style="<?php echo esc_url( $dancing_icon_url ); ?>" class="pig-dance"></div>' );

				// Consult the Oracle
				pig.load( this );
			});
		});

		// Share pig with the world.
		window.pig = pig;
		}(jQuery));
		</script>
		<style type="text/css">
		#wpadminbar #wp-admin-bar-pig {
			position: absolute;
		}
		#wp-admin-bar-pig span,
		#wp-admin-bar-pig span:hover,
		#wp-admin-bar-pig span:focus,
		#wp-admin-bar-pig span img,
		#wp-admin-bar-pig span img:hover,
		#wp-admin-bar-pig span img:focus {
			background: url( '<?php echo esc_url( $toolbar_icon_url ); ?>' ) center center no-repeat;
			background-size: 27px;
			text-indent: -9999px;
		}
		#wp-admin-bar-pig .ab-icon,
		#wp-admin-bar-pig .ab-icon:hover,
		#wp-admin-bar-pig .ab-icon:focus {
			margin: 0px;
			padding: 0px;
			width: 28px;
			height: 28px;
		}
		#pig-wrapper {
			z-index: -2;
			position: relative;
			opacity: 0;

			-webkit-transition-property: opacity;
			-webkit-transition-duration: 200ms;
		}
		#pig-wrapper.pig-active {
			z-index: 2000;
			opacity: 1;
		}
		#pig-panel {
			/* Center the panel */
			position: fixed;
			top: 50%;
			left: 50%;
			-webkit-transform: translate( -50%, -50% );
			-moz-transform:    translate( -50%, -50% );
			-ms-transform:     translate( -50%, -50% );
			-o-transform:      translate( -50%, -50% );
			transform:         translate( -50%, -50% );
			width: 600px;
			max-height: 600px;
			overflow: scroll;
			margin: 0 auto;
			background: #eee;
			border: solid 4px #000;
			z-index: 3000;
		}

		#pig-overlay {
			position: fixed;
			top: 0;
			left: 0;
			bottom: 0;
			right: 0;
			background: rgba( 0, 0, 0, 0.8 );
			z-index: 2000;
		}
		#pig-panel-content {
			margin: 0 auto;
			padding: 1em;
		}
		#pig-answers {
			margin: 0 0 0 30px;
			padding: 0;
		}
		#pig-answers li {
			list-style-type: none;
			margin: 0;
		}
		#pig-question {
			margin-left: 150px;
		}
		#pig-panel-position {
			margin: 0 2em;
		}
		a.pig-answer {
			padding: .5em;
			display: block;
			-webkit-transform: translateZ( 0 );
		}
		a.pig-answer:hover {
			background: #ccc;
		}
		.pig-dance {
			background: url( '<?php echo esc_url( $dancing_icon_url ); ?>' ) center center no-repeat;
			background-size: 30px;
			position: absolute;
			left: 190px;
			width: 30px;
			height: 30px;
			-webkit-animation: pig-dance 0.5s infinite alternate;
		}
		@-webkit-keyframes pig-dance {
			from {
				-webkit-transform: rotate(-50deg);
			}
			to {
				-webkit-transform: rotate(50deg);
			}
		}
		.pig-correct,
		.pig-incorrect {
			padding: 15px 15px 15px 60px;
			margin-bottom: 1em;
			font-weight: bold;
		}
		.pig-correct {
			background: #BFD370 no-repeat;
			background-image: url( '<?php echo esc_url( $results_icon_url ); ?>' );
			background-position: 10px 8px;
		}
		.pig-incorrect {
			background: #DD6515 no-repeat;
			background-image: url( '<?php echo esc_url( $results_icon_url ); ?>' );
			background-position: 5px -75px;
		}
		.pig-incorrect a {
			color: #fff;
		}
		</style><?php
	}

	function pig_panel() {
		?><div id="pig-wrapper">
		<div id="pig-panel">
			<div id="pig-panel-content">
				Getting piggy...
			</div>
			</div>
			<div id="pig-overlay"></div>
		</div><?php
	}
}

new PIG_Bar();
