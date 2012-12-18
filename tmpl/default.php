

<?php
/**
 * @package     Joomla.Site
 * @subpackage  mod_bw_menu_flower
 *
 * @copyright   Copyright (C) 2012 Brian Williford, All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @ Inquiries	info@ohmybrain.com - http://www.brianwilliford.com 
 * Last Modified: BW 121217a
 */

defined('_JEXEC') or die;

	// module vars here man
	$include_scripts	= $params->get('include_scripts', 1);
	$wrapper_bgcolor	= $params->get('css_wrapper_color','transparent');	
	$wrapper_size		= $params->get('css_wrapper_size','auto');
	$wrapper_size_ext	= $params->get('css_wrapper_size','auto');
	$flower_size_start	= $params->get('css_flower_size_start','0');
	$flower_size_end	= $params->get('css_flower_size_end','130');
	$pedal_rotate		= $params->get('pedal_rotate', 0);
	$begin_messaage		= $params->get('begin_message',' ');
	$control_panel		= $params->get('control_panel', 0);
	if($control_panel == 1){ 
		$wrapper_size_ext	= $params->get('css_wrapper_size')+50;
	}
	//echo "param: " . $pedal_rotate . "<br />";
	
	$doc =& JFactory::getDocument();
	$doc->addStyleSheet( 'modules/mod_bw_menu_flower/helpers/mod_bw_menu_flower.css' );
	if($include_scripts == 1){ 
		//$doc->addScript('modules/mod_bw_menu_flower/helpers/jquery.min.js');
		
	}
	JHtml::_('jquery.framework');  // joomla 3.0 :: includes no-conflict call :: JHtml::_('jquery.framework', false); does not
	$doc->addScript('modules/mod_bw_menu_flower/helpers/path.min.js');
	$doc->addScript('modules/mod_bw_menu_flower/helpers/jquery.ui.touch-punch.min.js');
?>
<?php // The menu class is deprecated. Use nav instead. ?>
<!--
<script src="modules/mod_bw_menu_flower/helpers/jquery.min.js"></script>
-->
<script language="javascript" type="text/javascript">
	// set up global vars
	bw_gvars = {};
	bw_gvars.flowerBloomed		= 0; // no touch
	
	// user editable
	bw_gvars.pedalRotate		= <?php echo $pedal_rotate ?>; // 0 parallel to horizon :: 1 pedals perpendicular radius
	bw_gvars.pedalAngle			= 90; // 45 can be fun / the above pedalRotate flips this integer 90 degrees
	bw_gvars.cssWrapperSize		= <?php echo $wrapper_size; ?>; // in pixels
	bw_gvars.radiusSizeStart	= <?php echo $flower_size_start; ?>; // in pixe
	bw_gvars.radiusSizeEnd		= <?php echo $flower_size_end; ?>; // in pixe
</script>

<style>
	#mod_bw_menu_flower_wrapper{
		position: relative;
		background: <?php echo $wrapper_bgcolor; ?>;
		top: 0px;
		margin-left: auto;
		margin-right: auto;
		width: <?php echo $wrapper_size; ?>px;
		max-width: <?php echo $wrapper_size; ?>px;
		height: <?php echo $wrapper_size_ext; ?>px;
		max-height: <?php echo $wrapper_size_ext; ?>px;
		overflow: hidden;
	}
	
	#menu-flower-pedal-wrapper{
		position: absolute;
		width: <?php echo $wrapper_size; ?>px;
		max-width: <?php echo $wrapper_size; ?>px;
		height: <?php echo $wrapper_size_ext; ?>px;
		top: 80px;
		left: 0px;
		z-index: 1; /* set the pedals, or menu layer to be below the center - This gets flipped on center mouseleave */
}
</style>

<div id="mod_bw_menu_flower_wrapper">
	
  	<!-- center image -->
	<div id="menu-flower-center-image-wrapper">
		<div class="loading-circle-inner"></div>
		<div class="circular-frame-2">
			<div class="circular-frame-outer-1 circle"></div>
			<div class="circular-frame-outer-2 circle"></div>
			<figure>			
				<?php if ($link) : ?>
				<a href="<?php echo $link; ?>">
				<?php endif; ?>
					<?php echo JHtml::_('image', $image->folder.'/'.$image->name, "I Am A Flower",  array('width' => $image->width, 'height' => $image->height)); ?>
				<?php if ($link) : ?>
				</a>
				<?php endif; ?>
	
				<figcaption class="circular-frame-caption"><?php echo $begin_messaage; ?></figcaption>
			</figure>
		</div>
	</div>
		
	<!-- this is the javascript manipluated circumference the menu is placed around -->
	<div id="menu-flower-pedal-wrapper">
	     <canvas></canvas>
	
	    <!-- joomla menu ouput -->
		<ul class="menu-flower-items <?php echo $class_sfx;?>"<?php
			$tag = '';
			if ($params->get('tag_id') != null){
				$tag = $params->get('tag_id').'';
				echo ' id="'.$tag.'"';
			}
		?>>
		<?php
		foreach ($list as $i => &$item) :
			$class = 'item-'.$item->id;
			if ($item->id == $active_id) {
				$class .= ' current';
			}
		
			if (in_array($item->id, $path)) {
				$class .= ' active';
			}
			elseif ($item->type == 'alias') {
				$aliasToId = $item->params->get('aliasoptions');
				if (count($path) > 0 && $aliasToId == $path[count($path) - 1]) {
					$class .= ' active';
				}
				elseif (in_array($aliasToId, $path)) {
					$class .= ' alias-parent-active';
				}
			}
		
			if ($item->type == 'separator') {
				$class .= ' divider';
			}
		
			if ($item->deeper) {
				$class .= ' deeper';
			}
		
			if ($item->parent) {
				$class .= ' parent';
			}
		
			if (!empty($class)) {
				$class = ' class="'.trim($class) .' mod-flower-pedal-text"';
			}
		
			echo '<li'.$class.'>';
		
			// Render the menu item.
			switch ($item->type) :
				case 'separator':
				case 'url':
				case 'component':
				case 'heading':
					require JModuleHelper::getLayoutPath('mod_bw_menu_flower', 'default_'.$item->type);
					break;
		
				default:
					require JModuleHelper::getLayoutPath('mod_bw_menu_flower', 'default_url');
					break;
			endswitch;
		
			// The next item is deeper.
			if ($item->deeper) {
				echo '<ul id="pedal-'.$item->id.'" class="nav-child unstyled">';
			}
			// The next item is shallower.
			elseif ($item->shallower) {
				echo '</li>';
				echo str_repeat('</ul></li>', $item->level_diff);
			}
			// The next item is on the same level.
			else {
				echo '</li>';
			}
		endforeach;
		?></ul>
  </div>
  

	
  <?php if($control_panel == 1){ ?>
  	<div class="menu-flower-controls-wrapper">
  		<div class="iphone-toggle-buttons">
            <ul>
                <li><div>Toggle Rotate</div><label for="toggle-rotate"><input type="checkbox" name="toggle-rotate" id="toggle-rotate" checked="checked" /><span>Toggle Rotate</span></label></li>
                <li><div>Toggle Translation</div><label for="toggle-translation"><input type="checkbox" name="toggle-translation" id="toggle-translation" checked="checked" /><span>Toggle Translation</span></label></li>
                <li><div>Toggle Guides</div><label for="toggle-guide"><input type="checkbox" name="toggle-guide" id="toggle-guide" /><span>Toggle Guides</span></label></li>
                <li><div>Animate on Path</div><label for="run-animate-path"><input type="checkbox" name="run-animate-path" id="run-animate-path"  /><span>Animate on Path</span></label></li>
                <li><div>Align to Middle/Bottom</div><label for="radio-0"><input type="radio" name="radio-button-group-0" id="radio-0" class="move-relative" relative-to="1" checked="checked" /><span>Align to Middle/Bottom</span></label></li>
                <li><div>Align to Top/Right</div><label for="radio-1"><input type="radio" name="radio-button-group-0" id="radio-1" class="move-relative" relative-to="2" /><span>Align to Top/Right</span></label></li>
                <li><div>Align to Bottom/Left</div><label for="radio-2"><input type="radio" name="radio-button-group-0" id="radio-2" class="move-relative" relative-to="3" /><span>Align to Bottom/Left<</span></label></li>
			</ul>
		</div>
	</div>
	<br clear="all">
	<?php } ?>
</div>	
	
<script type="text/javascript">
	
	// jquery time
	jQuery(function ($) { 
	
		// fade other menus so sub menu is not hidden. z-indexes are bound to parent and not restackable
		$(".menu-flower-items > li").hover(function() { // Mouse over
			$(this).siblings().stop().fadeTo(300, 0.5);
			$(this).parent().siblings().stop().fadeTo(300, 0.5); 
		
		}, function() { // Mouse out
			$(this).siblings().stop().fadeTo(1000, 1);
			$(this).parent().siblings().stop().fadeTo(300, 1);
		});			
			
		// vars
	    var bw_example_1				= $('.soo-example-shell-1');
	    var bw_circular_frame			= $('.circular-frame-2');
	    var bw_circular_frame_hovered	= $('.circular-frame-2 .circular-frame-outer-2');
	    
		// center circle hover begin - exands flower center image, blooms pedals
		$(".circular-frame-2").bind( { "mouseenter": function( event) {  
		       bw_circular_frame.addClass('circular-frame-hovered');  
		       $("#menu-flower-pedal-wrapper").animate( {width: '800px'}, 1500);
		       $(".loading-circle-inner").animate( {opacity: '0'}, 1500); 
		       
		       // call bloom function
		       expandFlowerPedals();
		       
		    },"mouseleave": function( event) {
		         $(".loading-circle-inner").animate( {opacity: '1'}, 2500);
		         // set the pedals, or menu layer to be above the center
		         $("#menu-flower-pedal-wrapper").css({ 'z-index':'50000'}); 
	
		    }, "click": function( event ) {
		       	 $(".loading-circle-inner").animate( {opacity: '0'}, 500);
		    }
		});
		
		// bloom function - circle start point exapnded on center image rollover. set start and end size the same to disable bloom animation
		function expandFlowerPedals(){
			//alert('called');
			var radiusSizeStart		= bw_gvars.radiusSizeStart;
			var radiusSizeEnd		= bw_gvars.radiusSizeEnd;	
			var pedalRotate			= bw_gvars.pedalRotate;
			//
			if(radiusSizeStart != radiusSizeEnd){
				if(bw_gvars.flowerBloomed < 1){
					(function movePedal() {
					  setTimeout(function() {
					    if (radiusSizeStart++ < radiusSizeEnd) {
					   	 //alert('loop: ' + radiusSizeStart);
					      setFlowerCanvas(radiusSizeStart,1,0,pedalRotate);
					      movePedal();
					    }
					  }, 10);
					})();
				}
			}
			// bloomed
			bw_gvars.flowerBloomed	= 1;
		} 


	// set up the flower like canvas of elements and sub elements around circle via (x',y')=(cx+r*cos(α),cy+r*sin(α)
	function setFlowerCanvas(circle_size,pedal_orientation=1,angleOffset=0,pedal_rotate=0){
		/*
			* (x',y')=(cx+r*cos(α),cy+r*sin(α)) appled set joomla menu items and sub-menu items around a circle 
			* formula/math credit goes to Ben Olson: http://benknowscode.wordpress.com/2012/09/24/aligning-dom-elements-around-a-circle/
			* path.js required and located at: http://bseth99.github.com/
			* implimentatin and creative by Brian Williford http://www.brianwilliford.com/
			//
			* circle_size 		= integer / radius in pixels
			* pedal_orientation = ineger / 1 = perpendicular to circle :: 2 = parallel to the horizon (not done)
		*/
	
		//alert("circle_size passed: " + circle_size);
		//alert("pedal_orientation: " + pedal_orientation);
		//alert("angleOffset: " + angleOffset);
		//alert("pedal_rotate: " + pedal_rotate);
				
		// vars
		var drawGuides = false,
		doRotate = pedal_rotate,
		doTranslate = true,
		angleOffset = angleOffset,
		translateRelative = pedal_orientation,
		doOnce = true;
		// adjust offeset settings for pedal rotation and template changes
		if(bw_gvars.pedalRotate < 1){
			cxOffset = 0;
			cyOffset = 60;
			circle_size	= circle_size+36;
		} else {
			cxOffset = 0;
			cyOffset = 102;
		}
		// circleRadius = radius : cx = width : cy = height 
		var circleRadius = circle_size, cx = bw_gvars.cssWrapperSize/2+cxOffset, cy = bw_gvars.cssWrapperSize/2-cyOffset,
		$circle = $('#menu-flower-pedal-wrapper canvas'),
		canvas1 = $circle[0];
	       
	   // add some general padding
	   canvas1.width = cx * 2 + 4;
	   canvas1.height = cy * 2 + 4;
	   //alert("canvas1.width: " +canvas1.width);
	   //alert("canvas1.height: " +canvas1.height);
	  // alert('circleRadius: '+circleRadius);
	  
	  //circleRadius = circleRadius+10;
	
	   // Center of the circle relative to canvas
	   cx += 2;
	   cy += 2;
	   //alert("cx: " + cx +  " cy: " + cy);

		// set the postion around path
		function position(){
	
			// center of the circle relative to the page
			var cpos = $circle.offset();
			px = cpos.left + cx,
			py = cpos.top + cy;
			//alert("px: " + px +  " py: " + py);
			
			// items along path
			var $items = $('#menu-flower-pedal-wrapper .menu-flower-items').children(),
			icnt = $items.length,
			dstep = 360 / icnt,
			cpath = PATH([
				{fn: 'start', x: cx+circleRadius, y: cy},
				{fn: 'circle', radius: circleRadius}
			]);
	
			// Move the start of the circle to the top.
			cpath.rotate(-bw_gvars.pedalAngle, cpath.center());
			
			var ctx = canvas1.getContext( "2d" ),
			$pts = $('#menu-flower-pedal-wrapper #points');
			
			ctx.fillStyle = "white"; // you could change this
			
			if (!drawGuides) {
			  ctx.clearRect(0, 0, canvas1.width, canvas1.height);
			}
			
			$items.each(function (idx, el){
				var $el = $(el),
				   angle = dstep * idx,
				   pt = cpath.step((angle + angleOffset) / 360),
				   tfm = '',
				   tx, ty;
				   
				  // layout methods
				switch (translateRelative){
				  case 1 : // bottom / middle
				     tx = $el.outerWidth(true) / 2;
				     ty = $el.outerHeight(true);
				     break;
				
				  case 2 : // top / right
				     tx = $el.outerWidth(true);
				     ty = 0;
				     angle -= 180;  // Need to flip them over to align to the top ...
				     break;
				
				  case 3 : // bottom/left
				     tx = 0;
				     ty = $el.outerHeight(true);
				     break;
				  }
					  //alert('angle: '+ angle + 'angleOffset: ' + angleOffset);
					  //angle = 45;
			
			   tfm += doRotate ? 'rotate('+(angle + angleOffset)+'deg) ' : '' ;
			   tfm += doTranslate ? 'translate(-'+(tx)+'px, -'+(ty)+'px)' : '';
			
				$el.css({
					left: (pt.x) + 'px',
					top: (pt.y) + 'px',
					transformOrigin: 'top left',
					transform: tfm
				});
				
				$($pts.children()[idx]).css({
					left: (pt.x) + 'px',
					top: (pt.y) + 'px'
				});
			});
			
			// option show canvas circle radius
			if (drawGuides && doOnce){
			 ctx.strokeStyle = "black";
			 ctx.lineWidth = 1;
			
			 ctx.beginPath();
			 ctx.arc(cx, cy, r, 0, 2*Math.PI);
			 ctx.stroke();
			}
			
			doOnce = false;
			
		}
	
	   // hidden controller functions. Unhide controller to play here
	   $('#toggle-guide').click(function (){
	        drawGuides = !drawGuides;
	        doOnce = drawGuides;
	        position();
	     });
	
	   $('#toggle-rotate').click(function (){
	        doRotate = !doRotate;
	        position();
	     });
	
	   $('#toggle-translation').click(function (){
	        doTranslate = !doTranslate;
	        position();
	     });
	
	   $('.move-relative').click(function (){
	        translateRelative = parseInt($(this).attr('relative-to'), 10);
	        position();
	     });
	
	   $('#run-animate-path').click(function (){
	        aOff = 0;
	
	        $(this).animate({tabIndex: 0},
	           {
	              duration: 10000,
	              easing: 'linear',
	              step: function (now, fx) { aOff = 360 * fx.pos; position();}
	           });
	     });
		
		// set position around path		 
	   position();
	};

	// pre-bloom start point
	setFlowerCanvas(<?php echo $flower_size_start; ?>,1,0,bw_gvars.pedalRotate);

	});
</script>
