/**
 * Handles the sidebar: if a sidebar is present in the current page,
 * sets it up so it scrolls along with the page as far as the page
 * will allow.
 * 
 * @package Application
 * @subpackage UI
 * @class Sidebar
 * @static
 * @author Sebastian Mordziol <s.mordziol@mistralys.eu>
 */
var Sidebar = 
{
	'ERROR_TOC_LIBRARY_NOT_LOADED':14201,
		
	'element':null,
	'parent':null,
	'minTop':null,
	
   /**
    * The offset added from the top added to the scrolled sidebar,
    * to account for the height of the fixed navigation bar.
    * @property {Integer}
    */
	'OffsetTop':90,
	
   /**
    * The sidebar scroll animation speed
    * @property {Integer}
    */
	'Speed':350,
	
   /**
    * The type of jQuery animation to use for the scrolling
    * @property {String}
    */
	'Animation':'linear',
	
   /**
    * Starts the sidebar handling: checks if the page has a sidebar
    * at all, and if it does registers the required events to make
    * it scroll along with the page.
    *
    */
	Start:function()
	{
		this.element = $('#sidebar .sidebar-wrap, #quickjump');

		// some pages do not have a sidebar
		if(this.element.length==0) {
			return;
		}
		
		application.log('Sidebar', 'Sidebar found, setting up automatic scrolling.', 'ui');
		
		var offset = this.element.offset();
		this.minTop = offset.top-this.OffsetTop;
		this.parent = this.element.parent();
		
		$(window).bind('scroll', function() {
			Sidebar.Handle_Scroll();
		});
		
		$(window).bind('resize', function() {
			Sidebar.Handle_ResizeWindow();
		});
	},
	
   /**
    * Event handler for when the browser window gets resized to adjust the
    * sidebar's position if need be.
    *
    */
	Handle_ResizeWindow:function()
	{
		this.Handle_Scroll();
	},
	
   /**
    * Event handler for when the user scrolls the page, to adjust the sidebar's
    * position accordingly.
    *
    */
	Handle_Scroll:function()
	{
		// Determine the availabe bounding box in which we can move around.
		// We do this each time, so the container element can be resized at
		// will, and the positions of the elements can shift as well.
		var maxTop = this.minTop + this.parent.innerHeight() - this.element.height();
		
		// where should the element be positioned? Make sure that
		// it does not move out of the bounding box.
		var top = $(window).scrollTop();
		if(top < this.minTop) {
			top = this.minTop;
		} else if( top > maxTop) {
			top = maxTop;
		}
		
		// from the offsets we can now determine the upper margin of the sidebar:
		// this way we don't have to position it absolutely and resizing the window
		// will not create any issues.
		var margin = top-this.minTop;
		
		// animate to the target position
		this.element.stop().animate(
			{'padding-top': margin}, 
			this.Speed, 
			this.Animation
		);
	},
	
	CreateFormTOC:function(formName, containerID)
	{
		if(typeof(Sidebar_FormTOC) == 'undefined') {
			throw new ApplicationException(
				'Library not loaded',
				'To use the form TOC, the [Sidebar_FormTOC] library must be loaded manually.',
				this.ERROR_TOC_LIBRARY_NOT_LOADED
			);
		}
		
		var toc = new Sidebar_FormTOC(formName, containerID);
		return toc;
	}
};