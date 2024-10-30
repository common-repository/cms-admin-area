jQuery(document).ready(function() {

     jQuery("#adminmenu li.menu-top").hover(
             function () {
                 jQuery(this).addClass("wp-hover-menu");
             },
             function () {
                 setTimeout(function() {
                     jQuery(this).removeClass("wp-hover-menu");
                 }, 3000)

             }
     );

 });