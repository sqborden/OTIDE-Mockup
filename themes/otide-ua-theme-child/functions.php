<?php
add_action( 'wp_enqueue_scripts', 'enqueue_parent_styles' );
function enqueue_parent_styles() {
wp_enqueue_style( 'parent-style', get_template_directory_uri().'/style.css' );
}


/*
* slider by redpishi.com 
* [slider height="450px" source="slider"]
*/
add_shortcode( 'slider', 'slider_func' );
function slider_func( $atts ) {
    $atts = shortcode_atts( array(
        'height' => '450px', 
		'source' => 'slider',		
    ), $atts, 'slider' );
static $asearch_first_call = 1;
$source = $atts["source"];
$height = $atts["height"];
$style0 = '<style>
#prev{
    left:0;
    border-radius: 0 10px 10px 0;
    margin-top: -2rem;
}
#next{
    right:0;
    border-radius:10px 0 0 10px;
}
#prev svg,#next svg{
    width:100%;
    fill: #fff;
}
#prev:hover,
#next:hover{
    background-color: rgba(0, 0, 0, 0.8);
}

</style>
';
$js0 = <<<EOD
<script>
const arrowHtml = '<span class="icon-arrow_back" id="prev"></span><span class="icon-arrow_forward" id="next"></span>'
let arrowIconLeft = '<?xml version="1.0" ?><!DOCTYPE svg  PUBLIC \'-//W3C//DTD SVG 1.1//EN\'  \'http://www.w3.org/Graphics/SVG/1.1/DTD/svg11.dtd\'><svg height="50px" id="Layer_1" style="enable-background:new 0 0 50 50;" version="1.1" viewBox="0 0 512 512" width="50px" color="#fff" xml:space="preserve" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink"><polygon points="352,115.4 331.3,96 160,256 331.3,416 352,396.7 201.5,256 "/></svg>';
</script>
EOD;
$style = '<style>
.'.$source.'{
    position: relative;
    overflow: hidden;
	height: '.$height.';
	padding: 0px!important;
}
.'.$source.' > div {
    width: 100%!important;
    position: absolute;
    transition: all 1s;
	height: '.$height.';
	margin: 0px!important;
 }
.'.$source.' > div {
	height: '.$height.';
 } 
.'.$source.' > span{
    height: 50px;
    position: absolute;
    top:50%;
    margin:-25px 0 0 0;
    background-color: rgba(0, 0, 0, 0.4);
    color:#fff;
    line-height: 50px;
    text-align: center;
    cursor: pointer;
}
</style>';
$js = <<<EOD
<script>
document.querySelector('.$source').innerHTML += arrowHtml;
let slides$source = document.querySelectorAll('.$source > div');
let slideSayisi$source = slides$source.length;
let prev$source = document.querySelector('.$source #prev');
let next$source = document.querySelector('.$source #next');
prev$source.innerHTML = arrowIconLeft;
next$source.innerHTML = arrowIconLeft;
next$source.querySelector('svg').style.transform = 'rotate(180deg)';
for (let index = 0; index < slides{$source}.length; index++) {
    const element{$source} = slides{$source}[index];
    element{$source}.style.transform = "translateX("+100*(index)+"%)";
}
let loop{$source} = 0 + 1000*slideSayisi{$source};
function goNext{$source}(){
    loop{$source}++;
            for (let index = 0; index < slides{$source}.length; index++) {
                const element{$source} = slides{$source}[index];
                element{$source}.style.transform = "translateX("+100*(index-loop{$source}%slideSayisi{$source})+"%)";
            }
}
function goPrev{$source}(){
    loop{$source}--;
            for (let index = 0; index < slides{$source}.length; index++) {
                const element{$source} = slides{$source}[index];
                element{$source}.style.transform = "translateX("+100*(index-loop{$source}%slideSayisi{$source})+"%)";
            }
}
next{$source}.addEventListener('click',goNext{$source});
prev{$source}.addEventListener('click',goPrev{$source});
document.addEventListener('keydown',function(e){
    if(e.code === 'ArrowRight'){
        goNext{$source}();
    }else if(e.code === 'ArrowLeft'){
        goPrev{$source}();
    }
});
</script>
EOD;
if ( $asearch_first_call == 1 ){	
	 $asearch_first_call++;
	 return "{$style0}{$style}{$js0}{$js}"; } elseif  ( $asearch_first_call > 1 ) {	$asearch_first_call++;
		return "{$style}{$js}"; }}

/*
* Register Child Theme Styles 
* 
*/
add_action( 'wp_enqueue_scripts', 'otide_child_theme_enqueue_styles' );

function otide_child_theme_enqueue_styles() {
	wp_enqueue_style( 
		'otide-child-theme-style', 
		get_stylesheet_uri()
	);
}