/**
 * Handles the changelog version selection screen.
 * 
 * @package Application
 * @subpackage Changelog
 * @class
 * @author Sebastian Mordziol <s.mordziol@mistralys.eu>
 * @extends Dialog_Basic
 */
var Changelog_Dialog_SwitchRevision = 
{
	_GetTitle:function()
	{
		return t('%1$s revisions', Changelog.Revisionable.Label);
	},
	
	_RenderBody:function()
	{
		var html = this.RenderLoader();
		
		this.LoadRevisions();
		
		return html;
	},
	
	RenderLoader:function()
	{
		return application.renderAlertInfo(
			application.renderSpinner(t('Loading revisions list, please wait...'))
		);
	},
	
	LoadRevisions:function()
	{
		this.ChangeBody(this.RenderLoader());
		
		var dialog = this;
		var payload = {
			'owner_primary':Changelog.Revisionable.OwnerPrimary,
			'type_name':Changelog.Revisionable.TypeName
		};
		
		application.AJAX(
			'GetChangelogRevisions', 
			payload, 
			function(data) {
				dialog.Handle_LoadRevisionsSuccess(data);
			}, 
			function(errorText, data) {
				dialog.Handle_LoadRevisionsFailure(errorText, data);
			}
		);
	},
	
	Handle_LoadRevisionsSuccess:function(data)
	{
		data.revisions.sort(function(a, b) {
			if(a.timestamp*1 > b.timestamp*1) { return -1; }
			if(b.timestamp*1 > a.timestamp*1) { return 1; }
			return 0;
		});
		
		var html = '' +
		'<table class="table table-condensed table-hover changelog-table" id="' + this.elementID('table') + '">' +
			'<thead>' +
				'<tr>' +
					'<th class="align-right">' + t('Revision') + '</th>' +
					'<th>' + t('Date') + '</th>' +
					'<th>' + t('Author') + '</th>' +
					'<th>' + t('Comments') + '</th>' +
					'<th class="align-center">' + t('Changes') + '</th>';
					if(!data.stateless) {
						html += '' +
						'<th>' + t('State') + '</th>';
					}
					html += '' +
				'</tr>' +
			'</thead>' +
			'<tbody>';
				$.each(data.revisions, function(idx, revision) 
				{
					var revisionLabel = revision.pretty_revision;
					if(User.isDeveloper()) {
						revisionLabel += ' <span class="muted">(' + revision.revision + ')</span>'; 
					}
					
					var rowClass = 'without-changes';
					var changesLabel = '<span class="muted">-</span>';
					if(revision.amount_changes*1 > 0) {
						changesLabel = revision.amount_changes;
						rowClass = 'with-changes';
					}
					
					var commentsLabel = revision.comments;
					if(commentsLabel == null || commentsLabel.length == 0) {
						commentsLabel = '<span class="muted">(' + t('No comments') + ')</span>';
					}
					
					html += ''+
					'<tr class="changelog-row ' + rowClass + '" data-refid="' + revision.revision + '">' +
						'<td class="align-right">' + revisionLabel + '</td>' +
						'<td>' + revision.date + '</td>' + 
						'<td>' + revision.owner_name + '</td>' +
						'<td>' + commentsLabel + '</td>' + 
						'<td class="align-center">' + changesLabel + '</td>';
						if(!data.stateless) {
							html += '' +
							'<td>' + revision.state_label_pretty + '</td>';
						}
						html += ''+
					'</tr>';
				});
				html += '' +
			'</tbody>'+
		'</table>';
			
		this.ChangeBody(html);
		
		var dialog = this;
		UI.RefreshTimeout(function() {
			dialog.Handle_TableRendered();
		});
	},
	
	Handle_TableRendered:function()
	{
		var dialog = this;
		$('#' + this.elementID('table') + ' .changelog-row').on('click', function() {
			dialog.Handle_ClickRow($(this));
		});
	},
	
	Handle_ClickRow:function(row)
	{
		if(row.hasClass('without-changes')) {
			return;
		}
		
		var revision = row.attr('data-refid');
		this.Hide();
		
		application.showLoader(t('Please wait, loading...'));
		
		document.location = URI(document.location.href)
			.removeSearch('revision')
			.addSearch('revision', revision);
	},
	
	Handle_LoadRevisionsFailure:function(errorText, data)
	{
		var dialog = this;
		
		this.ChangeBody(application.renderAlertError(
			'<p>' + 
				UI.Icon().Warning() + ' ' +
				'<b>' + t('Failed to load the revisions list.') + '</b> ' +
				t('Reason given:') + ' ' + errorText + 
			'<p>' +
			'</p>' +
				UI.Button(t('Try again'))
					.SetIcon(UI.Icon().Refresh())
					.Click(function() {
						dialog.LoadRevisions();
					}) +
			'</p>'
		));
	},
	
	_RenderFooter:function()
	{
		var dialog = this;
		
		this.AddButton(
			UI.Button(t('Show only with changes'))
				.SetTooltip(t('Shows only revisions in which at least one change was made.'))
				.SetIcon(UI.Icon().Collapse())
				.Click(function() {
					dialog.Handle_ShowOnlyChanged();
				})
		);
		
		this.AddButton(
			UI.Button(t('Show all'))
				.SetTooltip(t('Shows all revisions.'))
				.SetIcon(UI.Icon().Expand())
				.Click(function() {
					dialog.Handle_ShowAll();
				})
		);
		
		this.AddButtonClose();
	},
	
	Handle_ShowOnlyChanged:function()
	{
		$('#' + this.elementID('table') + ' .without-changes').hide();
	},
	
	Handle_ShowAll:function()
	{
		$('#' + this.elementID('table') + ' .without-changes').show();
	}
};

Changelog_Dialog_SwitchRevision = Dialog_Basic.extend(Changelog_Dialog_SwitchRevision);