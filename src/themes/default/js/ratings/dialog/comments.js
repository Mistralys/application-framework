var Ratings_Dialog_Comments =
{
	'form':null,
	'rating':null,
		
	_init:function()
	{
		this.SetIcon(UI.Icon().Rating());
		this.CreateForm();
	},
 
	_GetTitle:function()
	{
		return t('Rating comments');
	},
 
	_RenderAbstract:function()
	{
		return ''+ 
		t(
			'Thank you for your rating, it will help us identify which areas of %1$s work well, and which need improvement.',
			application.getAppName()
		);
	},
 
	_Handle_Shown:function()
	{
        this.form.FocusElement('comments');
	},
 
	_Handle_Closed:function()
	{
		Ratings.Hide();
	},
	
	_RenderBody:function()
	{
		return this.form.Render();
	},
 
	_RenderFooter:function()
	{
        var dialog = this;
 
		this.AddButtonPrimary(
            t('Confirm'),
            function() {
                dialog.form.Submit();
            }
        );
 
		this.AddButtonCancel();
	},
 
	Handle_SubmitComments:function(formValues)
	{
		var comments = formValues.comments.trim();
		
		// no comments, skip the rest.
		if(isEmpty(comments)) {
			this.Hide();
			return;
		}
 
        this.Hide();
        
        var payload = {
    		'rating_id':this.rating.GetID(),
    		'comments':comments
        };
        
        application.createAJAX('RatingSetComments')
        .SetPayload(payload)
        .Send();
	},
	
	SetRating:function(rating)
	{
		this.rating = rating;
	},
	
	CreateForm:function()
	{
		var dialog = this;
		var form = FormHelper.createForm(this.elementID('form'));
		 
		form.Submit(function(values) {
		    dialog.Handle_SubmitComments(values);
		});
		 
		var comments = form.AddTextarea('comments', t('Your comments'));
		comments.AddClass('input-xxlarge');
		comments.SetSize(7);
		comments.SetHelpText(
			'<b>' + t('Would you care to add a comment to your rating?') + '</b>' + ' '+
			t('It is entirely optional, but does help a lot, especially if you think a feature could/should be improved.')	
		);
		 
		form.SetDefaultElement('comments');
		
		this.form = form;
	}
};
 
Ratings_Dialog_Comments = Dialog_Basic.extend(Ratings_Dialog_Comments);