function draw_clock(clock_id, width, background, stroke, stroke_width, hour_signs_color, hour_hand_color, hour_hand_width, minute_hand_color, minute_hand_width, second_hand_color, second_hand_width, pin_bg, pin_stroke_color, pin_stroke_width, local_time){ 
  var seconds = 0; 
  if(local_time){
    local = new Date();    
    now = new Date(local_time);        
    now.setSeconds(local.getSeconds()); 
    var d2 = new Date(local.getFullYear(), local.getMonth(), local.getDay(), local.getHours(), local.getMinutes());
    var d1 = new Date(now.getFullYear(), now.getMonth(), now.getDay(), now.getHours(), now.getMinutes());    
    seconds =  (d1-d2)/1000;
    local.addSeconds(seconds);  
   } 
   
  jQuery("#u"+clock_id).attr('data-o', seconds);
  canvas = Raphael("u"+clock_id,width, width);
  var clock = canvas.circle(width*.5,width*.5, width * .475);
  clock.attr({"fill":background,"stroke":stroke,"stroke-width":stroke_width});
  var hour_sign;
  for(i=0;i<12;i++){
    var start_x = width*.5+Math.round((width*.4)*Math.cos(30*i*Math.PI/180));
    var start_y = width*.5+Math.round((width*.4)*Math.sin(30*i*Math.PI/180));
    var end_x = width*.5+Math.round((width*.45)*Math.cos(30*i*Math.PI/180));
    var end_y = width*.5+Math.round((width*.45)*Math.sin(30*i*Math.PI/180));
    hour_sign = canvas.path("M"+start_x+" "+start_y+"L"+end_x+" "+end_y);
    hour_sign.attr({stroke: hour_signs_color});
  }
  hour_hand = canvas.path("M" + width*.5 + " " + width*.5 + "L" + width*.5 + " " + (width*.25) + "");
  hour_hand.attr({stroke: hour_hand_color, "stroke-width": hour_hand_width});           
  minute_hand = canvas.path("M" + width*.5 + " " + width*.5 + "L" + width*.5 + " " + (width*.2) + "");
  minute_hand.attr({stroke: minute_hand_color, "stroke-width": minute_hand_width});
  second_hand = canvas.path("M" + width*.5 + " " + (width*.55) + "L" + width*.5 + " " + (width*.125) + "");
  second_hand.attr({stroke: second_hand_color, "stroke-width": second_hand_width}); 
  var pin = canvas.circle(width*.5, width*.5, pin_stroke_width);
  pin.attr({"fill":pin_bg,"stroke":pin_stroke_color});   
  update_clock(clock_id, width, hour_hand, minute_hand, second_hand);         
}

function update_clock(clock_id, width, hour_hand, minute_hand, second_hand){    
  var now = new Date(); 
  var sec = jQuery("#u"+clock_id).attr('data-o');
  sec = parseInt(sec);
  if(sec){
    now.addSeconds(sec);
   
  }
  var hours = now.getHours();
  var minutes = now.getMinutes();
  var seconds = now.getSeconds();      
  hour_hand.transform("r"+ (30*hours+(minutes/2.5)) + ", "+width*.5+", " + width*.5);
  minute_hand.transform("r"+ 6*minutes + ", "+width*.5+", " + width*.5);
  second_hand.transform("r"+ 6*seconds + ", "+width*.5+", " + width*.5);  
  setTimeout(function() {update_clock(clock_id, width, hour_hand, minute_hand, second_hand)}, 1000);
}

Date.prototype.addSeconds = function(seconds) {
    this.setSeconds(this.getSeconds() + seconds);
    return this;
};