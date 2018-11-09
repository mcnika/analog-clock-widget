<?php
/*
Plugin Name: Analog Clock Widget
Version: 1.3
Description: Display a SVG Analog Clock on your sidebar.
Author: mcnika
Author URI: http://www.mcnika.com
Plugin URI: http://plugins.mcnika.com/analog-clock-widget/
Text Domain: analog-clock-widget
*/

define( 'CURRENCYR_VERSION', '1.3' );
define( 'CURRENCYR_PLUGIN_URL', plugin_dir_url( __FILE__ ) );

class Analog_Clock_Widget extends WP_Widget {       
    
  protected static $did_script = false;
  
  // Widget constructor. 
  public function __construct() {
    parent::__construct( 'Analog_Clock_Widget', __('Analog Clock Widget', 'analog-clock-widget'), array( 'description' => __('Adds Analog Clock to sidebar', 'analog-clock-widget') ) );
            
    $this->clock_aligns = array(
      'none' => __('none', 'analog-clock-widge'),
      'left' => __('left', 'analog-clock-widge'),
      'center' => __('center', 'analog-clock-widge'),
      'right' => __('right', 'analog-clock-widge')                
    );    
   
    add_action('wp_enqueue_scripts', array($this, 'widgets_scripts'));      
    add_action( 'admin_enqueue_scripts', array($this, 'admin_scripts') );    
  }
    
  // Enqueue scripts.  
  function widgets_scripts(){
    if(!self::$did_script && is_active_widget(false, false, $this->id_base, true)){
      wp_register_script('raphael-min', CURRENCYR_PLUGIN_URL . 'js/raphael-min.js', array( 'jquery' ), CURRENCYR_VERSION );
      wp_enqueue_script('raphael-min');   
  
      wp_register_script('analog-clock-widget', CURRENCYR_PLUGIN_URL . 'js/analog-clock-widget.js', array( 'jquery' ), CURRENCYR_VERSION );
      wp_enqueue_script('analog-clock-widget');    
      self::$did_script = true;
    }             
  }  
  
  // Admin Panel Enqueue scripts.    
  function admin_scripts( $hook ) {      
    if ( 'widgets.php' != $hook ) {
      return;
    }    
    
    wp_enqueue_style( 'wp-color-picker' );    
    wp_enqueue_script( 'wp-color-picker' );
    wp_enqueue_script( 'underscore' );   
  }  
  
  // Get Time of location 
  public function getDateInfo($lng, $lat, $username) {
    
    if(!$lng || !$lat || !$username){
      return true;   
    }
    
    $url = "http://api.geonames.org/timezone?lat=$lat&lng=$lng&username=$username";       
    try {
      $timedata = file_get_contents($url);
      $sxml = simplexml_load_string($timedata); 
      if($sxml->timezone->time){
        $local_time = strtotime($sxml->timezone->time); 
        return array('error' => 0, 'date' => date("F j,Y H:i", $local_time));          
      }elseif(isset($sxml->status) && isset($sxml->status['message'])){      
        return array('error' => 1, 'message' => ucfirst($sxml->status['message']));      
      } 
      return true;
    } catch (Exception $exc) {        
      return true;
    }
  }
    
  // Widget output.  
  public function widget( $args, $instance ) {
    extract( $args );   
    $title = apply_filters( 'widget_title', $instance['title'] );       
    echo $args['before_widget'];       
    if ( ! empty( $title ) )
    echo $args['before_title'] . $title . $args['after_title']; 
    
    if(!$instance['longitude'] && !$instance['latitude'] && !$instance['username']) {
      $local_time = false; 
    } else {
      $local_time = $this->getDateInfo($instance['longitude'], $instance['latitude'], $instance['username']);
    }
    
    if(is_array($local_time)){
      
      if($local_time['error']){
        echo $local_time['message'];
        return; 
      } else {
        $local_time = $local_time['date'];
      }
     
    } elseif($local_time){
      echo __('Error! Please Validate your fields.', 'analog-clock-widget'); 
      echo $args['after_widget'];
      return; 
    }   
     
    ?>
    <?php if($instance['link']): ?><a href="<?php echo esc_url($instance['link']) ?>" target="<?php echo esc_attr($instance['target']) ?>"><?php endif; ?>
    <div id="u<?php echo $this->id; ?>"<?php echo $instance['clock_align'] == 'none' ? '' : 'style="text-align: '.$instance['clock_align'].';"'; ?>></div>
    <?php if($instance['link']): ?></a><?php endif; ?>
    <script>draw_clock("<?php echo $this->id; ?>", "<?php echo $instance['width']; ?>", "<?php echo $instance['background']; ?>", "<?php echo  $instance['stroke']; ?>", "<?php echo $instance['stroke_width'] ?>", "<?php echo $instance['hour_signs_color']; ?>", "<?php echo $instance['hour_hand_color'] ?>", "<?php echo $instance['hour_hand_width'] ?>", "<?php echo $instance['minute_hand_color'] ?>", "<?php echo $instance['minute_hand_width'] ?>", "<?php echo $instance['second_hand_color'] ?>", "<?php echo $instance['second_hand_width'] ?>", "<?php echo $instance['pin_bg'] ?>", "<?php echo $instance['pin_stroke_color'] ?>", "<?php echo $instance['pin_stroke_width'] ?>", "<?php echo $local_time; ?>")</script>
    <?php
    echo $args['after_widget'];
  }
  
  // Prints the settings form.
  public function form($instance){
      
    $instance = wp_parse_args( (array) $instance, array(
      'title' => '',  
      'width' => '200',
      'background' => '#ffffff',
      'stroke' => '#000000',
      'stroke_width' => '5',
      'hour_signs_color' => '#000000',
      'hour_hand_color' => '#000000',
      'hour_hand_width' => '6',
      'minute_hand_color' => '#000000',
      'minute_hand_width' => '4',
      'second_hand_color' => '#000000',
      'second_hand_width' => '2',            
      'pin_bg' => '#000000',
      'pin_stroke_color' => '#000000',
      'pin_stroke_width' => '5',
      'username' => '',
      'longitude' => '',
      'latitude' => '',
      'link' => '',
      'target' => '_self' ) ); 
 
    $instance = wp_parse_args( (array) $instance ); 
    ?>
    <script>
    jQuery(document).ready(function($) { 
      jQuery('.color-picker').on('focus', function(){
        var parent = jQuery(this).parent();
        jQuery(this).wpColorPicker()
        parent.find('.wp-color-result').click();
      }); 
    }); 
    </script>
    <p>
      <label for="<?php echo $this->get_field_id('title'); ?>"><?php echo __('Title', 'analog-clock-widget'); ?></label>
      <input type="text" name="<?php echo $this->get_field_name('title') ?>" id="<?php echo $this->get_field_id('title') ?> " value="<?php echo $instance['title'] ?>" class="widefat">
    </p>    
  
    <p>
      <label for="<?php echo $this->get_field_id('width'); ?>"><?php echo __('Width', 'analog-clock-widget'); ?></label>&nbsp;
      <input type="text" name="<?php echo $this->get_field_name('width') ?>" id="<?php echo $this->get_field_id('width') ?> " value="<?php echo $instance['width'] ?>" size="5">
    </p>
    <p>
      <label for="<?php echo $this->get_field_id('background'); ?>" style="display:block;"><?php echo __('Background', 'analog-clock-widget'); ?></label>
      <input type="text" name="<?php echo $this->get_field_name('background') ?>" id="<?php echo $this->get_field_id('background') ?> " value="<?php echo $instance['background']; ?>" class="widefat color-picker">
    </p>
    <p>
      <label for="<?php echo $this->get_field_id('stroke'); ?>" style="display:block;"><?php echo __('Stroke Color', 'analog-clock-widget'); ?></label>
      <input type="text" name="<?php echo $this->get_field_name('stroke') ?>" id="<?php echo $this->get_field_id('stroke') ?> " value="<?php echo $instance['stroke'] ?>" class="widefat color-picker">
    </p>
    <p>
      <label for="<?php echo $this->get_field_id('stroke_width'); ?>"><?php echo __('Stroke Width', 'analog-clock-widget'); ?></label>&nbsp;
      <input type="text" name="<?php echo $this->get_field_name('stroke_width') ?>" id="<?php echo $this->get_field_id('stroke_width') ?> " value="<?php echo $instance['stroke_width'] ?>" size="3">
    </p>
    <p>
      <label for="<?php echo $this->get_field_id('hour_signs_color'); ?>" style="display:block;"><?php echo __('Hour Signs', 'analog-clock-widget'); ?></label>
      <input type="text" name="<?php echo $this->get_field_name('hour_signs_color') ?>" id="<?php echo $this->get_field_id('hour_signs_color') ?> " value="<?php echo $instance['hour_signs_color'] ?>" class="widefat color-picker">
    </p>
    <p>
      <label for="<?php echo $this->get_field_id('clock_align'); ?>"><?php echo __('Clock align', 'image-text-widget'); ?></label>&nbsp;
      <select name="<?php echo $this->get_field_name('clock_align') ?>" id="<?php echo $this->get_field_id('clock_align') ?>">
        <?php foreach($this->clock_aligns as $id => $clock_align): ?>
          <option value="<?php echo esc_attr($id)?>" <?php echo selected($id, (isset($instance['clock_align']) ? $instance['clock_align'] : 'none'), FALSE) ?>><?php echo $clock_align; ?></option>
      <?php endforeach; ?>
      </select>       
    </p>  
    <hr>
    <h3><?php echo __('Hour Hand', 'analog-clock-widget'); ?></h3>
    <p>
      <label for="<?php echo $this->get_field_id('hour_hand_color'); ?>" style="display:block;"><?php echo __('Color', 'analog-clock-widget'); ?></label>
      <input type="text" name="<?php echo $this->get_field_name('hour_hand_color') ?>" id="<?php echo $this->get_field_id('hour_hand_color') ?> " value="<?php echo $instance['hour_hand_color'] ?>" class="widefat color-picker">
    </p>
    <p>
      <label for="<?php echo $this->get_field_id('hour_hand_width'); ?>"><?php echo __('Width', 'analog-clock-widget'); ?></label>
      <input type="text" name="<?php echo $this->get_field_name('hour_hand_width') ?>" id="<?php echo $this->get_field_id('hour_hand_width') ?> " value="<?php echo $instance['hour_hand_width'] ?>" size="3">
    </p>
    <hr>
    <h3><?php echo __('Minute Hand', 'analog-clock-widget'); ?></h3>
    <p>
      <label for="<?php echo $this->get_field_id('minute_hand_color'); ?>"  style="display:block;"><?php echo __('Color', 'analog-clock-widget'); ?></label>
      <input type="text" name="<?php echo $this->get_field_name('minute_hand_color') ?>" id="<?php echo $this->get_field_id('minute_hand_color') ?> " value="<?php echo $instance['minute_hand_color'] ?>" class="widefat color-picker">
    </p>
    <p>
      <label for="<?php echo $this->get_field_id('minute_hand_width'); ?>"><?php echo __('Width', 'analog-clock-widget'); ?></label>
      <input type="text" name="<?php echo $this->get_field_name('minute_hand_width') ?>" id="<?php echo $this->get_field_id('minute_hand_width') ?> " value="<?php echo $instance['minute_hand_width'] ?>" size="3">
    </p>
    <hr>
    <h3><?php echo __('Second Hand', 'analog-clock-widget'); ?></h3>
    <p>
      <label for="<?php echo $this->get_field_id('second_hand_color'); ?>"  style="display:block;"><?php echo __('Color', 'analog-clock-widget'); ?></label>
      <input type="text" name="<?php echo $this->get_field_name('second_hand_color') ?>" id="<?php echo $this->get_field_id('second_hand_color') ?> " value="<?php echo $instance['second_hand_color'] ?>" class="widefat color-picker">
    </p>
    <p>
      <label for="<?php echo $this->get_field_id('second_hand_width'); ?>"><?php echo __('Width', 'analog-clock-widget'); ?></label>
      <input type="text" name="<?php echo $this->get_field_name('second_hand_width') ?>" id="<?php echo $this->get_field_id('second_hand_width') ?> " value="<?php echo $instance['second_hand_width'] ?>" size="3">
    </p>
    <hr>
    <h3><?php echo __('Pin', 'analog-clock-widget'); ?></h3>
    <p>
      <label for="<?php echo $this->get_field_id('pin_bg'); ?>" style="display:block;"><?php echo __('Background', 'analog-clock-widget'); ?></label>
      <input type="text" name="<?php echo $this->get_field_name('pin_bg') ?>" id="<?php echo $this->get_field_id('pin_bg') ?> " value="<?php echo $instance['pin_bg'] ?>" class="widefat color-picker">
    </p>
    <p>
      <label for="<?php echo $this->get_field_id('pin_stroke_color'); ?>" style="display:block;"><?php echo __('Stroke Color', 'analog-clock-widget'); ?></label>
      <input type="text" name="<?php echo $this->get_field_name('pin_stroke_color') ?>" id="<?php echo $this->get_field_id('pin_stroke_color') ?> " value="<?php echo $instance['pin_stroke_color'] ?>" class="widefat color-picker">
    </p>
    <p>
      <label for="<?php echo $this->get_field_id('pin_stroke_width'); ?>"><?php echo __('Stroke Width', 'analog-clock-widget'); ?></label>
      <input type="text" name="<?php echo $this->get_field_name('pin_stroke_width') ?>" id="<?php echo $this->get_field_id('pin_stroke_width') ?> " value="<?php echo $instance['pin_stroke_width'] ?>" size="3">
    </p>  
    <hr>    
    <p>
      <label for="<?php echo $this->get_field_id('link'); ?>"><?php echo __('Link', 'analog-clock-widget'); ?></label>
      <input type="text" name="<?php echo $this->get_field_name('link') ?>" id="<?php echo $this->get_field_id('link') ?> " value="<?php echo $instance['link'] ?>" class="widefat">
    </p>
    <p>
        <label for="<?php echo esc_attr( $this->get_field_id( 'target' ) ); ?>"><?php esc_html_e( 'Open links in', 'analog-clock-widget' ); ?></label>
        <select id="<?php echo esc_attr( $this->get_field_id( 'target' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'target' ) ); ?>" class="widefat">
            <option value="_self" <?php selected( '_self', $instance['target'] ) ?>><?php esc_html_e( 'Current window (_self)', 'wp-instagram-widget' ); ?></option>
            <option value="_blank" <?php selected( '_blank', $instance['target'] ) ?>><?php esc_html_e( 'New window (_blank)', 'wp-instagram-widget' ); ?></option>
        </select>
    </p> 
    <hr>
    <h3><?php echo __('Local Time', 'analog-clock-widget'); ?></h3>
    <p>
      <label for="<?php echo $this->get_field_id('username'); ?>" style="display:block;"><?php echo __('Username', 'analog-clock-widget'); ?></label>
      <input type="text" name="<?php echo $this->get_field_name('username') ?>" id="<?php echo $this->get_field_id('username') ?> " value="<?php echo $instance['username'] ?>" class="widefat"><br>
      <small><?php echo __('To get the username you need to register <a href="http://www.geonames.org/login">GeoNames.org</a>. You will then receive an email with a confirmation link and after you have confirmed the email you can enable your account for the webservice on your account page.', 'analog-clock-widget'); ?></small>
    </p>  
    <p>
      <label for="<?php echo $this->get_field_id('longitude'); ?>" style="display:block;"><?php echo __('Longitude', 'analog-clock-widget'); ?></label>
      <input type="text" name="<?php echo $this->get_field_name('longitude') ?>" id="<?php echo $this->get_field_id('longitude') ?> " value="<?php echo $instance['longitude'] ?>" class="widefat">
    </p>
    <p>
      <label for="<?php echo $this->get_field_id('latitude'); ?>" style="display:block;"><?php echo __('Latitude', 'analog-clock-widget'); ?></label>
      <input type="text" name="<?php echo $this->get_field_name('latitude') ?>" id="<?php echo $this->get_field_id('latitude') ?> " value="<?php echo $instance['latitude'] ?>" class="widefat">
    </p>
  
  <?php
  }
  
  // Saves widget settings.  
  public function update($new_instance, $old_instance){
    $instance = $old_instance;        
    $instance['title'] = $new_instance['title'];
    $instance['width'] = $new_instance['width'];
    $instance['background'] = $new_instance['background'];
    $instance['stroke'] = $new_instance['stroke'];
    $instance['stroke_width'] = $new_instance['stroke_width'];   
    $instance['hour_signs_color'] = $new_instance['hour_signs_color'];
    $instance['clock_align'] = $new_instance['clock_align'];
    $instance['hour_hand_color'] = $new_instance['hour_hand_color'];
    $instance['hour_hand_width'] = $new_instance['hour_hand_width'];
    $instance['minute_hand_color'] = $new_instance['minute_hand_color'];
    $instance['minute_hand_width'] = $new_instance['minute_hand_width'];
    $instance['second_hand_color'] = $new_instance['second_hand_color'];
    $instance['second_hand_width'] = $new_instance['second_hand_width'];
    $instance['pin_bg'] = $new_instance['pin_bg'];
    $instance['pin_stroke_color'] = $new_instance['pin_stroke_color'];
    $instance['pin_stroke_width'] = $new_instance['pin_stroke_width'];        
    $instance['username'] = $new_instance['username'];
    $instance['longitude'] = $new_instance['longitude'];
    $instance['latitude'] = $new_instance['latitude'];
    $instance['link'] = $new_instance['link'];
    $instance['target'] = $new_instance['target'];
        
    return $instance;
  }          
}

function init_analog_clock_widget() {
  register_widget('Analog_Clock_Widget');
}
add_action('widgets_init', 'init_analog_clock_widget');