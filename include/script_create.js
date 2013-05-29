function show_query_settings(this_val)
{
	$('.tr_check, .tr_text, .tr_select').hide();

	if(this_val != '')
	{
		if(this_val == 3)
		{
			$('.tr_check').show();
		}

		if(this_val != 6)
		{
			$('.tr_text').show();
		}

		if(this_val == 10 || this_val == 14)
		{
			$('.tr_select').show();
		}
	}
}

$(function()
{
	show_query_settings($('#intQueryTypeID').val());

	$('#intQueryTypeID').on('change', function()
	{
		show_query_settings($(this).val());
	});

	$('.sortable_form').sortable(
	{
		//handle: $('.form_row'),
		opacity: .7,
		//placeholder: 'ui-state-highlight',
		update: function()
		{
			var post_data = $(this).sortable('toArray');

			//console.log(post_data);

			$.ajax(
			{
				url: '/wp-content/plugins/mf_form/include/ajax.php?type=sortOrder',
				type: 'post',
				data: 'strOrder=' + post_data,
				dataType: 'json',
				success: function(data)
				{
					if(data.success)
					{}

					else if(data.error)
					{
						alert(data.error);
					}
				}
			});
		}
	});
});