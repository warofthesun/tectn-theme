/*
 * Starter Scripts File

*/
(() => {
	const layers = [
	  { el: document.getElementById("curveFront"), speed: 0.10 },
	  { el: document.getElementById("curveMid"),   speed: 0.18 },
	  { el: document.getElementById("curveBack"),  speed: 0.26 },
	].filter(l => l.el);
  
	if (!layers.length) return;
  
	let ticking = false;
  
	const update = () => {
	  ticking = false;
  
	  // If your page scrolls inside another container, swap this (see note below)
	  const y = window.scrollY || document.documentElement.scrollTop || 0;
  
	  // Keep movement subtle (cap it)
	  const max = 18; // px
	  const base = Math.max(-max, Math.min(max, -y * 0.03));
  
	  layers.forEach(({ el, speed }) => {
		const dy = base * (1 + speed); // slightly different per layer
		el.setAttribute("transform", `translate(0 ${dy.toFixed(2)})`);
	  });
	};
  
	const onScroll = () => {
	  if (!ticking) {
		ticking = true;
		requestAnimationFrame(update);
	  }
	};
  
	window.addEventListener("scroll", onScroll, { passive: true });
	update(); // run once on load
  })();

/*
 * Get Viewport Dimensions
 * returns object with viewport dimensions to match css in width and height properties
 * ( source: http://andylangton.co.uk/blog/development/get-viewport-size-width-and-height-javascript )
*/
function updateViewportDimensions() {
	var w=window,d=document,e=d.documentElement,g=d.getElementsByTagName('body')[0],x=w.innerWidth||e.clientWidth||g.clientWidth,y=w.innerHeight||e.clientHeight||g.clientHeight;
	return { width:x,height:y };
}
// setting the viewport width
var viewport = updateViewportDimensions();

// Animate SVG background overlays on scroll
document.addEventListener("DOMContentLoaded", () => {
	const sections = document.querySelectorAll(".c-posts");
	if (!sections.length) return;
  
	let active = new Set();
  
	// Only animate when in view
	const io = new IntersectionObserver((entries) => {
	  entries.forEach((e) => {
		if (e.isIntersecting) active.add(e.target);
		else active.delete(e.target);
	  });
	}, { threshold: 0.15 });
  
	sections.forEach((s) => io.observe(s));
  
	// Scroll speed tracking
	let lastY = window.scrollY;
	let lastT = performance.now();
  
	// Smoothed blur values
	let blurFront = 12;
	let blurBack  = 8;
  
	const clamp = (n, min, max) => Math.max(min, Math.min(max, n));
	const lerp = (a, b, t) => a + (b - a) * t;
  
	function tick(now) {
	  const y = window.scrollY;
	  const dt = Math.max(16, now - lastT);  // ms
	  const dy = Math.abs(y - lastY);
  
	  // px/ms -> scale up a bit
	  const v = (dy / dt) * 1000; // px/s
  
	  // map velocity to blur range
	  const targetFront = clamp(12 + v * 0.05, 12, 45); // 12..22
	  const targetBack  = clamp(8  + v * 0.010, 8, 16);  // 8..16
  
	  // smooth (prevents jitter)
	  blurFront = lerp(blurFront, targetFront, 0.12);
	  blurBack  = lerp(blurBack,  targetBack,  0.12);
  
	  // apply only to active sections
	  active.forEach((section) => {
		const g = section.querySelector(".js-blurFront");
		const d = section.querySelector(".js-blurBack");
  
		if (g) g.setAttribute("stdDeviation", blurFront.toFixed(2));
		if (d) d.setAttribute("stdDeviation", blurBack.toFixed(2));
	  });
  
	  lastY = y;
	  lastT = now;
	  requestAnimationFrame(tick);
	}
  
	requestAnimationFrame(tick);
  });

/*
 * Throttle Resize-triggered Events
 * Wrap your actions in this function to throttle the frequency of firing them off, for better performance, esp. on mobile.
 * ( source: http://stackoverflow.com/questions/2854407/javascript-jquery-window-resize-how-to-fire-after-the-resize-is-completed )
*/
var waitForFinalEvent = (function () {
	var timers = {};
	return function (callback, ms, uniqueId) {
		if (!uniqueId) { uniqueId = "Don't call this twice without a uniqueId"; }
		if (timers[uniqueId]) { clearTimeout (timers[uniqueId]); }
		timers[uniqueId] = setTimeout(callback, ms);
	};
})();

// how long to wait before deciding the resize has stopped, in ms. Around 50-100 should work ok.
var timeToWaitForLast = 100;


/*
 * Here's an example so you can see how we're using the above function
 *
 * This is commented out so it won't work, but you can copy it and
 * remove the comments.
 *
 *
 *
 * If we want to only do it on a certain page, we can setup checks so we do it
 * as efficient as possible.
 *
 * if( typeof is_home === "undefined" ) var is_home = $('body').hasClass('home');
 *
 * This once checks to see if you're on the home page based on the body class
 * We can then use that check to perform actions on the home page only
 *
 * When the window is resized, we perform this function
 * $(window).resize(function () {
 *
 *    // if we're on the home page, we wait the set amount (in function above) then fire the function
 *    if( is_home ) { waitForFinalEvent( function() {
 *
 *	// update the viewport, in case the window size has changed
 *	viewport = updateViewportDimensions();
 *
 *      // if we're above or equal to 768 fire this off
 *      if( viewport.width >= 768 ) {
 *        console.log('On home page and window sized to 768 width or more.');
 *      } else {
 *        // otherwise, let's do this instead
 *        console.log('Not on home page, or window sized to less than 768.');
 *      }
 *
 *    }, timeToWaitForLast, "your-function-identifier-string"); }
 * });
 *
 * Pretty cool huh? You can create functions like this to conditionally load
 * content and other stuff dependent on the viewport.
 * Remember that mobile devices and javascript aren't the best of friends.
 * Keep it light and always make sure the larger viewports are doing the heavy lifting.
 *
*/

/*
 * We're going to swap out the gravatars.
 * In the functions.php file, you can see we're not loading the gravatar
 * images on mobile to save bandwidth. Once we hit an acceptable viewport
 * then we can swap out those images since they are located in a data attribute.
*/
function loadGravatars() {
  // set the viewport using the function above
  viewport = updateViewportDimensions();
  // if the viewport is tablet or larger, we load in the gravatars
  if (viewport.width >= 768) {
  jQuery('.comment img[data-gravatar]').each(function(){
    jQuery(this).attr('src',jQuery(this).attr('data-gravatar'));
  });
	}
} // end function


/* This is the bit that makes the scroll animation

Uncomment  the below to exclude first section from animation
*/


sr.reveal('.single-post:not(:first-child)');

sr.reveal('.single-post');

document.addEventListener("DOMContentLoaded", () => {
	const sections = document.querySelectorAll(".c-posts");
  
	if (!("IntersectionObserver" in window)) {
	  sections.forEach(s => s.classList.add("is-inview"));
	  return;
	}
  
	const io = new IntersectionObserver(
	  (entries) => {
		entries.forEach((entry) => {
		  if (entry.isIntersecting) {
			entry.target.classList.add("is-inview");
			io.unobserve(entry.target);
		  }
		});
	  },
	  { threshold: 0.15 }
	);
  
	sections.forEach((s) => io.observe(s));
  });


/*
 * Put all your regular jQuery in here.
*/
jQuery(document).ready(function($) {

	$("#mobile-nav").click(function(){
        $(".nav").toggleClass("show");
		$("#mobile-nav").toggleClass("show");
	});

	var $window = $(window),
        $nav = $('.nav');

    function resize() {
        if ($window.width() > 1024) {
            $("#mobile-nav").removeClass("show");
			$(".nav").removeClass("show");
        }
    }

    $window
        .resize(resize)
        .trigger('resize');
 
      
	 $('.faq_question').click(function() {	 
		 if ($(this).parent().is('.open')){
			 $(this).closest('.faq').find('.faq_answer_container').animate({'height':'0'},500);
			 $(this).closest('.faq').removeClass('open');
			 $(this).parent().find('.accordion-button-icon').removeClass('fa-regular fa-circle-caret-up').addClass('fa-regular fa-circle-caret-down');
		 }
		 else{
			 var newHeight =$(this).closest('.faq').find('.faq_answer').height() +'px';
			 $(this).closest('.faq').find('.faq_answer_container').animate({'height':newHeight},500);
			 $(this).closest('.faq').addClass('open');
			 $(this).parent().find('.accordion-button-icon').removeClass('fa-regular fa-circle-caret-down').addClass('fa-regular fa-circle-caret-up');
		}	 
	 });



}); /* end of as page load scripts */
