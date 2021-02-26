function selectAll( fieldname )
{
	$('input').each(
		function() {
			if( $(this).attr('name') == fieldname ) {
				$(this).prop('checked', true);
			}
		}
	);
}

function deselectAll( fieldname )
{
	$('input').each(
		function() {
			if( $(this).attr('name') == fieldname ) {
				$(this).prop('checked', false);
			}
		}
	);
}