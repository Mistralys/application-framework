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

		/**
		 * The sidebar scroll animation speed
		 * @property {Integer}
		 */
		'Speed':350,

		'parentInnerHeight':null,

		/**
		 * Starts the sidebar handling: checks if the page has a sidebar
		 * at all, and if it does registers the required events to make
		 * it scroll along with the page.
		 *
		 */
		Start:function()
		{
			var $sidebar = $('#sidebar .sidebar-wrap, #quickjump');

			// some pages do not have a sidebar
			if($sidebar.length==0) {
				return;
			}

			application.log('Sidebar', 'Sidebar found, setting up automatic scrolling.', 'ui');

			var $window = $(window);
			var $sidebarContainer = $('.with-sidebar');
			var sidebarOffsetTop = $sidebarContainer.offset().top;
			var navbarHeight = $('#app-mainnav').height();

			var topPadding = 15;

			var sidebarMargin = 0;

			$window.scroll(function() {
				var windowScrollTop = $window.scrollTop();
				var sidebarHeight = $sidebar.height();
				var windowInnerHeight = $window.innerHeight();

				//Set it once when scrolling started, otherwise this value is not correct from start
				if(this.parentInnerHeight == null){
					this.parentInnerHeight = $sidebar.parent().innerHeight();
				}

				// Only consider scrolling the sidebar,
				// if sidebar is bigger than the section to the left of it
				if (this.parentInnerHeight > sidebarHeight) {

					//Calculate starting point to scroll
					var topMarginStart = windowScrollTop + navbarHeight + topPadding;

					//Calculate margin when the top global Navbar has almost reached the siebar
					if(topMarginStart > sidebarOffsetTop){

						//Scroll from top when the sidebar is smaller than the window size of the clients monitor
						if(sidebarHeight <= (windowInnerHeight - navbarHeight)){
							sidebarMargin = topMarginStart - sidebarOffsetTop;
						}else{
							//When the sidebar is bigger than the clients monitor, check when we reached bottom or the top

							var bottomMarginStart = (windowScrollTop + windowInnerHeight) - (sidebarOffsetTop + sidebarHeight)
							if(bottomMarginStart > 0 && bottomMarginStart >= sidebarMargin){

								//Check if we reached the bottom of the sidebar and need to add margin from top
								//The new calculated margin needs to be bigger than the current margin, otherwise it means the user is scrolling upwards
								sidebarMargin = bottomMarginStart;
							}else if(topMarginStart < sidebarOffsetTop + sidebarMargin){

								//Check if we reached the top of the sidebar
								//if yes, start calculating the new margin
								sidebarMargin = topMarginStart - sidebarOffsetTop;
							}else{
								//If we neither reached the bottom or top, we leave the margin as is..
							}
						}

						// If sidebarHeight + marginTop is bigger than the praentInnerHeight,
						// we are at the bottom of the page and the sidebar should not scroll any further
						if(sidebarMargin + sidebarHeight > this.parentInnerHeight){
							sidebarMargin = this.parentInnerHeight - (sidebarHeight);
						}

					}else{
						sidebarMargin = 0;
					}
				} else {
					sidebarMargin = 0;
				}

				$sidebar.stop().animate(
					{marginTop: sidebarMargin},
					this.Speed);
			});
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
		},

		Toggle:function()
		{
			var el = $('#sidebar');
			var elToggle = $('#sidebar-toggle');
			var elCollapse = $('#sidebar-toggle-icon-collapse');
			var elExpand = $('#sidebar-toggle-icon-expand');

			if(elToggle.hasClass('expanded'))
			{
				el.hide();
				elToggle.removeClass('expanded');
				elToggle.addClass('collapsed');
				elCollapse.hide();
				elExpand.show();
			}
			else
			{
				el.show();
				elToggle.addClass('expanded');
				elToggle.removeClass('collapsed');
				elCollapse.show();
				elExpand.hide();
			}
		}
	};