/**
 * Comment.
 *
 * @package FoundationPress
 */

// import $ from 'jquery';
// import whatInput from 'what-input';
//
// window.$ = $;
//
// import Foundation from 'foundation-sites';
// If you want to pick and choose which modules to include, comment out the above and uncomment
// the line below.
import './lib/foundation-explicit-pieces';
$( document ).foundation();

$( '.page_item_has_children a[href="#"]' ).click(
	function(e) {
		e.preventDefault();
	}
);
function updateBannerSize() {
  var w = window.innerWidth;
  var charCount = document.querySelector('.alert-block').innerText.length

  if(w <= 1200 && charCount > 130) {
    $('html').addClass('double-top-banner');
  }
  if(w <= 900 && charCount > 105) {
    $('html').addClass('double-top-banner');
  }
  if(w <= 768 && charCount >= 85) {
    $('html').addClass('double-top-banner');
  }
  if(w <= 600) {
    if(charCount > 65) {
      $('html').addClass('double-top-banner');
    }
    if(charCount > 155) {
      $('html').removeClass('double-top-banner');
      $('html').addClass('triple-top-banner');
    }
  }
  if(w <= 415 && charCount > 105) {
    $('html').removeClass('double-top-banner');
    $('html').addClass('triple-top-banner');
  }
  if(w <= 375 && charCount > 90) {
    $('html').removeClass('double-top-banner');
    $('html').addClass('triple-top-banner');
  }
  if(w <= 320) {
    if(charCount > 78) {
      $('html').removeClass('double-top-banner');
      $('html').addClass('triple-top-banner');
    }
    if(charCount > 118) {
      $('html').removeClass('triple-top-banner');
      $('html').addClass('quadruple-top-banner');
    }
  }
}
updateBannerSize();
