/**
 * Comment.
 *
 * @package FondationPress
 */

import $ from 'jquery';
import { Foundation } from 'foundation-sites/js/foundation.core';
import { rtl, GetYoDigits, transitionend } from 'foundation-sites/js/foundation.core.utils';
import { Box } from 'foundation-sites/js/foundation.util.box'
import { onImagesLoaded } from 'foundation-sites/js/foundation.util.imageLoader';
import { Keyboard } from 'foundation-sites/js/foundation.util.keyboard';
import { MediaQuery } from 'foundation-sites/js/foundation.util.mediaQuery';
import { Motion, Move } from 'foundation-sites/js/foundation.util.motion';
import { Nest } from 'foundation-sites/js/foundation.util.nest';
import { Timer } from 'foundation-sites/js/foundation.util.timer';
import { Touch } from 'foundation-sites/js/foundation.util.touch';
import { Triggers } from 'foundation-sites/js/foundation.util.triggers';
import { Dropdown } from 'foundation-sites/js/foundation.dropdown';
// TODO: remove the following override upon next Foundation release //.
import { Magellan } from './foundation.magellan';
import { Sticky } from 'foundation-sites/js/foundation.sticky';
import { Toggler } from 'foundation-sites/js/foundation.toggler';
Foundation.addToJquery( $ );

// Add Foundation Utils to Foundation global namespace for backwards compatibility.
Foundation.rtl           = rtl;
Foundation.GetYoDigits   = GetYoDigits;
Foundation.transitionend = transitionend;
Foundation.Box            = Box;
Foundation.onImagesLoaded = onImagesLoaded;
Foundation.Keyboard       = Keyboard;
Foundation.MediaQuery     = MediaQuery;
Foundation.Motion         = Motion;
Foundation.Move           = Move;
Foundation.Nest           = Nest;
Foundation.Timer          = Timer;

// Touch and Triggers previously were almost purely sede effect driven,
// so no need to add it to Foundation, just init them.
Touch.init( $ );

Triggers.init( $, Foundation );

Foundation.plugin( Dropdown, 'Dropdown' );
Foundation.plugin( Magellan, 'Magellan' );
Foundation.plugin( Sticky, 'Sticky' );
Foundation.plugin( Toggler, 'Toggler' );

export default Foundation;
