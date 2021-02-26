var Ratings = 
{
	'MaxRating':null, // set serverside
	'rating':null,
	
	Start:function()
	{
		var ratings = this;
		
		// un-hover all stars when leaving the boundary
		$('#rating-widget .rating-stars').mouseleave(function() 
		{
			ratings.Clear();
		});
		
		// individual stars event handlers
		$('#rating-widget .rating-star').each(function() 
		{
			var el = $(this);
			
			el.mouseover(function() {
				ratings.Hover(el);
			})
			
			el.click(function() {
				ratings.Select(el);
			});
		});
	},
	
	Clear:function()
	{
		$('#rating-widget .rating-star').removeClass('hover');
		
		for(var i=1; i <= this.MaxRating; i++) {
			$('#rating-widget .rating-stars').removeClass('rating-'+i);
		}
	},
	
	Select:function(el)	
	{
		UI.CloseAllTooltips();
		
		var ratings = this;
		var number = parseInt(el.attr('data-number'));
		var payload = {
			'rating':number,
			'source_url':document.location.href
		};
		
		application.createAJAX('RatingAdd')
		.SetPayload(payload)
		.Success(function(data) {
			ratings.Handle_AddSuccess(data);
		})
		.Send();
		
		$('#rating-widget .rating-preview').html(t('Thank you!'));
	},
	
	Hide:function()
	{
		$('#rating-widget').fadeOut(350);
	},
	
	Handle_AddSuccess:function(data)
	{
		this.rating = new Ratings_Rating(
			data.rating_id,
			data.rating,
			data.date,
			data.comments
		);
		
		var dialog = this;
		
		application.loadScript(
			'ratings/dialog/comments.js', 
			function() {
				dialog.ShowCommentsDialog();
			}
		);
	},
	
	ShowCommentsDialog:function()
	{
		var comments = new Ratings_Dialog_Comments();
		comments.SetRating(this.rating);
		comments.Show();
	},
	
	Hover:function(el)
	{
		var number = parseInt(el.attr('data-number'));
		
		for(var i=1; i <= this.MaxRating; i++) 
		{
			if(i==number) {
				$('#rating-widget .rating-stars').addClass('rating-'+i);
			} else {
				$('#rating-widget .rating-stars').removeClass('rating-'+i);
			}
			
			if(i <= number) {
				$('#rating-widget .star-'+i).addClass('hover');
			} else {
				$('#rating-widget .star-'+i).removeClass('hover');
			}
		}
		
		$('#rating-widget .rating-stars').addClass('rating-'+number);
	}
};

var Ratings_Rating = 
{
	'id':null,
	'rating':null,
	'date':null,
	'comments':null,

	init:function(id, rating, date, comments)
	{
		this.id = id;
		this.rating = rating;
		this.date = date;
		this.comments = comments;
	},
	
	GetID:function()
	{
		return this.id;
	},
	
	GetComments:function()
	{
		return this.comments;
	},
	
	GetRating:function()
	{
		return this.rating;
	}
};

Ratings_Rating = Class.extend(Ratings_Rating);